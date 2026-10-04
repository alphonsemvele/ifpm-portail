<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;
use App\Http\Middleware\Role; 

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
         Folio::path(resource_path('views/pages'))->middleware([
             '*' => [

                Role::class,
                //
             ],
         ]);
    }
}
