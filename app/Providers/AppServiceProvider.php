<?php

namespace App\Providers;

use App\Services\DiscordNotifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $rateLimitKey = 'api|'.$request->ip();

            return Limit::perMinute(10)
                ->by($rateLimitKey)
                ->response(function (Request $request, array $headers) use ($rateLimitKey) {
                    $attempts = RateLimiter::attempts($rateLimitKey);
                    app(DiscordNotifier::class)->notifyRateLimitExceeded($request, $attempts);

                    return response()->json([
                        'message' => 'Too many requests',
                    ], 429, $headers);
                });
        });
    }
}
