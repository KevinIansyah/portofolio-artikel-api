<?php

namespace App\Models;

use App\Traits\HasUniqueSlugName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory, HasUniqueSlugName;

    protected $fillable = [
        'name',
        'slug',
        'dark_icon_ url',
        'light_icon_url',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    /**
     * Relationship
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'skill_project');
    }
}
