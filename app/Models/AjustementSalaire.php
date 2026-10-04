<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AjustementSalaire extends Model
{
    protected $table = 'ajustements_salaire';

    protected $fillable = [
        'user_id', 'mois', 'annee', 'type', 'mode', 'libelle', 'montant', 'motif', 'created_by',
    ];

    protected $casts = [
        'montant' => 'float',
        'mois'    => 'integer',
        'annee'   => 'integer',
    ];

    public function employe()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePeriode($q, int $mois, int $annee)
    {
        return $q->where('mois', $mois)->where('annee', $annee);
    }
}
