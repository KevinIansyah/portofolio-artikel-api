<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueSlugTitle
{
    /**
     * Get slug field mappings.
     * Override this method in model to customize.
     */
    protected function getSlugMappings(): array
    {
        return [
            'title' => 'slug',
            'title_id' => 'slug_id',
            'title_en' => 'slug_en',
        ];
    }

    /**
     * Boot the trait.
     */
    public static function bootHasUniqueSlugTitle()
    {
        static::creating(function ($model) {
            $slugMappings = $model->getSlugMappings();

            foreach ($slugMappings as $sourceField => $slugField) {
                if (!empty($model->$sourceField)) {
                    $model->$slugField = static::generateUniqueSlugTitle(
                        $slugField,
                        $model->$sourceField
                    );
                }
            }
        });

        static::updating(function ($model) {
            $slugMappings = $model->getSlugMappings();

            foreach ($slugMappings as $sourceField => $slugField) {
                if ($model->isDirty($sourceField) && !empty($model->$sourceField)) {
                    $model->$slugField = static::generateUniqueSlugTitle(
                        $slugField,
                        $model->$sourceField,
                        $model->id
                    );
                }
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public static function generateUniqueSlugTitle(string $slugColumn, string $title, $id = null): string
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
