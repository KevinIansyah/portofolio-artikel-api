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
            $model->slug_id = static::generateUniqueSlugName(
                'slug_id',
                $model->name_id
            );

            $model->slug_en = static::generateUniqueSlugName(
                'slug_en',
                $model->name_en
            );
        });

        static::updating(function ($model) {
            if ($model->isDirty('name_id')) {
                $model->slug_id = static::generateUniqueSlugName(
                    'slug_id',
                    $model->name_id,
                    $model->id
                );
            }

            if ($model->isDirty('name_en')) {
                $model->slug_en = static::generateUniqueSlugName(
                    'slug_en',
                    $model->name_en,
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

        $query = static::where($slugColumn, $slug);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        while ($query->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;

            $query = static::where($slugColumn, $slug);

            if ($id) {
                $query->where('id', '!=', $id);
            }
        }

        return $slug;
    }
}
