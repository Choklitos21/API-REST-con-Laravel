# Laravel API REST (Tickets & Devices)

REST API built with Laravel for user authentication, ticket management, and device assignment.

## Features

- Authentication with **Laravel Sanctum** (token-based API auth)
- Tickets CRUD endpoints
- Devices listing and assignment endpoint
- Validation, `try/catch`, exception handling, and correct HTTP status codes
- Global API **Rate Limiter** (`10 requests/minute` by client IP)
- Automatic **Discord alerts** for:
  - Internal server errors (`500`)
  - Rate limit exceeded (`429`)
- **Sentry** integration for monitoring and error traceability
- Docker setup with Laravel app + SQL Server

## Requirements

- Docker + Docker Compose
- (Optional for local non-Docker) PHP 8.2+, Composer, SQL Server

## Environment Variables

Copy `.env.example` to `.env` and configure:

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlsrv
DB_HOST=sqlserver
DB_PORT=1433
DB_DATABASE=laravel
DB_USERNAME=sa
DB_PASSWORD=Password123*

DISCORD_WEBHOOK_URL=
SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=1.0
```

> Do not commit real webhook URLs or DSNs to public repositories.

## Run with Docker (Recommended)

1. Build and start containers:

```bash
docker compose up -d --build
```

2. Check app is up:

```bash
curl http://localhost:8000/up
```

3. API base URL:

```text
http://localhost:8000/api
```

### Stop containers

```bash
docker compose down
```

## Run Locally (without Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Authentication with Laravel Sanctum

- `POST /api/register` and `POST /api/login` return a token.
- Send the token in protected endpoints:

```http
Authorization: Bearer <token>
Accept: application/json
```

- Protected routes:
  - `/api/tickets` (all CRUD endpoints)
  - `/api/devices`
  - `/api/devices/assign`
  - `/api/user`

## API Endpoints

Base path: `/api`

| Method | Endpoint | Auth Required | Description |
|---|---|---|---|
| POST | `/register` | No | Register user |
| POST | `/login` | No | User login |
| GET | `/tickets` | Yes | Get tickets |
| GET | `/tickets/{id}` | Yes | Get ticket by id |
| POST | `/tickets` | Yes | Create ticket |
| PUT | `/tickets/{id}` | Yes | Update ticket |
| DELETE | `/tickets/{id}` | Yes | Delete ticket |
| GET | `/devices` | Yes | Get devices |
| POST | `/devices/assign` | Yes | Assign device |
| GET | `/user` | Yes | Get authenticated user |

## HTTP Responses and Error Handling

- `200 OK`: successful read/update/login
- `201 Created`: successful resource creation
- `401 Unauthorized`: invalid credentials or missing/invalid token
- `404 Not Found`: resource does not exist
- `422 Unprocessable Entity`: validation errors
- `429 Too Many Requests`: rate limit exceeded
- `500 Internal Server Error`: unexpected server error

The controllers use `try/catch` blocks and return structured JSON responses.

## Rate Limiting

- All API routes are protected with Laravel `throttle:api`.
- Current policy: **10 requests per minute per IP**.
- If limit is exceeded, API returns:
  - Status `429`
  - JSON message: `Too many requests`
- A Discord alert is sent automatically with endpoint, IP, timestamp, and attempts.

## Discord Alerts

Configured with `DISCORD_WEBHOOK_URL` in `.env`.

### 1) Internal Error Alert (`500`)

Sent when an internal exception occurs. Includes:
- Endpoint
- HTTP method
- Error message
- Date
- Client IP

### 2) Rate Limit Alert (`429`)

Sent when request limit is exceeded. Includes:
- Endpoint
- Client IP
- Timestamp
- Attempts count

## Sentry Integration

Configured with:

```env
SENTRY_LARAVEL_DSN=your_dsn_here
SENTRY_TRACES_SAMPLE_RATE=1.0
```

Sentry is used to:
- Capture exceptions
- Register HTTP 500 errors
- Register unexpected errors
- Keep evidence in logs when events are sent (event ID logging)

## Quick Usage Example

1. Register:

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\",\"password\":\"password123\"}"
```

2. Login and copy token:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"john@example.com\",\"password\":\"password123\"}"
```

3. Call protected endpoint:

```bash
curl -X GET http://localhost:8000/api/tickets \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```
