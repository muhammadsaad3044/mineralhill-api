<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_text',
        'title_line_1',
        'title_line_2',
        'description',
        'cta_text',
        'cta_url',
        'cta_enabled',
        'background_image_path',
        'background_image_mobile_path',
        'overlay_opacity',
        'text_align',
        'is_active',
    ];

    protected $casts = [
        'cta_enabled' => 'boolean',
        'overlay_opacity' => 'integer',
        'is_active' => 'boolean',
    ];
}
