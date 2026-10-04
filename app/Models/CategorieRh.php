<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieRh extends Model
{
    protected $table = 'categories_rh';

    protected $fillable = [
        'libelle',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif'        => 'boolean',
    ];

    public function echelons()
    {
        return $this->hasMany(Echelon::class, 'categorie_rh_id')->orderBy('numero');
    }

    public function employes()
    {
        return $this->hasMany(User::class, 'categorie_rh_id');
    }

    public function profilSalaires()
    {
        return $this->hasMany(ProfilSalaire::class, 'categorie_rh_id');
    }
}