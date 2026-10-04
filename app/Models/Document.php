<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['path', 'article_id'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}