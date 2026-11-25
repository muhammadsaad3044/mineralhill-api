<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AccountLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'label',
        'value',
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
