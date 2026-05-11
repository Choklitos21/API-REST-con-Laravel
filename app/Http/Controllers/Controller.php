<?php

namespace App\Http\Controllers;

use App\Services\DiscordNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Facade as Sentry;
use Throwable;

abstract class Controller
{
    protected function handleInternalServerError(Request $request, Throwable $exception, string $context): \Illuminate\Http\JsonResponse
    {
        Log::error($context, ['error' => $exception->getMessage()]);
        $eventId = Sentry::captureException($exception);

        if ($eventId !== null) {
            Log::info('Sentry event sent', ['event_id' => (string) $eventId]);
        }

        app(DiscordNotifier::class)->notifyInternalServerError($request, $exception);

        return response()->json([
            'message' => 'Internal server error',
        ], 500);
    }
}
