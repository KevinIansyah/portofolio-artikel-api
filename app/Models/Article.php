<?php

namespace App\Models;

use App\Traits\HasUniqueSlugTitle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, HasUniqueSlugTitle;

    protected $fillable = [
        'user_id',
        'title_id',
        'description_id',
        'content_id',
        'slug_id',
        'title_en',
        'description_en',
        'content_en',
        'slug_en',
        'thumbnail_url',
        'status',
        'views',
        'reading_time',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'title',
        'description',
        'content',
        'slug',
    ];

    protected $hidden = [
        'title_id',
        'title_en',
        'description_id',
        'description_en',
        'content_id',
        'content_en',
        'slug_id',
        'slug_en',
    ];

    /**
     * Get title based on current locale
     */
    public function getTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"title_{$locale}"} ?? $this->title_id;
    }

    /**
     * Get description based on current locale
     */
    public function getDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"description_{$locale}"} ?? $this->description_id;
    }

    /**
     * Get content based on current locale
     */
    public function getContentAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"content_{$locale}"} ?? $this->content_id;
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_article');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_article');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByLocale($query, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $query->whereNotNull("title_{$locale}")
            ->whereNotNull("content_{$locale}");
    }

    /**
     * Override slug mappings
     */
    protected function getSlugMappings(): array
    {
        return [
            'title_id' => 'slug_id',
            'title_en' => 'slug_en',
        ];
    }
}
