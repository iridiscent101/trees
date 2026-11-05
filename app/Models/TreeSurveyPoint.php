<?php

namespace App\Models;

use App\Enums\SurveyReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TreeSurveyPoint extends Model
{
    use LogsActivity;

    protected $fillable = [
        'source_file',
        'source_row',
        'raw_species_name',
        'approved_species_name',
        'easting',
        'northing',
        'height_m',
        'latitude',
        'longitude',
        'coordinate_status',
        'campus_area_id',
        'review_status',
        'registered_tree_id',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('survey-records')
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function casts(): array
    {
        return [
            'easting' => 'decimal:3',
            'northing' => 'decimal:3',
            'height_m' => 'decimal:2',
            'latitude' => 'decimal:9',
            'longitude' => 'decimal:9',
            'review_status' => SurveyReviewStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function registeredTree(): BelongsTo
    {
        return $this->belongsTo(Tree::class, 'registered_tree_id');
    }

    public function campusArea(): BelongsTo
    {
        return $this->belongsTo(CampusArea::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePubliclyReviewable(Builder $query): Builder
    {
        return $query
            ->whereNull('registered_tree_id')
            ->whereIn('review_status', [
                SurveyReviewStatus::Pending->value,
                SurveyReviewStatus::NeedsCorrection->value,
            ]);
    }
}
