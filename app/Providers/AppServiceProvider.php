<?php

namespace App\Providers;

use App\Models\Device;
use App\Observers\DeviceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Device::observe([
            DeviceObserver::class,
        ]);
    }
}
