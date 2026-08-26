<?php

// routes/api.php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\HomeSummaryController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\InvitationSectionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Invitation Editor
|--------------------------------------------------------------------------
| All routes use auth:sanctum which supports both session-cookie auth
| (Inertia SPA on same domain) and Bearer token auth.
|--------------------------------------------------------------------------
*/

// ── Public utility endpoints ──────────────────────────────────────────────
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/check-email', function (Request $request) {
    $request->validate(['email' => ['required', 'email']]);
    return response()->json([
        'available' => ! User::where('email', $request->email)->exists(),
    ]);
});

// ── Authenticated Routes ───────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── Token auth ────────────────────────────────────────────────
    Route::get('/me',             [AuthController::class, 'me']);
    Route::post('/auth/logout',   [AuthController::class, 'logout']);

    // ── Device tokens (FCM push) ──────────────────────────────────
    Route::post('/devices',   [DeviceController::class, 'store']);
    Route::delete('/devices', [DeviceController::class, 'destroy']);

    // ── Home summary (shared module) ──────────────────────────────
    Route::get('/home/summary', [HomeSummaryController::class, 'show']);

    // ── Slug availability check ───────────────────────────────────
    Route::get('/invitations/check-slug', [InvitationController::class, 'checkSlug']);
    Route::put('/invitations/{invitation}/slug', [InvitationController::class, 'updateSlug']);

    // ── Invitation ────────────────────────────────────────────────
    Route::post(   '/invitations',               [InvitationController::class, 'store']);
    Route::put(    '/invitations/{invitation}',   [InvitationController::class, 'update']);

    // ── Details ───────────────────────────────────────────────────
    Route::post(  '/invitations/{invitation}/details',                      [InvitationController::class, 'storeDetails']);
    Route::delete('/invitations/{invitation}/details/photos/{photoField}',  [InvitationController::class, 'deleteDetailPhoto']);

    // ── Events ────────────────────────────────────────────────────
    Route::post(  '/invitations/{invitation}/events',          [InvitationController::class, 'storeEvent']);
    Route::put(   '/invitations/{invitation}/events/{event}',  [InvitationController::class, 'updateEvent']);
    Route::delete('/invitations/{invitation}/events/{event}',  [InvitationController::class, 'deleteEvent']);

    // ── Gallery ───────────────────────────────────────────────────
    // NOTE: reorder must be declared before /{gallery} to avoid "reorder" being
    // captured as a gallery UUID.
    Route::put(   '/invitations/{invitation}/galleries/reorder',       [InvitationController::class, 'reorderGallery']);
    Route::post(  '/invitations/{invitation}/galleries',               [InvitationController::class, 'storeGallery']);
    Route::delete('/invitations/{invitation}/galleries/{gallery}',     [InvitationController::class, 'deleteGallery']);

    // ── Music ─────────────────────────────────────────────────────
    Route::post('/invitations/{invitation}/music', [InvitationController::class, 'storeMusic']);

    // ── Sections ──────────────────────────────────────────────────
    Route::get(  '/invitations/{invitation}/sections',                     [InvitationSectionController::class, 'index']);
    Route::post( '/invitations/{invitation}/sections/media',               [InvitationSectionController::class, 'storeMedia']);
    Route::patch('/invitations/{invitation}/sections/{sectionKey}/toggle', [InvitationSectionController::class, 'toggle']);
    Route::patch('/invitations/{invitation}/sections/{sectionKey}',        [InvitationSectionController::class, 'updateData']);

    // ── Publish / Unpublish ───────────────────────────────────────
    Route::put('/invitations/{invitation}/publish',   [InvitationController::class, 'publish']);
    Route::put('/invitations/{invitation}/unpublish', [InvitationController::class, 'unpublish']);
});
