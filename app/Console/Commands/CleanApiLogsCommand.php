<?php

namespace App\Console\Commands;

use App\Services\ApiLoggerService;
use Illuminate\Console\Command;

class CleanApiLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-logs:clean 
                            {--days=30 : Number of days to keep logs}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old API logs from the database';

    /**
     * The API logger service instance.
     */
    protected ApiLoggerService $logger;

    /**
     * Create a new command instance.
     */
    public function __construct(ApiLoggerService $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        if (!$force) {
            $confirmed = $this->confirm(
                "This will delete all API logs older than {$days} days. Are you sure?"
            );

            if (!$confirmed) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info("Cleaning API logs older than {$days} days...");

        try {
            $deletedCount = $this->logger->cleanOldLogs($days);

            $this->info("Successfully deleted {$deletedCount} API log entries.");

            if ($deletedCount > 0) {
                $this->newLine();
                $this->warn("Note: Consider running 'php artisan queue:work' if you're using queue-based logging.");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clean API logs: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
} 