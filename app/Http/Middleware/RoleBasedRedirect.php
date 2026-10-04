<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedRedirect
{
    /**
     * Tableau de redirection par rôle.
     * Ajoute facilement de nouveaux rôles ici sans toucher au code principal.
     */
    protected array $redirectMap = [
        'admin'      => '/admin',
        'coordinateur' => '/filiere',   // ou 'filiere' si tu utilises ce nom
        'enseignant' => '/specialite',
        'etudiant'   => '/dashboard',
        // Ajoute d'autres rôles si besoin
        // 'superadmin' => '/super-dashboard',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pas d'utilisateur connecté → on passe au middleware suivant
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $role = $user->role ?? null; // Protection si role est null

        // Si le rôle existe dans la map → redirection
        if ($role && array_key_exists($role, $this->redirectMap)) {
            return redirect($this->redirectMap[$role]);
        }

        // Rôle inconnu ou non géré → on laisse passer (ou on peut rediriger vers une page par défaut)
        return $next($request);
    }
}