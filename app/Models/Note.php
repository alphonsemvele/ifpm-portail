<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = [
        'examen_id',
        'etudiant_id',
        'valeur',         // note sur 20
        'commentaire',
        'validee_par',    // id de l'utilisateur qui a validé (enseignant/responsable)
        'validee_at',
        'cours_id',
        'cc',
        'exam',
        'rattrapage', 
    ];

    protected $casts = [
        'validee_at' => 'datetime',
    ];

    // Relations
    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cour::class, 'cours_id');
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

    // Helper : note formatée
    public function getNoteAfficheeAttribute(): string
    {
        return $this->valeur !== null ? number_format($this->valeur, 2) . ' / 20' : '—';
    }
}