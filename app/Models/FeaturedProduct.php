<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class FeaturedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'eyebrow_text',
        'title_line_1',
        'title_line_2',
        'description',
        'currency_symbol',
        'rating_mode',
        'rating_value',
        'rating_count',
        'cta_text',
        'whatsapp_message_template',
        'show_quantity_selector',
        'is_active',
    ];

    protected $casts = [
        'rating_value' => 'float',
        'rating_count' => 'integer',
        'show_quantity_selector' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
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
