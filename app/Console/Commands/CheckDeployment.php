<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CheckDeployment extends Command
{
    protected $signature = 'deploy:check';
    protected $description = 'Check for new deployments and run necessary tasks';

    public function handle()
    {
        $statePath = base_path('.deployment-state');

        if (!File::exists($statePath)) {
            $this->warn('Deployment state file not found.');
            return;
        }

        try {
            $currentState = md5_file($statePath);
            $lastState = Cache::get('last_deployment_state');

            if ($currentState === $lastState) {
                return;
            }

            $this->info('New deployment detected. Running tasks...');

            // Run deployment tasks
            $this->runDeploymentTasks();

            // Update deployment state
            Cache::forever('last_deployment_state', $currentState);

            $this->info('Deployment tasks completed successfully!');

        } catch (\Exception $e) {
            $this->error('Deployment check failed: ' . $e->getMessage());
            \Log::error('Deployment check failed: ' . $e->getMessage());
        }
    }

    /**
     * Run tasks required for deployment
     */
    private function runDeploymentTasks()
    {
        $this->runMigrations();
        $this->clearCaches();
        $this->optimizeApplication();
    }

    private function runMigrations()
    {
        $this->info('Running database migrations...');
        Artisan::call('migrate:check');
    }

    private function clearCaches()
    {
        $this->info('Clearing application caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
    }

    private function optimizeApplication()
    {
        $this->info('Optimizing application...');
        Artisan::call('optimize:clear');
        Artisan::call('optimize');
    }
}
