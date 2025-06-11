<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'content', 'author_id', 'author_type', 'image'
    ];

    public function author()
    {
        return $this->morphTo();
    }
}