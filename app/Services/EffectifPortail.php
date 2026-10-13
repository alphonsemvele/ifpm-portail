<?php

namespace App\Services;

use App\Models\User;
use App\Support\Portail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Reception de l'effectif envoye par le portail La Majestueuse.
 *
 * Le portail est la seule autorite sur les comptes : il nous dit qui travaille
 * ici, et nous recopions. On cree le compte local AVANT la premiere connexion,
 * pour que l'administration puisse affecter l'agent a un departement, a une
 * filiere ou a une specialite, lui demander un document, le placer dans un
 * emploi du temps — sans attendre qu'il se soit connecte une fois.
 *
 * Trois regles tiennent tout :
 *
 *  1. on ne supprime jamais un compte — un enseignant retire du portail garde
 *     ses affectations, ses seances passees et ses depots ;
 *  2. un agent absent d'une liste complete perd seulement le droit d'entrer ;
 *  3. le matricule et l'adresse locaux ne sont pas ecrases : d'autres tables
 *     s'y referent, et l'institut a son propre plan de matricules.
 */
class EffectifPortail
{
    /** Comptes que le portail ne gouverne pas : la scolarite les gere ici. */
    private const ETUDIANTS = ['student', 'etudiant'];

    /**
     * Applique une liste recue du portail.
     *
     * @param  array<string, mixed>  $charge
     * @return array<string, int>
     */
    public function appliquer(array $charge): array
    {
        $resultat = ['crees' => 0, 'mis_a_jour' => 0, 'desactives' => 0, 'ignores' => 0, 'erreurs' => 0];
        $agents = is_array($charge['effectif'] ?? null) ? $charge['effectif'] : [];
        $complet = (bool) ($charge['complet'] ?? false);

        return DB::transaction(function () use ($agents, $complet, $resultat) {
            $vus = [];

            foreach ($agents as $agent) {
                if (! is_array($agent) || blank($agent['portail_id'] ?? null)) {
                    $resultat['ignores']++;

                    continue;
                }

                /*
                 * Un agent qui resiste ne doit pas emporter les autres : la
                 * liste entiere echouait sur une seule adresse deja prise.
                 */
                try {
                    $user = $this->retrouver($agent);

                    if ($user) {
                        $this->mettreAJour($user, $agent);
                        $resultat['mis_a_jour']++;
                    } else {
                        $user = $this->creer($agent);
                        $resultat['crees']++;
                    }
                } catch (Throwable $e) {
                    Log::warning('Effectif : agent non repris.', [
                        'portail_id' => $agent['portail_id'],
                        'raison' => $e->getMessage(),
                    ]);
                    $resultat['erreurs']++;

                    continue;
                }

                $vus[] = $user->id;
            }

            /*
             * Liste complete : qui n'y figure plus n'a plus acces. On ferme la
             * connexion, on ne supprime rien, et on ne touche qu'aux comptes
             * que le portail nous a deja annonces — les comptes locaux
             * d'avant le raccordement ne sont pas concernes.
             */
            if ($complet) {
                $resultat['desactives'] = User::whereNotNull('portail_id')
                    ->whereNotIn('id', $vus ?: [0])
                    ->where(fn ($q) => $q->where('peut_se_connecter', true)->orWhere('dans_le_personnel', true))
                    // Il n'est plus rattache a l'institut : il n'entre plus, et
                    // il quitte le personnel. Son compte et son histoire restent.
                    ->update($this->colonnesConnues(['peut_se_connecter' => false, 'dans_le_personnel' => false]));
            }

            Cache::forever('portail.effectif.recu_le', now()->toDateTimeString());

            return $resultat;
        });
    }

    /**
     * Ne garde que les colonnes que cette application possede vraiment.
     *
     * Les trois instituts partagent le meme socle mais pas la meme table :
     * `sexe` n'existe qu'a IUM, par exemple. Le portail, lui, envoie la meme
     * fiche a tout le monde — on y prend ce qui a un sens ici.
     *
     * @param  array<string, mixed>  $valeurs
     * @return array<string, mixed>
     */
    private function colonnesConnues(array $valeurs): array
    {
        static $connues = null;
        $connues ??= array_flip(Schema::getColumnListing((new User)->getTable()));

        return array_intersect_key($valeurs, $connues);
    }

