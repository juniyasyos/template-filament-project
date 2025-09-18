<?php

namespace App\Domain\Drive\Providers;

use App\Domain\Drive\Repositories\DriveNodeRepositoryInterface;
use App\Domain\Drive\Repositories\DriveNodeRepository;
use App\Domain\Drive\Services\DriveService;
use Illuminate\Support\ServiceProvider;

class DriveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(DriveNodeRepositoryInterface::class, DriveNodeRepository::class);

        // Register drive service as singleton
        $this->app->singleton(DriveService::class, function ($app) {
            return new DriveService($app->make(DriveNodeRepositoryInterface::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
