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
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlugName($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = static::generateUniqueSlugName($model->name, $model->id);
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public static function generateUniqueSlugName(string $name, $id = null): string
    {
        $slug = Str::slug($name, '-');
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
