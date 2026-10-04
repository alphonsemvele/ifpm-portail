<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Configuration extends Model
{
    /**
     * Les attributs remplissables du modèle.
     *
     * @var array
     */
    protected $fillable = ['name', 'code', 'status'];

    /**
     * Les attributs à caster.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string', // Assure que le statut est traité comme une chaîne
    ];

    /**
     * Relation many-to-many avec Section via Configurationsection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'configurationsections', 'configuration_id', 'section_id')
                    ->withPivot('level', 'status')
                    ->wherePivot('status', '!=', 'failed')
                    ->orderByPivot('level', 'asc');
    }
}
