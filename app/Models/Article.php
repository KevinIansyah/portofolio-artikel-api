<?php

namespace App\Models;

use App\Traits\HasUniqueSlugTitle;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasUniqueSlugTitle;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'content',
        'thumbnail_url',
        'status',
        'views',
        'reading_time',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }
}
