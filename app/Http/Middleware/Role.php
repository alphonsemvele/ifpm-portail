<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Pas connecté → laisser passer (les middlewares auth/verified gèrent).
        if (!$user) {
            return $next($request);
        }

        // L'utilisateur peut-il accéder à cet espace ?
        if ($user->canAccess($request)) {
            return $next($request);
        }

        // Sinon, on le renvoie vers son espace d'accueil.
        return redirect($user->homePath())
            ->with('info', 'Vous avez été redirigé vers votre espace.');
    }
}
