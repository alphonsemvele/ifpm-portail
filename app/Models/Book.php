<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['name', 'auteur', 'code', 'status'];

    public function bookUsers()
    {
        return $this->hasMany(bookUser::class, 'book_id');
    }
}
