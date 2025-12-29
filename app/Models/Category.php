<?php

namespace App\Models;

use App\Traits\HasUniqueSlugName;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasUniqueSlugName;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
    ];

    protected $hidden = ['pivot'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'category_project');
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }

    public function scopeForProjects($query)
    {
        return $query->where('type', 'project');
    }

    public function scopeForArticles($query)
    {
        return $query->where('type', 'article');
    }
}
