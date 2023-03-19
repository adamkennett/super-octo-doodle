<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ExternalMovieServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //We could swap out to a different api like this
        $this->app->bind(
            'App\External\Interfaces\ExternalMovieApiServiceInterface',
            'App\External\TheMovieDatabaseApiService'
//            'App\External\OpenMovieApiService'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
