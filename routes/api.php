<?php

use App\Http\Controllers\Api\Admin\AccountLinksController as AdminAccountLinksController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\Admin\SocialLinksController as AdminSocialLinksController;
use App\Http\Controllers\Api\Admin\TestimonialsController as AdminTestimonialsController;
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
