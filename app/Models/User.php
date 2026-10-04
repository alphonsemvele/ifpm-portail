<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'lastname',
        'contact',
        'whatsapp',
        'email',
        'role',
        'matricule',
        'filiere_id',
        'specialite_id',
        'cycle_id',
        'picture',
        'password',
        'region_id',
        'department_id',
        'arrondissement_id',
        'status',
        'father_name',
        'father_contact',
        'mother_name',
        'mother_contact',
        'poste',
        'entite',
        'photo',
        'cropped_photo',
        'section_id',
        'departement_id',
        'profil_salaire_id',
        'categorie_rh_id',
        'echelon_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'roles'             => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Un rôle modifié dans l'application (page Personnel) remplace ceux venus du portail.
        static::saving(function (User $user) {
            if ($user->isDirty('role') && ! $user->isDirty('roles')) {
                $user->roles = null;
            }
        });
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissement_id');
    }

    public function supports()
    {
        return $this->hasMany(Support::class, 'user_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'etudiant_id');
    }

    public function profilSalaire()
    {
        return $this->belongsTo(ProfilSalaire::class, 'profil_salaire_id');
    }

    public function categorieRh()
    {
        return $this->belongsTo(CategorieRh::class, 'categorie_rh_id');
    }

    public function echelon()
    {
        return $this->belongsTo(Echelon::class, 'echelon_id');
    }

    public function paiements()
    {
        return $this->hasMany(PaiementSalaire::class, 'user_id');
    }

    /* ===================================================================
     |  Rôles & accès aux espaces
     * =================================================================== */

    /** Postes ayant accès uniquement au module RH & Paie. */
    public const FINANCE_POSTES = ['dir_aaf', 'dir_rh', 'comptable', 'daf_ifpm'];

    /**
     * Rôles détenus. Ils viennent du portail (colonne `roles`, plusieurs
     * possibles) ; à défaut, le rôle unique de la colonne `role`.
     *
     * @return array<int, string>
     */
    public function rolesDetenus(): array
    {
        $roles = array_values(array_filter((array) ($this->roles ?? [])));

        return $roles ?: [$this->role === 'etudiant' ? 'student' : (string) $this->role];
    }

    /** L'utilisateur détient-il au moins l'un de ces rôles ? */
    public function aLeRole(string ...$codes): bool
    {
        return (bool) array_intersect($codes, $this->rolesDetenus());
    }

    /** Libellés des rôles détenus, pour l'affichage. */
    public function libellesRoles(): string
    {
        return collect($this->rolesDetenus())
            ->map(fn ($role) => config("roles.{$role}.libelle", $role === 'student' ? 'Apprenant' : ucfirst($role)))
            ->implode(' · ');
    }

    /** L'utilisateur fait-il partie du personnel finance / RH ? */
    public function isFinanceStaff(): bool
    {
        return in_array($this->poste, self::FINANCE_POSTES, true) || $this->aLeRole('finance');
    }

    /** Espace principal : il décide de l'accueil. */
    public function espace(): string
    {
        return match (true) {
            $this->aLeRole('admin')               => 'admin',
            $this->aLeRole('student', 'etudiant') => 'eleve',
            $this->isFinanceStaff()               => 'finance',
            default                               => 'personnel',
        };
    }

    /**
     * Espaces dont le menu est assemblé. Chaque lien reste filtré par
     * allowedPrefixes : l'espace « admin » n'apporte à un non-administrateur
     * que les pages que l'un de ses rôles lui ouvre.
     *
     * @return array<int, string>
     */
    public function espacesMenu(): array
    {
        $espace = $this->espace();

        if (in_array($espace, ['admin', 'eleve'], true)) {
            return [$espace];
        }

        $espaces = [$espace];

        if (array_diff($this->rolesDetenus(), ['finance']) || ! $this->isFinanceStaff()) {
            $espaces[] = 'personnel';
        }

        $espaces[] = 'admin';

        return array_values(array_unique($espaces));
    }

    /** Chemin de l'espace d'accueil de l'utilisateur après connexion. */
    public function homePath(): string
    {
        return match ($this->espace()) {
            'admin'   => '/admin',
            'eleve'   => '/dashboard',
            'finance' => '/finance',
            // Tout le personnel (coordonnateur, enseignant, personnel, concierge…)
            // atterrit sur l'espace personnel : bulletin de paie + profil.
            default   => '/personnel',
        };
    }

    /** L'utilisateur est-il affilié à une filière / coordination ? */
    public function hasFiliereModule(): bool
    {
        return $this->aLeRole('coordonnateur', 'coordinateur', 'filiere')
            || !empty($this->filiere_id) || !empty($this->departement_id);
    }

    /** L'utilisateur est-il affilié à une spécialité / enseignement ? */
    public function hasSpecialiteModule(): bool
    {
        return $this->aLeRole('enseignant', 'specialite')
            || !empty($this->specialite_id);
    }

    /**
     * Préfixes de chemins autorisés pour l'utilisateur : l'union de ceux de
     * chacun de ses rôles (config/roles.php).
     * Utilisé par le middleware `role` pour cloisonner les espaces.
     */
    public function allowedPrefixes(): array
    {
        // Routes partagées, accessibles à tout utilisateur connecté.
        $shared = ['logout', 'profile', 'profile/*', 'bulletin/*', 'bulletins/*'];

        if ($this->aLeRole('admin')) {
            return ['*'];
        }

        if ($this->aLeRole('student', 'etudiant')) {
            return array_merge($shared, ['dashboard', 'dashboard/*']);
        }

        // Personnel : espace personnel + accès apportés par chaque rôle.
        $prefixes = array_merge($shared, ['personnel', 'personnel/*']);

        if ($this->isFinanceStaff()) {
            $prefixes = array_merge($prefixes, config('roles.finance.acces', []));
        }
        foreach ($this->rolesDetenus() as $role) {
            $prefixes = array_merge($prefixes, config("roles.{$role}.acces", []));
        }
        if ($this->hasFiliereModule()) {
            $prefixes = array_merge($prefixes, ['filiere', 'filiere/*']);
        }
        if ($this->hasSpecialiteModule()) {
            $prefixes = array_merge($prefixes, ['specialite', 'specialite/*']);
        }

        return array_values(array_unique($prefixes));
    }

    /** L'utilisateur peut-il accéder à la requête courante ? */
    public function canAccess(\Illuminate\Http\Request $request): bool
    {
        foreach ($this->allowedPrefixes() as $prefix) {
            if ($prefix === '*' || $request->is($prefix)) {
                return true;
            }
        }

        return false;
    }
}