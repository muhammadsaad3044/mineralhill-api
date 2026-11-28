<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'price',
        'compare_at_price',
        'enabled',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'compare_at_price' => 'float',
        'enabled' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

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
