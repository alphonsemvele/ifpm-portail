<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configurationsection extends Model
{
    /**
     * Les attributs remplissables du modèle.
     *
     * @var array
     */
    protected $fillable = ['section_id', 'configuration_id', 'level', 'status'];

    /**
     * Les attributs à caster.
     *
     * @var array
     */
    protected $casts = [
        'level' => 'integer', // Assure que le niveau est traité comme un entier
        'status' => 'string', // Assure que le statut est traité comme une chaîne
    ];

    /**
     * Relation avec le modèle Section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Relation avec le modèle Configuration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function configuration()
    {
        return $this->belongsTo(Configuration::class);
    }
}
