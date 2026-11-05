<?php

namespace App\Models;

use App\Enums\TreeCondition;
use App\Enums\TreeStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Tree extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = ['tree_code', 'species_id', 'campus_area_id', 'descriptive_location', 'nearby_landmark', 'latitude', 'longitude', 'trunk_diameter_cm', 'estimated_height_m', 'estimated_age_years', 'date_planted', 'current_condition', 'status', 'verification_status', 'public_notes', 'internal_remarks', 'is_featured', 'recorded_by', 'verified_by', 'verified_at', 'published_at'];

    protected static function booted(): void
    {
        static::created(function (Tree $tree): void {
            if (! $tree->tree_code) {
                $tree->updateQuietly(['tree_code' => 'T-'.str_pad((string) $tree->getKey(), 4, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('trees')
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'trunk_diameter_cm' => 'decimal:2',
            'estimated_height_m' => 'decimal:2', 'date_planted' => 'date', 'current_condition' => TreeCondition::class,
            'status' => TreeStatus::class, 'verification_status' => VerificationStatus::class, 'is_featured' => 'boolean',
            'verified_at' => 'datetime', 'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TreeStatus::Existing)->where('verification_status', VerificationStatus::Verified)->whereNotNull('published_at');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function campusArea(): BelongsTo
    {
        return $this->belongsTo(CampusArea::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(TreeMonitoring::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TreePhoto::class);
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(TreePhoto::class)->where('is_primary', true);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(TreeVerification::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(TreeQrCode::class);
    }
}
