<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'description',
        'responsable_id',
        'status',
        'cycle_id'
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Relation : un département a plusieurs filières
    public function filieres()
    {
        return $this->hasMany(Filiere::class);
    }

    // Pour accéder facilement à toutes les spécialités du département (via filières)
    public function specialites()
    {
        return $this->hasManyThrough(Specialite::class, Filiere::class);
    }

    // Pour accéder à toutes les UE (via spécialités)
    public function ues()
    {
        return $this->hasManyThrough(Ue::class, Specialite::class, 'departement_id', 'specialite_id');
        // Note : cette relation nécessite que Ue ait specialite_id
    }

    // Pour accéder à tous les cours (via UE)
    public function cours()
    {
        return $this->hasManyThrough(Cour::class, Ue::class, 'departement_id', 'ue_id');
        // Note : cette relation nécessite que Cour ait ue_id
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }
}