<?php

namespace App\Providers;

use App\Models\Arbeitszeit;
use App\Observers\ArbeitszeitObserver;
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
        Arbeitszeit::observe(ArbeitszeitObserver::class);
    }
}
