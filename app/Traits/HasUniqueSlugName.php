<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueSlugName
{
    /**
     * Get slug field mappings.
     * Override this method in model to customize.
     */
    protected function getSlugMappings(): array
    {
        return [
            'name' => 'slug',
            'name_id' => 'slug_id',
            'name_en' => 'slug_en',
        ];
    }

    /**
     * Boot the trait.
     */
    public static function bootHasUniqueSlugName()
    {
        static::creating(function ($model) {
            $slugMappings = $model->getSlugMappings();

            foreach ($slugMappings as $sourceField => $slugField) {
                if (!empty($model->$sourceField)) {
                    $model->$slugField = static::generateUniqueSlugName(
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
                    $model->$slugField = static::generateUniqueSlugName(
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
