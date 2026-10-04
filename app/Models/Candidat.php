<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Candidat au concours : reprend la fiche de renseignement IFPM-NDAZOA.
 */
class Candidat extends Model
{
    protected $fillable = [
        'session_id', 'date_inscription', 'nom', 'prenoms', 'sexe', 'date_naissance',
        'lieu_naissance', 'cni', 'nationalite', 'profession', 'situation_familiale',
        'profession_conjoint', 'region_origine', 'departement_origine', 'ville', 'quartier',
        'telephone', 'whatsapp', 'email', 'langue', 'admission',
        'filiere1_id', 'filiere2_id', 'filiere3_id',
        'diplome', 'date_obtention', 'niveau',
        'piece_fiche', 'piece_acte', 'piece_diplome', 'piece_photos', 'piece_enveloppe',
        'statut', 'observations', 'cle_doublon', 'saisi_par',
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'date_naissance' => 'date',
        'date_obtention' => 'date',
        'piece_fiche' => 'boolean',
        'piece_acte' => 'boolean',
        'piece_diplome' => 'boolean',
        'piece_photos' => 'boolean',
        'piece_enveloppe' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(SessionConcours::class, 'session_id');
    }

    public function filiere1()
    {
        return $this->belongsTo(Filiere::class, 'filiere1_id');
    }

    public function filiere2()
    {
        return $this->belongsTo(Filiere::class, 'filiere2_id');
    }

    public function filiere3()
    {
        return $this->belongsTo(Filiere::class, 'filiere3_id');
    }

    public function inscription()
    {
        return $this->hasOne(Inscription::class, 'candidat_id');
    }

    public function nomComplet(): string
    {
        return trim($this->nom.' '.$this->prenoms);
    }

    /** Les cinq pièces exigées par la fiche de renseignement. */
    public function piecesFournies(): int
    {
        return collect(['piece_fiche', 'piece_acte', 'piece_diplome', 'piece_photos', 'piece_enveloppe'])
            ->filter(fn ($champ) => (bool) $this->{$champ})
            ->count();
    }

    public function dossierComplet(): bool
    {
        return $this->piecesFournies() === 5;
    }
}
