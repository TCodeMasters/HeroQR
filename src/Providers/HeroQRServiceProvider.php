<?php

namespace HeroQR\Providers;

use HeroQR\Core\QRCodeGenerator;
use Illuminate\Support\ServiceProvider;

/**
 * Registers and bootstraps the QR code generation services for HeroQR package in Laravel
 */
class HeroQRServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services
     *
     * @return void
     */
    public function boot()
    {
        // Load routes, config, or other resources here if needed
        // e.g. $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Register any application services
     *
     * @return void
     */
    public function register()
    {
        // Register the QRCodeGenerator as a singleton
        $this->app->singleton(QRCodeGenerator::class, function ($app) {
            return new QRCodeGenerator();
        });
    }
}
