<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    protected $fillable = [
        'name',
        'institution',
        'status'
    ];

    protected $casts = [
        'institution' => 'string',
        'status' => 'string',
    ];

    public $timestamps = true; // Ajout des timestamps

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }
    public function filieres()
    {
        return $this->hasMany(Filiere::class, 'cycle_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'cycle_id');
    }

    public function salles()
    {
        return $this->hasMany(Salle::class, 'cycle_id');
    }
    public function cours() {
    return $this->hasMany(Cour::class);
}
}
