<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            \Illuminate\Support\Facades\Log::info('Laravel back-end connected to PostgreSQL Database ✅');
            // For visibility in docker logs (STDOUT/STDERR)
            if (app()->environment('local')) {
                error_log('Laravel back-end connected to PostgreSQL Database ✅');
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("Could not connect to the database. Check your configuration. Error: " . $e->getMessage());
             error_log("❌ Laravel back-end database connection failed: " . $e->getMessage());
        }
    }
}
