<?php

namespace App\Models;

use App\Enums\PhotoType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreePhoto extends Model
{
    use HasFactory;

    protected $fillable = ['tree_id', 'tree_monitoring_id', 'type', 'disk', 'path', 'original_filename', 'alt_text', 'caption', 'is_primary', 'sort_order', 'uploaded_by'];

    protected static function booted(): void
    {
        static::saved(function (TreePhoto $photo): void {
            if ($photo->is_primary) {
                static::query()->whereBelongsTo($photo->tree)->whereKeyNot($photo->getKey())->update(['is_primary' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return ['type' => PhotoType::class, 'is_primary' => 'boolean'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(TreeMonitoring::class, 'tree_monitoring_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
