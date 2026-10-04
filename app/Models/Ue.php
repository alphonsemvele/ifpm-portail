<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ue extends Model
{
    protected $fillable = [
        'name',
        'code',
        'specialite_id',
        'filiere_id',
        'cycle_id',
        'credits',
        'status',
         'hour_number',
         'examen_id'
    ];

    protected $casts = [
        'status'  => 'string',
        'credits' => 'integer',
    ];

    public $timestamps = true;

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function cours()
    {
        return $this->hasMany(Cour::class, 'ue_id');
    }

    /** Alias utilise par les espaces filiere et specialite. */
    public function cour()
    {
        return $this->cours();
    }
    
    public function examen()
{
    return $this->belongsTo(Examen::class, 'examen_id');
}
}