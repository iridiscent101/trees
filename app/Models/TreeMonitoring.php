<?php

namespace App\Models;

use App\Enums\TreeCondition;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreeMonitoring extends Model
{
    use HasFactory;

    protected $fillable = ['tree_id', 'observed_at', 'condition', 'trunk_diameter_cm', 'estimated_height_m', 'remarks', 'public_notes', 'is_public', 'verification_status', 'recorded_by', 'verified_by', 'verified_at'];

    protected function casts(): array
    {
        return ['observed_at' => 'datetime', 'condition' => TreeCondition::class, 'trunk_diameter_cm' => 'decimal:2', 'estimated_height_m' => 'decimal:2', 'is_public' => 'boolean', 'verification_status' => VerificationStatus::class, 'verified_at' => 'datetime'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TreePhoto::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(TreeVerification::class);
    }
}
