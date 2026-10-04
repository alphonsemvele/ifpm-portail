<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementSalaire extends Model
{
    protected $table = 'paiements_salaires';

    protected $fillable = [
        'user_id', 'profil_salaire_id', 'echelon_id',
        'mois', 'annee',
        'salaire_base', 'total_indemnites', 'total_retenues', 'salaire_net',
        'detail_json',
        'statut',
        'valide_par', 'valide_le',
        'paye_par',   'paye_le',
        'note',
    ];

    protected $casts = [
        'detail_json' => 'array',
        'valide_le'   => 'datetime',
        'paye_le'     => 'datetime',
        'salaire_base'     => 'float',
        'total_indemnites' => 'float',
        'total_retenues'   => 'float',
        'salaire_net'      => 'float',
    ];

    public function employe()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function profil()
    {
        return $this->belongsTo(ProfilSalaire::class, 'profil_salaire_id');
    }

    public function echelon()
    {
        return $this->belongsTo(Echelon::class, 'echelon_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function payePar()
    {
        return $this->belongsTo(User::class, 'paye_par');
    }

    public function getMoisNomAttribute(): string
    {
        $mois = [
            1 => 'Janvier', 2 => 'Février',   3 => 'Mars',
            4 => 'Avril',   5 => 'Mai',        6 => 'Juin',
            7 => 'Juillet', 8 => 'Août',       9 => 'Septembre',
            10 => 'Octobre',11 => 'Novembre',  12 => 'Décembre',
        ];
        return $mois[$this->mois] ?? '';
    }

    public function getStatutBadgeAttribute(): array
    {
        return match($this->statut) {
            'en_attente' => ['label' => 'En attente', 'bg' => '#fef9c3', 'color' => '#92400e'],
            'valide'     => ['label' => 'Validé',     'bg' => '#dbeafe', 'color' => '#1d4ed8'],
            'paye'       => ['label' => 'Payé',       'bg' => '#dcfce7', 'color' => '#15803d'],
            default      => ['label' => $this->statut,'bg' => '#f3f4f6', 'color' => '#374151'],
        };
    }
}