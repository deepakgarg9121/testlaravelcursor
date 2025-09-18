<?php

namespace App\Http\Middleware;

use App\Support\Metrics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $path = $request->path();
        // Avoid counting the metrics endpoint itself
        if ($path !== 'metrics') {
            Metrics::increment('http_requests_total', [
                'method' => $request->getMethod(),
                'path' => '/' . ltrim($path, '/'),
                'status' => (string) $response->getStatusCode(),
            ], 1);
        }

        return $response;
    }
}


