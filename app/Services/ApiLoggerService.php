<?php

namespace App\Services;

use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiLoggerService
{
    /**
     * Log an API request and response.
     */
    public function log(Request $request, Response $response, float $startTime): void
    {
        $endTime = microtime(true);
        $responseTimeMs = round(($endTime - $startTime) * 1000, 2);

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_headers' => $this->filterHeaders($request->headers->all()),
            'request_body' => $this->getRequestBody($request),
            'response_headers' => $this->filterHeaders($response->headers->all()),
            'response_body' => $this->getResponseBody($response),
            'response_status' => $response->getStatusCode(),
            'response_time_ms' => $responseTimeMs,
            'user_id' => $this->getUserId(),
            'session_id' => $request->session()?->getId(),
            'correlation_id' => $this->getCorrelationId($request),
            'metadata' => $this->getMetadata($request),
        ];

        // Use queue for better performance if configured
        if (config('api-logger.use_queue', false)) {
            dispatch(function () use ($logData) {
                ApiLog::create($logData);
            })->onQueue(config('api-logger.queue_name', 'default'));
        } else {
            ApiLog::create($logData);
        }
    }

    /**
     * Get the request body content.
     */
    protected function getRequestBody(Request $request): ?string
    {
        $content = $request->getContent();

        // Don't log if content is too large
        if (strlen($content) > config('api-logger.max_body_size', 10000)) {
            return '[Content too large to log]';
        }

        // Don't log sensitive data
        if ($this->containsSensitiveData($content)) {
            return '[Sensitive content redacted]';
        }

        return $content ?: null;
    }

    /**
     * Get the response body content.
     */
    protected function getResponseBody(Response $response): ?string
    {
        $content = $response->getContent();

        // Don't log if content is too large
        if (strlen($content) > config('api-logger.max_body_size', 10000)) {
            return '[Content too large to log]';
        }

        // Don't log sensitive data
        if ($this->containsSensitiveData($content)) {
            return '[Sensitive content redacted]';
        }

        return $content ?: null;
    }

    /**
     * Filter headers to remove sensitive information.
     */
    protected function filterHeaders(array $headers): array
    {
        $sensitiveHeaders = config('api-logger.sensitive_headers', [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
            'x-csrf-token',
        ]);

        $filtered = [];
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (!in_array($lowerKey, $sensitiveHeaders)) {
                $filtered[$key] = $value;
            } else {
                $filtered[$key] = '[REDACTED]';
            }
        }

        return $filtered;
    }

    /**
     * Check if content contains sensitive data.
     */
    protected function containsSensitiveData(string $content): bool
    {
        $sensitivePatterns = config('api-logger.sensitive_patterns', [
            '/password["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
            '/token["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
            '/secret["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
            '/key["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        ]);

        foreach ($sensitivePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the current user ID.
     */
    protected function getUserId(): ?string
    {
        if (Auth::check()) {
            return Auth::id();
        }

        return null;
    }

    /**
     * Get or generate a correlation ID for request tracking.
     */
    protected function getCorrelationId(Request $request): string
    {
        // Check if correlation ID is already set
        $correlationId = $request->header('X-Correlation-ID') 
            ?? $request->header('X-Request-ID')
            ?? $request->header('X-Trace-ID');

        if ($correlationId) {
            return $correlationId;
        }

        // Generate a new correlation ID
        return Str::uuid()->toString();
    }

    /**
     * Get additional metadata for the request.
     */
    protected function getMetadata(Request $request): array
    {
        return [
            'route_name' => $request->route()?->getName(),
            'route_action' => $request->route()?->getActionName(),
            'route_parameters' => $request->route()?->parameters(),
            'query_parameters' => $request->query(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'referer' => $request->header('Referer'),
            'origin' => $request->header('Origin'),
        ];
    }

    /**
     * Clean old log entries.
     */
    public function cleanOldLogs(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        return ApiLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get statistics about API logs.
     */
    public function getStatistics(string $period = '24h'): array
    {
        $startDate = match($period) {
            '1h' => now()->subHour(),
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            default => now()->subDay(),
        };

        $query = ApiLog::where('created_at', '>=', $startDate);

        return [
            'total_requests' => $query->count(),
            'successful_requests' => $query->where('response_status', '>=', 200)
                ->where('response_status', '<', 300)->count(),
            'error_requests' => $query->where('response_status', '>=', 400)->count(),
            'average_response_time' => $query->avg('response_time_ms'),
            'slowest_request' => $query->orderBy('response_time_ms', 'desc')->first(),
            'most_common_endpoints' => $query->selectRaw('url, COUNT(*) as count')
                ->groupBy('url')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'status_code_distribution' => $query->selectRaw('response_status, COUNT(*) as count')
                ->groupBy('response_status')
                ->orderBy('response_status')
                ->get(),
        ];
    }
} 