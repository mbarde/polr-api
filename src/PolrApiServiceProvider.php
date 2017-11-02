<?php

namespace Lagdo\Polr\Api;

use Illuminate\Support\ServiceProvider;

class PolrApiServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Load package routes
        require(__DIR__ . '/Http/routes.php');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the route middleware
        $this->app->routeMiddleware([
            'rest_api' => Http\Middleware\RestApiMiddleware::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
