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
        $this->app->bind(
            \App\Repositories\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\SellerRepositoryInterface::class,
            \App\Repositories\Eloquent\SellerRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\SaleRepositoryInterface::class,
            \App\Repositories\Eloquent\SaleRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
