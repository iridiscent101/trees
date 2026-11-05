<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusArea extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'slug', 'description', 'center_latitude', 'center_longitude', 'is_active'];

    protected function casts(): array
    {
        return ['center_latitude' => 'decimal:7', 'center_longitude' => 'decimal:7', 'is_active' => 'boolean'];
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }

    public function surveyPoints(): HasMany
    {
        return $this->hasMany(TreeSurveyPoint::class);
    }
}
