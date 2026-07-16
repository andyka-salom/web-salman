<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiCampaignController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\BroadcastController;
use App\Http\Controllers\Api\DailyCheckupController;
use App\Http\Controllers\Api\MyActionItemsController;
use App\Http\Controllers\Api\CermatReportController;
use App\Http\Controllers\Api\CampaignSalmanApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
        Route::get('dcu/export/pdf', [DailyCheckupController::class, 'exportPdf']);
        Route::get('dcu/export/excel', [DailyCheckupController::class, 'exportExcel']);
// Password Reset Flow
Route::post('/password/email', [AuthController::class, 'sendVerificationCode']);
Route::post('/password/verify', [AuthController::class, 'verifyCode']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Protected Routes (Requires Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']); // POST digunakan untuk upload file
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);

    Route::prefix('master')->group(function () {
        Route::get('/areas', [MasterDataController::class, 'getAreas']);
        Route::get('/pelanggaran', [MasterDataController::class, 'getPelanggaran']);
        Route::get('/security-event-categories', [MasterDataController::class, 'getSecurityEventCategories']);
        Route::get('/unsafe-acts', [MasterDataController::class, 'getUnsafeActs']);
        Route::get('/unsafe-conditions', [MasterDataController::class, 'getUnsafeConditions']);
        Route::get('/line-supervisors', [MasterDataController::class, 'getLineSupervisors']);
    });


    // --- Broadcast System (WhatsApp Blasting) ---
    Route::prefix('broadcast')->group(function () {
        Route::get('/dashboard', [BroadcastController::class, 'index']);
        Route::get('/form-data', [BroadcastController::class, 'formData']); // Dropdown helpers
        Route::get('/history', [BroadcastController::class, 'history']);
        Route::get('/{id}', [BroadcastController::class, 'show']);

        // Actions
        Route::post('/sync-wa-groups', [BroadcastController::class, 'syncWhatsAppGroups']);
        Route::post('/send/manual', [BroadcastController::class, 'sendManual']);
        Route::post('/send/group-contact', [BroadcastController::class, 'sendGroupContact']);
        Route::post('/send/group-wa', [BroadcastController::class, 'sendGroupWA']);
        Route::post('/{id}/retry', [BroadcastController::class, 'retry']);
        Route::delete('/{id}', [BroadcastController::class, 'destroy']);
    });

    Route::get('/campaigns', [ApiCampaignController::class, 'index']);
    Route::get('/campaigns/{id}', [ApiCampaignController::class, 'show']);

    Route::prefix('dcu')->group(function () {
        Route::get('/', [DailyCheckupController::class, 'index']);
        Route::get('/today-crew', [DailyCheckupController::class, 'getCrewWithTodayChecks']);
        Route::post('/bulk', [DailyCheckupController::class, 'storeBulk']);
        Route::get('/export/pdf', [DailyCheckupController::class, 'exportPdf']);
        Route::get('/export/excel', [DailyCheckupController::class, 'exportExcel']);
        Route::get('/{healthCheck}', [DailyCheckupController::class, 'show']);
    });


    // --- My Action Items (Kanban / To-Do List) ---
    Route::prefix('my-action-items')->group(function () {
        Route::get('/', [MyActionItemsController::class, 'index']); // Get grouped Kanban items
        Route::get('/{actionItem}', [MyActionItemsController::class, 'show']);
        // Note: Using POST for updates to handle file uploads properly
        Route::post('/{actionItem}/update', [MyActionItemsController::class, 'update']);
        Route::delete('/photos/{photo}', [MyActionItemsController::class, 'destroyPhoto']);
    });


     Route::prefix('cermat-reports')->name('api.cermat.')->group(function () {

        // CRUD Operations
        Route::get('/', [CermatReportApiController::class, 'index'])->name('index');
        Route::post('/', [CermatReportApiController::class, 'store'])->name('store');
        Route::get('/{report}', [CermatReportApiController::class, 'show'])->name('show');
        Route::put('/{report}', [CermatReportApiController::class, 'update'])->name('update');
        Route::patch('/{report}', [CermatReportApiController::class, 'update'])->name('patch');
        Route::delete('/{report}', [CermatReportApiController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/resubmit', [CermatReportApiController::class, 'resubmit'])->name('resubmit');
    });
});
