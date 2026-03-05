<?php

namespace Webkul\KissDataFeed\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\KissDataFeed\Console\Commands\KissDataFeedInstaller;

class KissDataFeedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');
        $this->mergeConfigFrom(__DIR__.'/../Config/exporters.php', 'exporters');
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        Route::middleware('web')->group(__DIR__.'/../Routes/kiss-datafeed-routes.php');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'kiss_datafeed');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'kiss_datafeed');

        $this->app->register(ModuleServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                KissDataFeedInstaller::class,
            ]);
        }
    }
}
