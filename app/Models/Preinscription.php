<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preinscription extends Model
{
    protected $table = 'preinscriptions'; // Nom de la table

    protected $primaryKey = 'id';

    protected $fillable = [
        'formation_name',
        'cycle_id',
        'filiere_id',
        'specialite_id',
        'name',
        'last_name',
        'birth',
        'region_id',
        'departement_id',
        'arrondissement_id',
        'contact',
        'email',
        'father',
        'mother',
        'status',
        "isValidated",
        "price",
        "birth_certificate",
        "diploma",
        "ref",
        "payment_status",
        "price",
        "whatsapp_number",
        "parrain_name",
        "parrain_contact"
    ];

    protected $casts = [
        'birth' => 'date',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // Relations (exemples basés sur les foreign keys)
    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function departement()
    {
        return $this->belongsTo(Department::class, 'departement_id');
    }

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissement_id');
    }
}
