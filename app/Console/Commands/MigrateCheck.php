<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the migrations are up to date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Ensure migrations table exists
            if (!Schema::hasTable('migrations')) {
                Log::info('Initial migration running...');
                Artisan::call('migrate', ['--force' => true]);
                Log::info('Initial migration completed');
                return;
            }

            // Check for pending migrations
            $output = Artisan::call('migrate:status');
            $status = Artisan::output();

            if (str_contains($status, 'Pending')) {
                Log::info('Running pending migrations...');
                Artisan::call('migrate', ['--force' => true]);
                Log::info('Migrations completed successfully');
            }
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
