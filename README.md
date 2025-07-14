<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Laravel API Logger

A comprehensive API logging package for Laravel that captures and stores all incoming API requests and responses, including headers, body content, and performance metrics.

## Features

- **Complete Request/Response Logging**: Captures HTTP method, URL, headers, body, status codes, and response times
- **Security-First**: Automatically redacts sensitive information like passwords, tokens, and API keys
- **Performance Monitoring**: Tracks response times and identifies slow requests
- **User Tracking**: Associates requests with authenticated users and sessions
- **Correlation IDs**: Supports request tracing with correlation IDs
- **Web Interface**: Beautiful, responsive web interface for viewing and analyzing logs
- **Advanced Filtering**: Filter logs by method, status, user, date range, and more
- **Export Functionality**: Export logs in JSON or CSV format
- **Automatic Cleanup**: Scheduled cleanup of old log entries
- **Queue Support**: Optional queue-based logging for high-traffic applications
- **Comprehensive Statistics**: Real-time statistics and analytics

## Installation

1. **Run the migration** to create the API logs table:
   ```bash
   php artisan migrate
   ```

2. **Publish the configuration** (optional):
   ```bash
   php artisan vendor:publish --tag=api-logger-config
   ```

3. **Register the middleware** in your `app/Http/Kernel.php`:
   ```php
   protected $middlewareGroups = [
       'api' => [
           // ... other middleware
           \App\Http\Middleware\ApiLoggerMiddleware::class,
       ],
   ];
   ```

   Or apply it to specific routes:
   ```php
   Route::middleware(['api.logger'])->group(function () {
       // Your API routes here
   });
   ```

## Configuration

The package is highly configurable. You can customize the behavior by modifying the `config/api-logger.php` file:

### Basic Configuration

```php
return [
    // Enable/disable API logging
    'enabled' => env('API_LOGGER_ENABLED', true),
    
    // Use queues for better performance
    'use_queue' => env('API_LOGGER_USE_QUEUE', false),
    'queue_name' => env('API_LOGGER_QUEUE_NAME', 'default'),
    
    // Maximum body size to log (in bytes)
    'max_body_size' => env('API_LOGGER_MAX_BODY_SIZE', 10000),
    
    // Log retention period (in days)
    'retention_days' => env('API_LOGGER_RETENTION_DAYS', 30),
];
```

### Security Configuration

```php
// Headers to redact
'sensitive_headers' => [
    'authorization',
    'cookie',
    'x-api-key',
    'x-auth-token',
    'x-csrf-token',
],

// Patterns to detect sensitive data
'sensitive_patterns' => [
    '/password["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
    '/token["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
    '/secret["\']?\s*[:=]\s*["\'][^"\']*["\']/i',
],
```

### Exclusion Rules

```php
// Paths to exclude from logging
'excluded_paths' => [
    '/health',
    '/metrics',
    '/ping',
    '/favicon.ico',
],

// HTTP methods to exclude
'excluded_methods' => [
    'OPTIONS',
],

// Status codes to exclude
'excluded_status_codes' => [
    404, // Not Found
    405, // Method Not Allowed
],
```

## Usage

### Web Interface

Access the web interface at `/api-logs` to view and analyze your API logs:

- **Dashboard**: View statistics and overview
- **Filtering**: Filter by method, status, user, date range, etc.
- **Details**: View complete request/response details
- **Export**: Export logs in various formats

### API Endpoints

The package provides several API endpoints for programmatic access:

```php
// Get all logs with filtering
GET /api-logs?method=POST&status=200&user_id=123

// Get specific log details
GET /api-logs/{id}

// Get statistics
GET /api-logs/statistics?period=24h

// Export logs
GET /api-logs/export?format=csv

// Delete specific log
DELETE /api-logs/{id}

// Bulk delete logs
DELETE /api-logs/bulk
```

### Artisan Commands

```bash
# Clean old logs
php artisan api-logs:clean --days=30

# Clean old logs without confirmation
php artisan api-logs:clean --days=30 --force
```

### Programmatic Usage

```php
use App\Services\ApiLoggerService;

// Get statistics
$logger = app(ApiLoggerService::class);
$stats = $logger->getStatistics('24h');

// Clean old logs
$deletedCount = $logger->cleanOldLogs(30);
```

## Database Schema

The `api_logs` table includes the following fields:

- `id` - Primary key
- `method` - HTTP method (GET, POST, etc.)
- `url` - Full request URL
- `ip_address` - Client IP address
- `user_agent` - User agent string
- `request_headers` - JSON encoded request headers
- `request_body` - Request body content
- `response_headers` - JSON encoded response headers
- `response_body` - Response body content
- `response_status` - HTTP status code
- `response_time_ms` - Response time in milliseconds
- `user_id` - Authenticated user ID
- `session_id` - Session ID
- `correlation_id` - Request correlation ID
- `metadata` - Additional request metadata
- `created_at` - Timestamp
- `updated_at` - Timestamp

## Security Features

### Automatic Data Redaction

The package automatically redacts sensitive information:

- **Headers**: Authorization, cookies, API keys, tokens
- **Body Content**: Passwords, tokens, secrets, keys
- **Pattern Matching**: Configurable regex patterns for sensitive data

### Privacy Protection

- Sensitive headers are replaced with `[REDACTED]`
- Bodies containing sensitive data are replaced with `[Sensitive content redacted]`
- Large bodies are truncated to prevent storage issues

## Performance Considerations

### Queue-Based Logging

For high-traffic applications, enable queue-based logging:

```php
'use_queue' => true,
'queue_name' => 'api-logs',
```

### Database Optimization

The package includes database indexes for optimal performance:

- Composite index on `method` and `url`
- Index on `response_status`
- Index on `created_at`
- Index on `user_id`
- Index on `correlation_id`

### Automatic Cleanup

Configure automatic cleanup to prevent database bloat:

```php
'retention_days' => 30, // Keep logs for 30 days
```

The cleanup runs daily at 2:00 AM via Laravel's task scheduler.

## Testing

Generate test data using the included factory:

```php
use App\Models\ApiLog;

// Create a single log entry
ApiLog::factory()->create();

// Create multiple log entries
ApiLog::factory()->count(100)->create();

// Create successful requests only
ApiLog::factory()->successful()->count(50)->create();

// Create error requests only
ApiLog::factory()->error()->count(20)->create();

// Create slow requests
ApiLog::factory()->slow()->count(10)->create();
```

## Troubleshooting

### Common Issues

1. **Middleware not working**: Ensure the middleware is registered in the correct middleware group
2. **Large log files**: Adjust `max_body_size` or enable queue-based logging
3. **Performance issues**: Enable queue-based logging and ensure proper database indexes
4. **Sensitive data exposure**: Review and update `sensitive_patterns` configuration

### Debug Mode

Enable debug logging to troubleshoot issues:

```php
// In your .env file
API_LOGGER_ENABLED=true
API_LOGGER_MAX_BODY_SIZE=5000
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support and questions:

- Create an issue on GitHub
- Check the documentation
- Review the configuration options

## Changelog

### Version 1.0.0
- Initial release
- Complete request/response logging
- Web interface
- Security features
- Performance monitoring
- Export functionality
