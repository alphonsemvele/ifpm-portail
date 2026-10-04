<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $fillable = ["menuDay_id","user_id","hours","status","code"];

    public function menuDay()
{
    return $this->belongsTo(menuDay::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}
