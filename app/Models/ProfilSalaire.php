<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilSalaire extends Model
{
    protected $table = 'profil_salaires';

    protected $fillable = [
        'nom',
        'description',
        'categorie_rh_id',
        'echelon_id',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function categorie()
    {
        return $this->belongsTo(CategorieRh::class, 'categorie_rh_id');
    }

    public function echelon()
    {
        return $this->belongsTo(Echelon::class, 'echelon_id');
    }

    public function indemnites()
    {
        return $this->belongsToMany(Indemnite::class, 'profil_salaire_indemnite', 'profil_salaire_id', 'indemnite_id')
                    ->withPivot('type_calcul', 'value')
                    ->withTimestamps();
    }

    public function retenues()
    {
        return $this->belongsToMany(Retenue::class, 'profil_salaire_retenue', 'profil_salaire_id', 'retenue_id')
                    ->withPivot('type_calcul', 'value')
                    ->withTimestamps();
    }

    public function employes()
    {
        return $this->hasMany(User::class, 'profil_salaire_id');
    }

    /** Salaire de base = échelon rattaché au profil */
    public function getSalaireBaseAttribute(): float
    {
        return (float)($this->echelon?->salaire ?? 0);
    }

    public function getSalaireNetAttribute(): float
    {
        $base = $this->salaire_base;
        $ind  = $this->indemnites->sum(fn($i) => $i->pivot->type_calcul === 'fixe'
            ? $i->pivot->value : round($base * $i->pivot->value / 100));
        $ret  = $this->retenues->sum(fn($r) => $r->pivot->type_calcul === 'fixe'
            ? $r->pivot->value : round($base * $r->pivot->value / 100));
        return $base + $ind - $ret;
    }
}