<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'label',
        'url',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('public_settings_cache');
        });

        static::deleted(function () {
            Cache::forget('public_settings_cache');
        });
    }
}
