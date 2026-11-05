<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeVerification extends Model
{
    use HasFactory;

    protected $fillable = ['tree_id', 'tree_monitoring_id', 'verifier_id', 'status', 'remarks', 'reviewed_fields', 'decided_at'];

    protected function casts(): array
    {
        return ['status' => VerificationStatus::class, 'reviewed_fields' => 'array', 'decided_at' => 'datetime'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(TreeMonitoring::class, 'tree_monitoring_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
