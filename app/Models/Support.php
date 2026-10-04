<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    protected $fillable =["description","user_id","status","code"];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
