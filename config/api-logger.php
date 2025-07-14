<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Logger Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the API Logger package.
    | You can customize these settings based on your application needs.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable API Logging
    |--------------------------------------------------------------------------
    |
    | Set this to false to completely disable API logging.
    |
    */
    'enabled' => env('API_LOGGER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Use queues for better performance when logging API requests.
    | This is recommended for high-traffic applications.
    |
    */
    'use_queue' => env('API_LOGGER_USE_QUEUE', false),
    'queue_name' => env('API_LOGGER_QUEUE_NAME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Body Size Limits
    |--------------------------------------------------------------------------
    |
    | Maximum size of request/response body to log in bytes.
    | Larger bodies will be truncated with a message.
    |
    */
    'max_body_size' => env('API_LOGGER_MAX_BODY_SIZE', 10000),

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | Paths that should not be logged. These are matched against the request path.
    |
    */
    'excluded_paths' => [
        '/health',
        '/metrics',
        '/ping',
        '/favicon.ico',
        '/robots.txt',
        '/sitemap.xml',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded HTTP Methods
    |--------------------------------------------------------------------------
    |
    | HTTP methods that should not be logged.
    |
    */
    'excluded_methods' => [
        'OPTIONS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Status Codes
    |--------------------------------------------------------------------------
    |
    | HTTP status codes that should not be logged.
    |
    */
    'excluded_status_codes' => [
        404, // Not Found
        405, // Method Not Allowed
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded IP Addresses
    |--------------------------------------------------------------------------
    |
    | IP addresses that should not be logged.
    |
    */
    'excluded_ips' => [
        // Add IP addresses here
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Headers
    |--------------------------------------------------------------------------
    |
    | Headers that contain sensitive information and should be redacted.
    |
    */
    'sensitive_headers' => [
        'authorization',
        'cookie',
        'x-api-key',
        'x-auth-token',
        'x-csrf-token',
        'x-forwarded-for',
        'x-real-ip',
        'x-client-ip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Patterns
    |--------------------------------------------------------------------------
    |
    | Regular expressions to detect sensitive data in request/response bodies.
    | If any pattern matches, the entire body will be redacted.
    |
    */
    'sensitive_patterns' => [
        '/password["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        '/token["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        '/secret["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        '/key["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        '/api_key["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        '/access_token["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
        '/refresh_token["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to keep API logs before automatic cleanup.
    | Set to null to disable automatic cleanup.
    |
    */
    'retention_days' => env('API_LOGGER_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Response time thresholds for categorizing requests as slow.
    |
    */
    'slow_request_threshold_ms' => env('API_LOGGER_SLOW_THRESHOLD_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database connection to use for storing API logs.
    |
    */
    'database_connection' => env('API_LOGGER_DB_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Logging Channels
    |--------------------------------------------------------------------------
    |
    | Additional logging channels to use for API logs.
    | This is in addition to the database storage.
    |
    */
    'additional_channels' => [
        // 'slack',
        // 'mail',
    ],

    /*
    |--------------------------------------------------------------------------
    | Correlation ID Headers
    |--------------------------------------------------------------------------
    |
    | Headers to check for correlation IDs in order of preference.
    |
    */
    'correlation_id_headers' => [
        'X-Correlation-ID',
        'X-Request-ID',
        'X-Trace-ID',
    ],

    /*
    |--------------------------------------------------------------------------
    | Metadata Fields
    |--------------------------------------------------------------------------
    |
    | Additional fields to capture in the metadata column.
    |
    */
    'metadata_fields' => [
        'route_name',
        'route_action',
        'route_parameters',
        'query_parameters',
        'content_type',
        'accept',
        'referer',
        'origin',
    ],

]; 