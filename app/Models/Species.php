<?php

namespace App\Models;

use App\Enums\ConservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Species extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'species';

    protected $fillable = ['common_name', 'scientific_name', 'family_name', 'local_name', 'classification', 'conservation_status', 'origin', 'typical_lifespan', 'description', 'educational_value', 'is_active'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('species')
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function casts(): array
    {
        return ['conservation_status' => ConservationStatus::class, 'is_active' => 'boolean'];
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }
}
