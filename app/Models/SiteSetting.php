<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo_path',
        'whatsapp_number',
        'whatsapp_message',
        'whatsapp_enabled',
    ];

    protected $casts = [
        'whatsapp_enabled' => 'boolean',
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
