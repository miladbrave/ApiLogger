<?php

namespace App\Http\Middleware;

use App\Services\ApiLoggerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiLoggerMiddleware
{
    /**
     * The API logger service instance.
     */
    protected ApiLoggerService $logger;

    /**
     * Create a new middleware instance.
     */
    public function __construct(ApiLoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip logging if disabled
        if (!config('api-logger.enabled', true)) {
            return $next($request);
        }

        // Skip logging for excluded paths
        if ($this->shouldSkipLogging($request)) {
            return $next($request);
        }

        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        // Skip logging for excluded response status codes
        if ($this->shouldSkipResponseLogging($response)) {
            return $response;
        }

        // Log the request and response
        try {
            $this->logger->log($request, $response, $startTime);
        } catch (\Exception $e) {
            // Log the error but don't break the application
            \Log::error('API Logger failed to log request', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        }

        return $response;
    }

    /**
     * Check if the request should be skipped from logging.
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        $excludedPaths = config('api-logger.excluded_paths', [
            '/health',
            '/metrics',
            '/ping',
            '/favicon.ico',
        ]);

        $excludedMethods = config('api-logger.excluded_methods', [
            'OPTIONS',
        ]);

        // Check excluded paths
        foreach ($excludedPaths as $path) {
            if (str_starts_with($request->path(), ltrim($path, '/'))) {
                return true;
            }
        }

        // Check excluded methods
        if (in_array($request->method(), $excludedMethods)) {
            return true;
        }

        // Check if request is from excluded IPs
        $excludedIps = config('api-logger.excluded_ips', []);
        if (in_array($request->ip(), $excludedIps)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the response should be skipped from logging.
     */
    protected function shouldSkipResponseLogging($response): bool
    {
        if (!$response instanceof SymfonyResponse) {
            return true;
        }

        $excludedStatusCodes = config('api-logger.excluded_status_codes', [
            404, // Not Found
            405, // Method Not Allowed
        ]);

        return in_array($response->getStatusCode(), $excludedStatusCodes);
    }
} 