<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroFeature extends Model
{
    use HasFactory;

    public const ALLOWED_ICONS = [
        'leaf',
        'diamond',
        'flask',
        'shield',
        'star',
        'heart',
        'truck',
        'award',
        'check',
        'sparkles',
    ];

    public const MAX_ITEMS = 6;

    protected $fillable = [
        'icon',
        'title',
        'subtitle',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}
