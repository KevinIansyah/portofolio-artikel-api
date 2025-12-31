<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueSlugTitle
{
    /**
     * Boot the trait.
     */
    protected static function bootHasUniqueSlugTitle()
    {
        static::creating(function ($model) {
            $model->slug_id = static::generateUniqueSlug(
                'slug_id',
                $model->title_id
            );

            $model->slug_en = static::generateUniqueSlug(
                'slug_en',
                $model->title_en
            );
        });

        static::updating(function ($model) {
            if ($model->isDirty('title_id')) {
                $model->slug_id = static::generateUniqueSlug(
                    'slug_id',
                    $model->title_id,
                    $model->id
                );
            }

            if ($model->isDirty('title_en')) {
                $model->slug_en = static::generateUniqueSlug(
                    'slug_en',
                    $model->title_en,
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

        $query = static::where($slugColumn, $slug);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        while ($query->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;

            $query = static::where($slugColumn, $slug);

            if ($id) {
                $query->where('id', '!=', $id);
            }
        }

        return $slug;
    }
}
