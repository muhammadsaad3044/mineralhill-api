<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location_or_role',
        'avatar_path',
        'rating',
        'product',
        'review',
        'reviewed_at',
        'status',
        'featured',
    ];

    protected $casts = [
        'rating' => 'integer',
        'featured' => 'boolean',
        'reviewed_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('public_testimonials_all');
            Cache::forget('public_testimonials_featured');
        });

        static::deleted(function () {
            Cache::forget('public_testimonials_all');
            Cache::forget('public_testimonials_featured');
        });
    }
}
