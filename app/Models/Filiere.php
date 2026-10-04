<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    protected $fillable = [
        'name',
        'cycle_id',
        'responsable_id',
        'status',
        'code',
        'description',
        'departement_id'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public $timestamps = true;

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function specialites()
    {
        return $this->hasMany(Specialite::class, 'filiere_id');
    }

    public function salles()
    {
        return $this->hasMany(Salle::class, 'filiere_id');
    }

    public function ues()
    {
        return $this->hasMany(Ue::class, 'filiere_id');
    }
    public function departement()
{
    return $this->belongsTo(Departement::class);
}
public function cours() {
    return $this->hasMany(Cour::class);
}
}