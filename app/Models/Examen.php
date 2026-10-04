<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    protected $table = 'examens';

    protected $fillable = [
       
               // 'cc', 'examen', 'rattrapage', 'devoir', etc.
        'titre',
        'date',
      // ex: 0.4 pour CC, 0.6 pour examen final
           // 20 par défaut
        'statut',         // 'ouvert', 'ferme', 'en_cours', 'annule'
        'description',
        'cycle_id'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Relations
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cour::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    // Helper : nom complet du type
    
    public function ue()
{
    return $this->hasMany(Ue::class, 'examen_id');
}
 public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }
  
}