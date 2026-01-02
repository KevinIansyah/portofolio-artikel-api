<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueSlugTitle
{
    /**
     * Boot the trait.
     */
    public static function bootHasUniqueSlugTitle()
    {
        static::creating(function ($model) {
            if (!empty($model->title_id)) {
                $model->slug_id = static::generateUniqueSlug(
                    'slug_id',
                    $model->title_id
                );
            }

            if (!empty($model->title_en)) {
                $model->slug_en = static::generateUniqueSlug(
                    'slug_en',
                    $model->title_en
                );
            }

            if (!empty($model->title)) {
                $model->slug = static::generateUniqueSlug(
                    'slug',
                    $model->title
                );
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title_id') && !empty($model->title_id)) {
                $model->slug_id = static::generateUniqueSlug(
                    'slug_id',
                    $model->title_id,
                    $model->id
                );
            }

            if ($model->isDirty('title_en') && !empty($model->title_en)) {
                $model->slug_en = static::generateUniqueSlug(
                    'slug_en',
                    $model->title_en,
                    $model->id
                );
            }

            if ($model->isDirty('title') && !empty($model->title)) {
                $model->slug = static::generateUniqueSlug(
                    'slug',
                    $model->title,
                    $model->id
                );
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    protected static function generateUniqueSlug(string $slugColumn, string $title, $id = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (static::slugExists($slugColumn, $slug, $id)) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists.
     */
    protected static function slugExists(string $slugColumn, string $slug, $id = null): bool
    {
        $query = static::where($slugColumn, $slug);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }
}
