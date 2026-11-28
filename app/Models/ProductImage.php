<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }

        return url(Storage::url($this->path));
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('public_featured_product');
        });

        static::deleted(function () {
            Cache::forget('public_featured_product');
        });
    }
}
