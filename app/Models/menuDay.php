<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class menuDay extends Model
{
    protected $fillable = ["menu_id","day","hours","specialday","status"];
    public function menu()
{
    return $this->belongsTo(Menu::class);
}

public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
