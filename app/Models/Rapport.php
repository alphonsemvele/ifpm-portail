<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
      protected $fillable = [
        'title','user_id','specialite_id','filiere_id','status','content','title'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * La spécialité concernée par le rapport (si spécifique)
     */
    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    /**
     * La filière du rapport
     */
    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }
}
