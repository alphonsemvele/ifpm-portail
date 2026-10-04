<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $fillable =["name","capacite","filiere_id","specialite_id","cycle_id","status"];

    public function filiere()
        {
            return $this->belongsTo(Filiere::class);
        }

        public function specialite()
        {
            return $this->belongsTo(Specialite::class, 'specialite_id', 'id');
        }

        public function cycle()
        {
            return $this->belongsTo(Cycle::class);
        }
}
