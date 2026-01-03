<?php

namespace App\Models;

use App\Traits\HasUniqueSlugName;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasUniqueSlugName;

    protected $fillable = [
        'name_id',
        'slug_id',
        'name_en',
        'slug_en',
        'type',
    ];

    protected $appends = [
        'name',
        'slug',
    ];

    protected $hidden = [
        'name_id',
        'name_en',
        'slug_id',
        'slug_en',
        'type',
        'created_at',
        'updated_at',
        'pivot',
    ];

    /**
     * Get name based on current locale
     */
    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_id;
    }

    /**
     * Get slug based on current locale
     */
    public function getSlugAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"slug_{$locale}"} ?? $this->slug_id;
    }

    /**
     * Relationships
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'category_project');
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'category_article');
    }

    /**
     * Scopes
     */
    public function scopeForProjects($query)
    {
        return $query->where('type', 'project');
    }

    public function scopeForArticles($query)
    {
        return $query->where('type', 'article');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByLocale($query, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $query->whereNotNull("name_{$locale}");
    }

    /**
     * Override slug mappings
     */
    protected function getSlugMappings(): array
    {
        return [
            'name_id' => 'slug_id',
            'name_en' => 'slug_en',
        ];
    }
}
