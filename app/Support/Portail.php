<?php

namespace App\Support;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Échanges signés avec le portail La Majestueuse : catalogue des rôles et
 * annuaire du personnel. Le secret partagé (PORTAIL_CLIENT_SECRET) signe
 * chaque message dans les deux sens.
 */
class Portail
{
    public const ALGORITHME = 'HS256';

    /**
     * Rôles déclarés dans config/roles.php, dans l'ordre de priorité.
     *
     * @return array<int, array{code: string, libelle: string, description: ?string}>
     */
    public static function catalogue(): array
    {
        return collect(config('roles', []))
            ->map(fn (array $role, string $code) => [
                'code' => $code,
                'libelle' => $role['libelle'] ?? $code,
                'description' => $role['description'] ?? null,
            ])
            ->values()
            ->all();
    }

    /**
     * Ne garde que les rôles connus, rangés dans l'ordre du catalogue.
     *
     * @param  iterable<mixed>  $codes
     * @return array<int, string>
     */
    public static function rolesConnus(iterable $codes): array
    {
        $alias = ['coordinateur' => 'coordonnateur'];
        $recus = collect($codes)
            ->map(fn ($code) => strtolower(trim((string) $code)))
            ->map(fn ($code) => $alias[$code] ?? $code)
            ->filter()
            ->all();

        return array_values(array_intersect(array_keys(config('roles', [])), $recus));
    }

    /**
     * Personnel de l'application (hors étudiants et comptes supprimés).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function personnel(): array
    {
        return User::whereNotIn('role', ['student', 'etudiant'])
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'failed'))
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'reference' => $u->matricule ?: $u->email,
                'matricule' => $u->matricule,
                'prenom' => $u->name,
                'nom' => $u->lastname,
                'email' => str_ends_with((string) $u->email, '@portail.local') ? null : $u->email,
                'telephone' => $u->contact,
                'poste' => $u->poste,
                'roles' => $u->rolesDetenus(),
            ])
            ->values()
            ->all();
    }

    /**
     * Charge utile commune à l'annuaire et à la synchronisation.
     *
     * @return array<string, mixed>
     */
    public static function annuaire(): array
    {
        return [
            'application' => config('portail.client_id'),
            'nom' => config('etablissement.sigle', config('app.name')),
            'roles' => self::catalogue(),
            'personnel' => self::personnel(),
        ];
    }

    public static function signer(array $charge): string
    {
        $maintenant = time();

        return JWT::encode($charge + [
            'iss' => config('portail.client_id'),
            'aud' => 'portail',
            'jti' => (string) Str::uuid(),
            'iat' => $maintenant,
            'exp' => $maintenant + 60,
        ], (string) config('portail.client_secret'), self::ALGORITHME);
    }

    /**
     * Vérifie un jeton émis par le portail pour l'usage attendu.
     */
    public static function verifier(?string $jeton, string $usage): object
    {
        $secret = config('portail.client_secret');

        if (blank($secret) || blank($jeton)) {
            throw new RuntimeException('Liaison avec le portail non configurée ou jeton absent.');
        }

        $charge = JWT::decode($jeton, new Key($secret, self::ALGORITHME));

        if (($charge->aud ?? null) !== config('portail.client_id') || ($charge->usage ?? null) !== $usage) {
            throw new RuntimeException('Jeton destiné à un autre usage.');
        }

        $cle = 'portail-jeton:'.($charge->jti ?? '');

        if (blank($charge->jti ?? null) || ! Cache::add($cle, true, now()->addMinutes(5))) {
            throw new RuntimeException('Jeton déjà utilisé.');
        }

        return $charge;
    }
}
