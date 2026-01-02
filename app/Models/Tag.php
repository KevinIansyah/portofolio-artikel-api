<?php

namespace App\Models;

use App\Traits\HasUniqueSlugName;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasUniqueSlugName;

    protected $fillable = [
        'name_id',
        'slug_id',
        'name_en',
        'slug_en',
    ];

    protected $appends = [
        'name',
        'slug'
    ];

    protected $hidden = [
        'name_id',
        'slug_id',
        'name_en',
        'slug_en',
        'created_at',
        'updated_at',
        'pivot'
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
     * Relationship
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'tag_article');
    }

    /**
     * Scopes
     */
    public function scopeByLocale($query, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $query->whereNotNull("name_{$locale}");
    }
}
