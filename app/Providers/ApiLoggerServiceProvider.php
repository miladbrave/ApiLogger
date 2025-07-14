<?php

namespace App\Providers;

use App\Http\Middleware\ApiLoggerMiddleware;
use App\Services\ApiLoggerService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ApiLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the API logger service as a singleton
        $this->app->singleton(ApiLoggerService::class, function ($app) {
            return new ApiLoggerService();
        });

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/api-logger.php', 'api-logger'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/api-logger.php' => config_path('api-logger.php'),
            ], 'api-logger-config');

            // Register commands
            $this->commands([
                \App\Console\Commands\CleanApiLogsCommand::class,
            ]);

            // Schedule cleanup command
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $retentionDays = config('api-logger.retention_days');
                
                if ($retentionDays !== null) {
                    $schedule->command('api-logs:clean', ['--days' => $retentionDays])
                        ->daily()
                        ->at('02:00')
                        ->withoutOverlapping();
                }
            });
        }

        // Register middleware
        $this->app['router']->aliasMiddleware('api.logger', ApiLoggerMiddleware::class);

        // Auto-register middleware for API routes if configured
        if (config('api-logger.auto_register_middleware', true)) {
            $this->app->booted(function () {
                $router = $this->app['router'];
                
                // Register for API routes
                if (method_exists($router, 'middlewareGroup')) {
                    $router->middlewareGroup('api', function ($router) {
                        $router->pushMiddlewareToGroup('api', ApiLoggerMiddleware::class);
                    });
                }
            });
        }
    }
} 