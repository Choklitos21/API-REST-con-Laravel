<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Sentry\Laravel\Facade as Sentry;
use Tests\TestCase;

class SentryIntegrationTest extends TestCase
{
    public function test_internal_server_error_is_reported_to_sentry(): void
    {
        Sentry::shouldReceive('captureException')
            ->once()
            ->andReturn(null);

        $controller = new class extends Controller
        {
            public function report(Request $request, Exception $exception)
            {
                return $this->handleInternalServerError($request, $exception, 'Test context');
            }
        };

        $response = $controller->report(
            Request::create('/api/tickets', 'GET'),
            new Exception('Simulated unexpected error')
        );

        $this->assertEquals(500, $response->getStatusCode());
    }
}
