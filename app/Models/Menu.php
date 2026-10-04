<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ["name","status","type","categorie","price"];
    public function menuDays()
{
    return $this->hasMany(menuDay::class);
}
}
