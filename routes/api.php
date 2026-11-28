<?php

use App\Http\Controllers\Api\Admin\AccountLinksController as AdminAccountLinksController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\HeroController as AdminHeroController;
use App\Http\Controllers\Api\Admin\HeroFeaturesController as AdminHeroFeaturesController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ProductImagesController as AdminProductImagesController;
use App\Http\Controllers\Api\Admin\ProductSizesController as AdminProductSizesController;
use App\Http\Controllers\Api\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\Admin\SocialLinksController as AdminSocialLinksController;
use App\Http\Controllers\Api\Admin\TestimonialsController as AdminTestimonialsController;
use App\Http\Controllers\Api\Public\HeroController as PublicHeroController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\Public\SettingsController as PublicSettingsController;
use App\Http\Controllers\Api\Public\TestimonialsController as PublicTestimonialsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Prefix: /api/v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── PUBLIC STOREFRONT ROUTES (No Auth) ───────────────────────────────────
    Route::get('/settings', [PublicSettingsController::class, 'index']);
    Route::get('/testimonials', [PublicTestimonialsController::class, 'index']);
    Route::get('/hero', [PublicHeroController::class, 'index']);
    Route::get('/product', [PublicProductController::class, 'index']);

    // ── AUTHENTICATION ROUTES ────────────────────────────────────────────────
    // Throttled to 5 login attempts per minute
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    // ── PROTECTED ADMIN ROUTES (auth:sanctum + EnsureUserIsAdmin) ────────────
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

        // Session & Identity
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        // Site Settings & Branding
        Route::get('/settings', [AdminSettingsController::class, 'index']);
        Route::put('/settings/whatsapp', [AdminSettingsController::class, 'updateWhatsApp']);
        Route::delete('/settings/whatsapp', [AdminSettingsController::class, 'deleteWhatsApp']);
        Route::post('/settings/logo', [AdminSettingsController::class, 'uploadLogo']);
        Route::delete('/settings/logo', [AdminSettingsController::class, 'removeLogo']);
        Route::post('/settings/reset', [AdminSettingsController::class, 'reset']);

        // Hero Section & Feature Badges
        Route::get('/hero', [AdminHeroController::class, 'show']);
        Route::put('/hero', [AdminHeroController::class, 'update']);
        Route::post('/hero/background', [AdminHeroController::class, 'uploadBackground']);
        Route::delete('/hero/background', [AdminHeroController::class, 'removeBackground']);
        Route::post('/hero/reset', [AdminHeroController::class, 'reset']);

        Route::get('/hero/features', [AdminHeroFeaturesController::class, 'index']);
        Route::post('/hero/features', [AdminHeroFeaturesController::class, 'store']);
        Route::post('/hero/features/reorder', [AdminHeroFeaturesController::class, 'reorder']);
        Route::put('/hero/features/{id}', [AdminHeroFeaturesController::class, 'update']);
        Route::delete('/hero/features/{id}', [AdminHeroFeaturesController::class, 'destroy']);

        // Featured Product Management
        Route::get('/product', [AdminProductController::class, 'show']);
        Route::put('/product', [AdminProductController::class, 'update']);
        Route::post('/product/reset', [AdminProductController::class, 'reset']);

        Route::get('/product/sizes', [AdminProductSizesController::class, 'index']);
        Route::post('/product/sizes', [AdminProductSizesController::class, 'store']);
        Route::post('/product/sizes/reorder', [AdminProductSizesController::class, 'reorder']);
        Route::post('/product/sizes/{id}/default', [AdminProductSizesController::class, 'setDefault']);
        Route::put('/product/sizes/{id}', [AdminProductSizesController::class, 'update']);
        Route::delete('/product/sizes/{id}', [AdminProductSizesController::class, 'destroy']);

        Route::get('/product/images', [AdminProductImagesController::class, 'index']);
        Route::post('/product/images', [AdminProductImagesController::class, 'store']);
        Route::post('/product/images/reorder', [AdminProductImagesController::class, 'reorder']);
        Route::put('/product/images/{id}', [AdminProductImagesController::class, 'update']);
        Route::delete('/product/images/{id}', [AdminProductImagesController::class, 'destroy']);

        // Social Media Links
        Route::get('/social-links', [AdminSocialLinksController::class, 'index']);
        Route::post('/social-links', [AdminSocialLinksController::class, 'store']);
        Route::post('/social-links/reorder', [AdminSocialLinksController::class, 'reorder']);
        Route::put('/social-links/{id}', [AdminSocialLinksController::class, 'update']);
        Route::delete('/social-links/{id}', [AdminSocialLinksController::class, 'destroy']);

        // Account & Contact Links
        Route::get('/account-links', [AdminAccountLinksController::class, 'index']);
        Route::post('/account-links', [AdminAccountLinksController::class, 'store']);
        Route::post('/account-links/reorder', [AdminAccountLinksController::class, 'reorder']);
        Route::put('/account-links/{id}', [AdminAccountLinksController::class, 'update']);
        Route::delete('/account-links/{id}', [AdminAccountLinksController::class, 'destroy']);

        // Testimonials Management
        Route::get('/testimonials', [AdminTestimonialsController::class, 'index']);
        Route::post('/testimonials', [AdminTestimonialsController::class, 'store']);
        Route::post('/testimonials/bulk-status', [AdminTestimonialsController::class, 'bulkStatus']);
        Route::post('/testimonials/bulk-delete', [AdminTestimonialsController::class, 'bulkDelete']);
        Route::get('/testimonials/{id}', [AdminTestimonialsController::class, 'show']);
        Route::put('/testimonials/{id}', [AdminTestimonialsController::class, 'update']);
        Route::delete('/testimonials/{id}', [AdminTestimonialsController::class, 'destroy']);
    });
});
