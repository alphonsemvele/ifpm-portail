<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PortalDirectoryController;
use App\Http\Controllers\Auth\PortalSignOnController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
 * Entree depuis le portail La Majestueuse. C'est desormais le seul chemin
 * d'authentification : cette application n'a plus de formulaire de connexion
 * ni d'inscription.
 */
Route::get('sso/portail', PortalSignOnController::class)->name('sso.portail');

// Le portail y lit le catalogue des rôles et le personnel (jeton signé).
Route::get('portail/annuaire', PortalDirectoryController::class)->name('portail.annuaire');

Route::middleware('guest')->group(function () {
    /*
     * L'inscription et la connexion se font au portail. On y renvoie plutot
     * que d'exposer un second formulaire.
     *
     * L'acces de secours ne s'ouvre que si PORTAIL_FALLBACK_LOGIN=true, a
     * n'activer que si le portail est indisponible.
     */
    Route::get('register', fn () => redirect(config('portail.url').'/inscription'))->name('register');

    Route::get('login', function () {
        if (config('portail.fallback_login')) {
            return app(AuthenticatedSessionController::class)->create();
        }

        return redirect(config('portail.url').'/connexion');
    })->name('login');

    Route::post('login', function (\App\Http\Requests\Auth\LoginRequest $request) {
        abort_unless(config('portail.fallback_login'), 403, 'La connexion se fait depuis le portail.');

        return app(AuthenticatedSessionController::class)->store($request);
    });

    // Mot de passe oublie : c'est le portail qui detient l'identite.
    Route::get('forgot-password', fn () => redirect(config('portail.url').'/connexion'))
        ->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
