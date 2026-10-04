<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une ligne de l'échéancier : inscription, associations, visite médicale,
 * puis les huit versements de scolarité.
 */
class Versement extends Model
{
    protected $fillable = [
        'inscription_id', 'rang', 'libelle', 'montant_prevu', 'montant', 'date_versement', 'vise', 'penalite',
    ];

    protected $casts = [
        'date_versement' => 'date',
        'vise' => 'boolean',
        'montant_prevu' => 'decimal:2',
        'montant' => 'decimal:2',
        'penalite' => 'decimal:2',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function estRegle(): bool
    {
        return (float) $this->montant > 0 && $this->date_versement !== null;
    }
}
