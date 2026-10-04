<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Echelon extends Model
{
    protected $fillable = [
        'categorie_rh_id',
        'numero',
        'libelle',
        'salaire',
        'anciennete_min',
        'actif',
    ];

    protected $casts = [
        'salaire'        => 'float',
        'actif'          => 'boolean',
        'anciennete_min' => 'integer',
        'numero'         => 'integer',
    ];

    public function categorie()
    {
        return $this->belongsTo(CategorieRh::class, 'categorie_rh_id');
    }

    public function employes()
    {
        return $this->hasMany(User::class, 'echelon_id');
    }

    public function getSalaireFormateAttribute(): string
    {
        return number_format($this->salaire, 0, ',', ' ') . ' FCFA';
    }
}