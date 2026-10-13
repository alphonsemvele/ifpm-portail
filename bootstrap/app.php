<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Role;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
          $middleware->alias([
        'role' => Role::class,
    ]);

        /*
         * Le portail nous depose l'effectif par une requete serveur a serveur :
         * il n'a pas de session chez nous, donc pas de jeton CSRF. Sa demande
         * est authentifiee autrement, par une signature avec le secret partage
         * (App\Support\Portail::verifier).
         */
        $middleware->validateCsrfTokens(except: [
            'portail/effectif',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
