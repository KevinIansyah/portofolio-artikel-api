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
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlugTitle($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title')) {
                $model->slug = static::generateUniqueSlugTitle($model->title, $model->id);
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public static function generateUniqueSlugTitle(string $title, $id = null): string
    {
        $slug = Str::slug($title, '-');
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;

            $query = static::where('slug', $slug);
            if ($id) {
                $query->where('id', '!=', $id);
            }
        }

        return $slug;
    }
}
