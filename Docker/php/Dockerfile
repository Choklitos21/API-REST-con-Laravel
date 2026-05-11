FROM php:8.4-cli

# Base dependencies for Laravel and SQL Server support
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    zip \
    gnupg2 \
    unixodbc-dev \
    apt-transport-https \
    ca-certificates \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Microsoft key and repository
RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc \
    | gpg --dearmor \
    -o /usr/share/keyrings/microsoft-prod.gpg

RUN echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/mssql-release.list

# SQL Server ODBC driver
RUN apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions needed by the API
RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install pdo mbstring bcmath pcntl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Expose API port
EXPOSE 8000
