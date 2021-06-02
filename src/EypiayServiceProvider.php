<?php

namespace Eypiay\Eypiay;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class EypiayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'eypiay');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'eypiay');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        $routePath = base_path(config('eypiay.path')) . '/build/routes.php';

        if (File::exists($routePath)) {
            $this->loadRoutesFrom($routePath);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('eypiay.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/eypiay'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/eypiay'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/eypiay'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                \Eypiay\Eypiay\Commands\EypiayInstall::class,
                \Eypiay\Eypiay\Commands\EypiayBuild::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'eypiay');

        // Register the main class to use with the facade
        $this->app->singleton('eypiay', function () {
            return new Eypiay;
        });
    }
}
