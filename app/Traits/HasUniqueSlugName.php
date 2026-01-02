<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueSlugName
{
    /**
     * Boot the trait.
     */
    public static function bootHasUniqueSlugName()
    {
        static::creating(function ($model) {
            if (!empty($model->name_id)) {
                $model->slug_id = static::generateUniqueSlugName(
                    'slug_id',
                    $model->name_id
                );
            }

            if (!empty($model->name_en)) {
                $model->slug_en = static::generateUniqueSlugName(
                    'slug_en',
                    $model->name_en
                );
            }

            if (!empty($model->name)) {
                $model->slug = static::generateUniqueSlugName(
                    'slug',
                    $model->name
                );
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name_id') && !empty($model->name_id)) {
                $model->slug_id = static::generateUniqueSlugName(
                    'slug_id',
                    $model->name_id,
                    $model->id
                );
            }

            if ($model->isDirty('name_en') && !empty($model->name_en)) {
                $model->slug_en = static::generateUniqueSlugName(
                    'slug_en',
                    $model->name_en,
                    $model->id
                );
            }

            if ($model->isDirty('name') && !empty($model->name)) {
                $model->slug = static::generateUniqueSlugName(
                    'slug',
                    $model->name,
                    $model->id
                );
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public static function generateUniqueSlugName(string $slugColumn, string $name, $id = null): string
    {
        $slug = Str::slug($name);
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
