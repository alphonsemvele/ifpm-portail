<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Portail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Entree depuis le portail La Majestueuse.
 *
 * Le portail signe une assertion courte avec le secret partage ; on la
 * verifie, on retrouve ou on cree le compte local, puis on ouvre la session.
 * L'employe arrive directement dans son espace.
 */
class PortalSignOnController extends Controller
{
    /** Correspondance entre un ancien role unique transmis par le portail et les roles locaux. */
    private const ROLES = [
        'admin' => 'admin',
        'coordonnateur' => 'coordonnateur',
        'coordinateur' => 'coordonnateur',
        'enseignant' => 'enseignant',
        'etudiant' => 'student',
        'student' => 'student',
        'bibliothecaire' => 'bibliothecaire',
        'concierge' => 'concierge',
        'personnel' => 'personnel',
        'filiere' => 'filiere',
        'specialite' => 'specialite',
    ];

    public function __invoke(Request $request): RedirectResponse
    {
        $secret = config('portail.client_secret');

        if (blank($secret)) {
            Log::warning('SSO portail : aucun secret configuré.');

            return $this->refus("La liaison avec le portail n'est pas configurée.");
        }

        try {
            $assertion = JWT::decode((string) $request->query('token'), new Key($secret, 'HS256'));
        } catch (Throwable $e) {
            Log::warning('SSO portail : jeton rejeté.', ['raison' => $e->getMessage()]);

            return $this->refus('Lien de connexion invalide ou expiré.');
        }

        if (($assertion->aud ?? null) !== config('portail.client_id')) {
            return $this->refus("Ce lien de connexion ne concerne pas cette application.");
        }

        // Un jeton ne sert qu'une fois : on retient son identifiant le temps
        // de sa validite pour empecher qu'un lien recopie soit rejoue.
        $jeton = 'sso-portail:'.($assertion->jti ?? '');

        if (blank($assertion->jti ?? null) || Cache::has($jeton)) {
            return $this->refus('Ce lien de connexion a déjà été utilisé.');
        }

        Cache::put($jeton, true, now()->addMinutes(5));

        $user = $this->comptePour($assertion);

        // Les decisions prises dans l'application restent souveraines : un compte
        // supprime ou dont la connexion a ete desactivee n'entre pas, meme par le portail.
        if ($user->status === 'failed') {
            return $this->refus("Votre compte a été supprimé dans cette application. Contactez l'administration.");
        }

        if ($user->getAttribute('peut_se_connecter') === false) {
            return $this->refus("Votre connexion à cette application a été désactivée. Contactez l'administration.");
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->homePath())
            ->with('success', 'Bienvenue '.($user->name ?? '').' !');
    }

    /**
     * Retrouve le compte local, ou le cree. Le portail fait foi pour
     * l'identite : on reprend ses valeurs a chaque entree.
     */
    private function comptePour(object $a): User
    {
        $user = null;

        /*
         * On cherche d'abord par la reference que le portail a enregistree
         * pour cette application : c'est la seule cle fiable, les matricules
         * et les adresses ne coincidant pas d'une application a l'autre.
         */
        if (filled($a->reference ?? null)) {
            $user = User::where('matricule', $a->reference)->orWhere('email', $a->reference)->first();
        }

        if (! $user && filled($a->matricule ?? null)) {
            $user = User::where('matricule', $a->matricule)->first();
        }

        if (! $user && filled($a->email ?? null)) {
            $user = User::where('email', $a->email)->first();
        }

        // La colonne email est obligatoire ici : a defaut d'adresse, on en
        // derive une depuis le matricule.
        $email = $a->email ?? (($a->matricule ?? Str::random(8)).'@portail.local');

        $valeurs = [
            'name' => $a->prenom ?? 'Employé',
            'lastname' => $a->nom ?? null,
            'email' => $email,
            'matricule' => $a->matricule ?? null,
            'contact' => $a->telephone ?? null,
            'poste' => $a->poste ?? null,
            'entite' => $a->entite ?? null,
            'status' => 'Success',
        ];

        /*
         * Les roles viennent du portail : c'est lui qui decide de ce que l'employe
         * est ici. Il peut en attribuer plusieurs (catalogue config/roles.php) ;
         * le premier dans l'ordre du catalogue devient le role principal.
         * S'il n'en transmet aucun, le compte garde les siens.
         */
        $roles = Portail::rolesConnus(array_merge((array) ($a->roles ?? []), [$a->role ?? null]));
        $role = $roles
            ? config("roles.{$roles[0]}.role_local", $roles[0])
            : (self::ROLES[strtolower((string) ($a->role ?? ''))] ?? null);

        if ($user) {
            // Le compte local garde son matricule et son adresse : ce sont ses
            // cles de rattachement, et d'autres tables peuvent s'y referer.
            unset($valeurs['matricule'], $valeurs['email']);

            // Seul un compte en attente est valide par le portail ; un compte
            // supprime garde son statut.
            if ($user->status !== 'pending') {
                unset($valeurs['status']);
            }

            if ($role !== null) {
                $valeurs['role'] = $role;
                $valeurs['roles'] = $roles ?: null;
            }

            $user->forceFill($valeurs)->save();

            return $user;
        }

        return User::forceCreate($valeurs + [
            'role' => $role ?? 'personnel',
            'roles' => $roles ?: null,
            // Aucun mot de passe utilisable : on n'entre que par le portail.
            'password' => bcrypt(Str::random(48)),
        ]);
    }

    private function refus(string $message): RedirectResponse
    {
        return redirect(config('portail.url'))->with('error', $message);
    }
}
