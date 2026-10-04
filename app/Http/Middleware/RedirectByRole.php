<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si pas connecté, on laisse passer (login, etc.)
        if (!$user) {
            return $next($request);
        }

        // Redirection selon le rôle (priorité : admin > coordonnateur > enseignant > étudiant)
        if ($user->role === 'admin') {
            return redirect('/admin')->with('success', 'Bienvenue dans l\'espace administrateur');
        }

        if ($user->role === 'coordinateur') {
            return redirect('/filiere')->with('success', 'Bienvenue dans l\'espace coordinateur');
        }

        if ($user->role === 'enseignant') {
            return redirect('/specialite')->with('success', 'Bienvenue dans l\'espace enseignant');
        }

        if ($user->role === 'etudiant') {
            return redirect('/dashboard')->with('success', 'Bienvenue dans votre espace étudiant');
        }

        // Si rôle inconnu → page d'accueil ou erreur
        return redirect('/')->with('error', 'Rôle non reconnu. Contactez l\'administrateur.');
    }
}