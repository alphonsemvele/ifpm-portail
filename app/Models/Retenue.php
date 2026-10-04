<?php
// ─── App\Models\Retenue.php ───────────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Retenue extends Model
{
    protected $fillable = ['libelle', 'description', 'actif'];
    protected $casts    = ['actif' => 'boolean'];

    public function profilSalaires(): BelongsToMany
    {
        return $this->belongsToMany(ProfilSalaire::class, 'profil_salaire_retenue')
            ->withPivot('type_calcul', 'value')
            ->withTimestamps();
    }
}