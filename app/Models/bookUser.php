<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bookUser extends Model
{
    protected $fillable = ['user_id', 'book_id', 'return_date', 'status'];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
