<?php

use App\Support\Portail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Envoie au portail le catalogue des rôles (config/roles.php) et le personnel.
 * Le portail les propose ensuite à l'administrateur pour l'attribution.
 */
Artisan::command('portail:synchroniser', function () {
    if (blank(config('portail.client_id')) || blank(config('portail.client_secret'))) {
        $this->error('PORTAIL_CLIENT_ID et PORTAIL_CLIENT_SECRET doivent être renseignés dans .env.');

        return 1;
    }

    $reponse = Http::acceptJson()->timeout(30)
        ->withToken(Portail::signer(['usage' => 'synchronisation']))
        ->post(rtrim((string) config('portail.url'), '/').'/api/applications/synchronisation', Portail::annuaire());

    if ($reponse->failed()) {
        $this->error('Le portail a refusé la synchronisation ('.$reponse->status().') : '.($reponse->json('message') ?? $reponse->body()));

        return 1;
    }

    $r = $reponse->json('resultat', []);
    $this->info(sprintf(
        '%d rôle(s) transmis · %d compte(s) créé(s) · %d rattaché(s) · %d déjà lié(s) · %d ignoré(s)',
        $r['roles'] ?? 0, $r['crees'] ?? 0, $r['rattaches'] ?? 0, $r['deja_lies'] ?? 0, $r['ignores'] ?? 0,
    ));

    return 0;
})->purpose('Envoie le catalogue des rôles et le personnel au portail La Majestueuse');