    /**
     * Le compte local correspondant, s'il existe deja.
     *
     * @param  array<string, mixed>  $agent
     */
    private function retrouver(array $agent): ?User
    {
        $personnel = fn () => User::whereNotIn('role', self::ETUDIANTS);

        if ($user = $personnel()->where('portail_id', $agent['portail_id'])->first()) {
            return $user;
        }

        // La reference est l'identifiant que le portail a retenu pour nous
        // lors de la synchronisation de l'annuaire : matricule ou adresse.
        if (filled($agent['reference'] ?? null)) {
            $user = $personnel()
                ->where(fn ($q) => $q->where('matricule', $agent['reference'])->orWhere('email', $agent['reference']))
                ->first();
        }

        if (! $user && filled($agent['matricule'] ?? null)) {
            $user = $personnel()->where('matricule', $agent['matricule'])->first();
        }

        if (! $user && filled($agent['email'] ?? null)) {
            $user = $personnel()->where('email', $agent['email'])->first();
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $agent
     */
    private function mettreAJour(User $user, array $agent): void
    {
        $valeurs = $this->identite($agent);

        // Les cles de rattachement locales restent : des notes, des seances et
        // des bulletins s'y referent.
        unset($valeurs['matricule'], $valeurs['email']);

        /*
         * Le portail peut fermer une porte, pas la rouvrir : si l'institut a
         * desactive la connexion d'un agent de son cote, cette decision tient.
         * On n'ecrit donc que le refus.
         */
        if ($agent['peut_se_connecter'] ?? false) {
            unset($valeurs['peut_se_connecter']);
        }

        $user->forceFill($this->colonnesConnues($valeurs + ['portail_id' => $agent['portail_id']]))->save();
    }

    /**
     * @param  array<string, mixed>  $agent
     */
    private function creer(array $agent): User
    {
        /*
         * La colonne email est obligatoire et unique ici. A defaut d'adresse
         * professionnelle — ou si celle du portail est deja prise dans cette
         * base, souvent par un compte etudiant — on en derive une. L'adresse
         * vraie reste celle du portail, qui fait foi.
         */
        $derivee = (($agent['matricule'] ?? null) ?: 'portail-'.$agent['portail_id']).'@portail.local';
        $email = filled($agent['email'] ?? null) ? $agent['email'] : $derivee;

        if (User::where('email', $email)->exists()) {
            Log::info("Effectif : adresse deja prise, une adresse derivee est utilisee.", [
                'portail_id' => $agent['portail_id'], 'email' => $email,
            ]);
            $email = User::where('email', $derivee)->exists()
                ? 'portail-'.$agent['portail_id'].'@portail.local'
                : $derivee;
        }

        /*
         * L'adresse retenue ci-dessus l'emporte sur celle que porte l'identite :
         * avec l'operateur +, c'est la gauche qui gagne, et l'adresse deja
         * prise revenait par la fenetre.
         */
        $valeurs = array_merge($this->identite($agent), [
            'portail_id' => $agent['portail_id'],
            'email' => $email,
            'matricule' => $agent['matricule'] ?? null,
            // Aucun mot de passe utilisable : on n'entre que par le portail.
            'password' => bcrypt(Str::random(48)),
        ]);

        return User::forceCreate($this->colonnesConnues($valeurs));
    }

    /**
     * Ce que le portail nous dicte, a chaque reception.
     *
     * @param  array<string, mixed>  $agent
     * @return array<string, mixed>
     */
    private function identite(array $agent): array
    {
        $roles = Portail::rolesConnus((array) ($agent['roles'] ?? []));

        return [
            'name' => $agent['prenom'] ?? 'Employé',
            'lastname' => $agent['nom'] ?? null,
            'matricule' => $agent['matricule'] ?? null,
            'email' => $agent['email'] ?? null,
            'contact' => $agent['telephone'] ?? null,
            'poste' => $agent['poste'] ?? null,
            'entite' => $agent['entite'] ?? null,
            'sexe' => in_array($agent['sexe'] ?? null, ['M', 'F'], true) ? $agent['sexe'] : null,
            'status' => 'Success',
            // Le portail distingue ses employes de ses comptes techniques :
            // un administrateur du portail entre ici sans etre du personnel.
            'dans_le_personnel' => (bool) ($agent['dans_le_personnel'] ?? true),
            // Le portail decide qui entre : compte en attente de validation ou
            // suspendu chez lui, pas d'entree ici.
            'peut_se_connecter' => (bool) ($agent['peut_se_connecter'] ?? false),
            'role' => $roles ? config("roles.{$roles[0]}.role_local", $roles[0]) : 'personnel',
            'roles' => $roles ?: null,
        ];
    }

    /**
     * Va chercher la liste au portail et l'applique.
     *
     * Le portail diffuse deja a chaque changement d'acces ; ceci sert quand
     * l'institut etait hors ligne ce jour-la, ou tout simplement la premiere
     * fois — avant la premiere diffusion, l'institut ne connait personne.
     *
     * @return array<string, mixed> le resultat, ou ['erreur' => '…']
     */
    public function recupererDuPortail(): array
    {
        if (blank(config('portail.client_id')) || blank(config('portail.client_secret'))) {
            return ['erreur' => "La liaison avec le portail n'est pas configurée (PORTAIL_CLIENT_ID et PORTAIL_CLIENT_SECRET)."];
        }

        try {
            $reponse = Http::acceptJson()->timeout(30)
                ->withToken(Portail::signer(['usage' => 'effectif']))
                ->get(rtrim((string) config('portail.url'), '/').'/api/applications/effectif');
        } catch (Throwable $e) {
            Log::warning('Effectif : le portail ne répond pas.', ['raison' => $e->getMessage()]);

            return ['erreur' => 'Le portail ne répond pas.'];
        }

        if ($reponse->failed()) {
            return ['erreur' => 'Le portail a refusé la demande ('.$reponse->status().') : '
                .($reponse->json('message') ?? 'réponse inattendue').'.'];
        }

        return $this->appliquer((array) $reponse->json());
    }

    /** Date de la derniere liste recue, pour l'ecran d'administration. */
    public static function recuLe(): ?string
    {
        return Cache::get('portail.effectif.recu_le');
    }
}
