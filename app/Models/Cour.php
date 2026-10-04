<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cour extends Model
{
    protected $fillable = [
        'name',
        'code',
        'filiere_id',
        'cycle_id',
        'specialite_id',
        'ue_id',
        'responsable_id',
        'hour_number',
        'credit',
        'status',
        'examen_id',
    ];

    // ── Auto-remplissage de examen_id depuis la filière ────────────────────────
    protected static function boot()
    {
        parent::boot();

        $syncExamenId = function (self $cour) {
            if ($cour->filiere_id && empty($cour->examen_id)) {
                $filiere = Filiere::find($cour->filiere_id);
                if ($filiere && $filiere->examen_id) {
                    $cour->examen_id = $filiere->examen_id;
                }
            }
        };

        static::creating($syncExamenId);
        static::updating($syncExamenId);
    }

    // ── Relations ──────────────────────────────────────────────────────────────
    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function ue()
    {
        return $this->belongsTo(Ue::class, 'ue_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id')
            ->whereNotIn('role', ['student', 'etudiant', 'admin']);
    }

    public function examens()
    {
        return $this->hasMany(Examen::class);
    }

    public function notes()
    {
        return $this->hasManyThrough(Note::class, Examen::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }
}