<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscordNotifier
{
    public function notifyInternalServerError(Request $request, Throwable $exception): void
    {
        $message = implode("\n", [
            '🚨 **Internal Server Error (500)**',
            'Endpoint: /'.$request->path(),
            'HTTP Method: '.$request->method(),
            'Error Message: '.$exception->getMessage(),
            'Date: '.now()->toDateTimeString(),
            'Client IP: '.$request->ip(),
        ]);

        $this->send($message);
    }

    public function notifyRateLimitExceeded(Request $request, int $attempts): void
    {
        $message = implode("\n", [
            '⚠️ **Rate Limit Exceeded (429)**',
            'Endpoint: /'.$request->path(),
            'Client IP: '.$request->ip(),
            'Timestamp: '.now()->toDateTimeString(),
            'Attempts: '.$attempts,
        ]);

        $this->send($message);
    }

    private function send(string $message): void
    {
        $webhookUrl = config('services.discord.webhook_url');

        if (! is_string($webhookUrl) || $webhookUrl === '') {
            return;
        }

        try {
            Http::timeout(5)->post($webhookUrl, [
                'content' => $message,
            ]);
        } catch (Throwable $exception) {
            Log::error('Discord notification error', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
