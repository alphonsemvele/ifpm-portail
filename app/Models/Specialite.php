<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialite extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'filiere_id',
        'responsable_id',
        'price',
        'status',
        'cycle_id',           // ← on le garde
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relations
    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');   // ← utile pour l'affichage
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id')
            ->whereNotIn('role', ['student', 'etudiant', 'admin'])
            ->where('status', 'Success');
    }

    public function etudiants(): HasMany
    {
        return $this->hasMany(User::class, 'specialite_id')
            ->whereIn('role', ['student', 'etudiant'])
            ->where('status', 'Success');
    }

    public function ues(): HasMany
    {
        return $this->hasMany(Ue::class, 'specialite_id');
    }

    // Accessors
    public function getResponsableNameAttribute(): string
    {
        return $this->responsable?->name ?? 'Non assigné';
    }

    public function getCycleNameAttribute(): string
    {
        return $this->cycle?->name ?? '—';
    }

    public function getEtudiantsCountAttribute(): int
    {
        return $this->etudiants()->count();
    }

    public function getUeCountAttribute(): int
    {
        return $this->ues()->count();
    }
    public function cours() {
    return $this->hasMany(Cour::class);
}
}