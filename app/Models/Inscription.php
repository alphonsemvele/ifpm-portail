<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Inscription d'un apprenant admis : reprend la fiche d'inscription IFPM,
 * avec son échéancier de versements.
 */
class Inscription extends Model
{
    protected $fillable = [
        'candidat_id', 'user_id', 'matricule', 'annee', 'civilite', 'nom', 'prenoms',
        'date_naissance', 'lieu_naissance', 'nationalite', 'religion', 'email', 'telephone',
        'regime', 'filiere_id', 'specialite_id', 'conditions_acces', 'duree_formation',
        'diplome_vise', 'metiers', 'montant_scolarite', 'assurance_ifpm', 'compagnie_assurance',
        'date_visite_medicale', 'code_visite', 'statut', 'observations', 'saisi_par',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_visite_medicale' => 'date',
        'assurance_ifpm' => 'boolean',
        'montant_scolarite' => 'decimal:2',
    ];

    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class);
    }

    public function versements()
    {
        return $this->hasMany(Versement::class)->orderBy('rang');
    }

    public function nomComplet(): string
    {
        return trim($this->nom.' '.$this->prenoms);
    }

    /** Somme réellement encaissée : un versement compte une fois daté. */
    public function totalVerse(): float
    {
        return (float) $this->versements()->whereNotNull('date_versement')->sum('montant');
    }

    /** Total prévu par l'échéancier, frais fixes compris. */
    public function totalPrevu(): float
    {
        return (float) $this->versements()->sum('montant_prevu');
    }

    /** Ce qu'il reste à payer sur la scolarité annoncée. */
    public function resteAPayer(): float
    {
        return max(0, (float) $this->montant_scolarite - $this->totalVerse());
    }
}
