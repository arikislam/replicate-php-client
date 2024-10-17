<?php

namespace Arikislam\ReplicatePhpClient\Providers;

use Illuminate\Support\ServiceProvider;
use Arikislam\ReplicatePhpClient\ReplicateClient;
use Illuminate\Support\Facades\Log;

/**
 * Class ReplicatePhpClientServiceProvider
 *
 * This service provider is responsible for registering and bootstrapping
 * the Replicate PHP Client within a Laravel application.
 *
 * @package Arikislam\ReplicatePhpClient\Providers
 */
class ReplicatePhpClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * This method is called by Laravel during the service provider registration process.
     * It registers the ReplicateClient in the service container and sets up error handling.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge the package configuration with the application's copy
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/replicate.php', 'replicate'
        );

        // Register the ReplicateClient as a singleton
        $this->app->singleton('replicate', function ($app) {
            return new ReplicateClient($app['config']['replicate.api_token']);
        });

        // Create an alias for the ReplicateClient
        $this->app->alias('replicate', ReplicateClient::class);

        // Add a macro to the ReplicateClient to handle errors
        ReplicateClient::macro('withErrorHandling', function ($callback) {
            try {
                return $callback($this);
            } catch (\Exception $exception) {
                Log::error('Replicate API Error: ' . $exception->getMessage(), [
                    'exception' => $exception,
                ]);
                // You can add more custom error handling here if needed
                throw $exception;
            }
        });
    }

    /**
     * Bootstrap services.
     *
     * This method is called by Laravel after all services are registered.
     * It publishes the package configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__ . '/../../config/replicate.php' => config_path('replicate.php'),
        ], 'replicate-config');
    }
}
