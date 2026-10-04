<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une session de concours : « Concours du 19 septembre 2026 ».
 * Les candidats s'y rattachent, et la liste officielle en découle.
 */
class SessionConcours extends Model
{
    protected $table = 'sessions_concours';

    protected $fillable = [
        'libelle', 'date_concours', 'annee', 'lieu', 'type', 'statut', 'observations',
    ];

    protected $casts = [
        'date_concours' => 'date',
    ];

    public function candidats()
    {
        return $this->hasMany(Candidat::class, 'session_id');
    }

    public function estOuverte(): bool
    {
        return $this->statut === 'ouverte';
    }
}
