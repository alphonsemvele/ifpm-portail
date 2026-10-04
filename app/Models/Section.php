<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Section extends Model
{
    /**
     * Les attributs remplissables du modèle.
     *
     * @var array
     */
    protected $fillable = ['name', 'abbreviation', 'code', 'status'];

    /**
     * Les attributs à caster.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string', // Assure que le statut est traité comme une chaîne
    ];

    /**
     * Relation many-to-many avec Configuration via Configurationsection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function configurations()
    {
        return $this->belongsToMany(Configuration::class, 'configurationsections', 'section_id', 'configuration_id')
                    ->withPivot('level', 'status')
                    ->wherePivot('status', '!=', 'failed')
                    ->orderByPivot('level', 'asc');
    }
    
    
     public function user()
    {
        return $this->hasMany(User::class, 'user_id');
    }
}
