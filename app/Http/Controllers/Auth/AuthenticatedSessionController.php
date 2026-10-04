<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        
        $request->session()->regenerate();

        // V�rifier le statut de l'utilisateur
        $user = Auth::user();
        
        if ($user->status !== 'Success') {
            // D�connecter l'utilisateur
            Auth::guard('web')->logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Rediriger vers la page de connexion avec un message d'erreur
            return redirect()->route('login')->with('error', 'Votre compte n\'a pas encore ete valide par l\'administrateur. Veuillez patienter ou contacter le service concerne.');
        }

        // Redirection vers l'espace correspondant au rôle / poste de l'utilisateur.
        return redirect($user->homePath())
            ->with('success', 'Bienvenue ' . ($user->name ?? '') . ' !');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}