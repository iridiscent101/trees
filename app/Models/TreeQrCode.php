<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeQrCode extends Model
{
    use HasFactory;

    protected $fillable = ['tree_id', 'public_url', 'storage_path', 'version', 'print_count', 'status', 'generated_by', 'generated_at', 'last_printed_at'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime', 'last_printed_at' => 'datetime'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
