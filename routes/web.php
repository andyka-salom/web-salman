<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TableController;
//master
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\SecurityEventCategoryController;
use App\Http\Controllers\Master\UnsafeActController;
use App\Http\Controllers\Master\UnsafeConditionController;
use App\Http\Controllers\Master\AreaController;
use App\Http\Controllers\Master\PelanggaranController;
use App\Http\Controllers\Master\ActionCategoryController;
use App\Http\Controllers\Master\GroupController;
use App\Http\Controllers\Master\VesselController;
use App\Http\Controllers\Master\CrewMemberController;
use App\Http\Controllers\Master\EntityFunctionController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\CoverTemplateController;
use App\Http\Controllers\Master\ProfileController;
use App\Http\Controllers\Master\RoleController;

use App\Http\Controllers\Features\CampaignController;
use App\Http\Controllers\Features\DailyCheckupController;
use App\Http\Controllers\Features\CampaignSalmanController;
use App\Http\Controllers\CermatReportController;
use App\Http\Controllers\BroadcastController;
//auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ActionItemMonitoringController;
use App\Http\Controllers\KpiHsseController;
//dashboard
use App\Http\Controllers\Dashboard\HealthCheckDashboardController;
use App\Http\Controllers\MyActionItemsController;
use App\Http\Controllers\Master\ContractController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\Features\HsseEvaluationController;
use App\Http\Controllers\Master\ContactController;
use App\Http\Controllers\Features\CrewAssessmentController;
use App\Http\Controllers\Public\CertificateVerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
use Illuminate\Support\Facades\Artisan;

// Route::get('/clear-cache', function () {
//     Artisan::call('optimize:clear');

//     return response()->json([
//         'status' => 'ok',
//         'message' => 'Cache berhasil dibersihkan (config, route, view, application).'
//     ]);
// });
// Route::get('/migrate', function () {
//     Artisan::call('migrate', [
//         '--force' => true // penting supaya bisa jalan di production
//     ]);

//     return response()->json([
//         'status' => 'ok',
//         'message' => 'Database berhasil dimigrate.',
//         'output' => Artisan::output()
//     ]);
// });



Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('password/reset', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('password/send-code', [PasswordResetController::class, 'sendVerificationCode'])->name('password.send-code');
    Route::get('password/verify', [PasswordResetController::class, 'showVerifyForm'])->name('password.verify');
    Route::post('password/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify-code');
    Route::post('password/resend-code', [PasswordResetController::class, 'resendCode'])->name('password.resend-code');
    Route::get('password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/update', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});


Route::middleware(['auth', 'active'])->group(function () {

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::post('/analytics-data', [DashboardController::class, 'getAnalyticsData'])->name('analytics.data');
        Route::get('/sales', [DashboardController::class, 'sales'])->name('sales');
        Route::get('/health-check', [HealthCheckDashboardController::class, 'index'])->name('health-check');
            Route::get('/campaign-salman', [DashboardController::class, 'campaignSalman'])->name('campaign-salman');
    Route::post('/campaign-salman-data', [DashboardController::class, 'getCampaignSalmanData'])->name('campaign-salman.data');
    });
    // AJAX endpoint untuk detail vessel per tanggal
    Route::get('/dashboard/health-check/vessel-detail', [HealthCheckDashboardController::class, 'getVesselDetail'])->name('dashboard.health-check.vessel-detail');
    Route::group(['prefix' => 'cermat/action-monitoring', 'as' => 'cermat.action-monitoring.', 'middleware' => ['auth']], function () {
    Route::get('/', [ActionItemMonitoringController::class, 'index'])->name('index');
    Route::get('/data', [ActionItemMonitoringController::class, 'data'])->name('data'); // Untuk DataTable Detail
    Route::get('/action-monitoring/export', [ActionItemMonitoringController::class, 'export'])->name('export');
});
    // Main dashboard redirect
    Route::get('/dashboard', function () {
        return redirect()->route('dashboard.analytics');
    })->name('dashboard');
Route::get('/home', [DashboardController::class, 'home'])->name('home');
    //profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });


Route::prefix('kpi-hsse')->name('kpi-hsse.')->middleware(['auth'])->group(function () {

    // ── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [KpiHsseController::class, 'dashboard'])->name('dashboard');

    // ── Resource utama ─────────────────────────────────────────
    Route::get('/',                    [KpiHsseController::class, 'index'])->name('index');
    Route::get('/create',              [KpiHsseController::class, 'create'])->name('create');
    Route::post('/',                   [KpiHsseController::class, 'store'])->name('store');
    Route::get('/{kpiReport}',         [KpiHsseController::class, 'show'])->name('show');
    Route::get('/{kpiReport}/edit',    [KpiHsseController::class, 'edit'])->name('edit');

    // update — AJAX auto-save per baris (PUT/POST dari JS)
    Route::match(['PUT','POST'], '/{kpiReport}', [KpiHsseController::class, 'update'])->name('update');

    // update metadata — AJAX
    Route::post('/{kpiReport}/period', [KpiHsseController::class, 'updatePeriod'])->name('period.update_report');

    Route::delete('/{kpiReport}',      [KpiHsseController::class, 'destroy'])->name('destroy');

    // ── Submit & Review ────────────────────────────────────────
    Route::post('/{kpiReport}/submit', [KpiHsseController::class, 'submit'])->name('submit');
    Route::post('/{kpiReport}/review', [KpiHsseController::class, 'review'])->name('review');

    // ── Draft (simpan draft — PUT dari form modal) ─────────────
    Route::put('/{kpiReport}/draft',   [KpiHsseController::class, 'saveDraft'])->name('draft');

    // ── HSSE AJAX ──────────────────────────────────────────────
    // Inline decision Accept/Reject per baris (POST JSON dari JS doInlineDecision)
    Route::post('/{kpiReport}/decision', [KpiHsseController::class, 'updateDecision'])->name('decision.update');

    // Update nilai/score
    Route::post('/{kpiReport}/score',    [KpiHsseController::class, 'updateScore'])->name('score.update');

    // Update catatan verifikasi (auto-save blur)
    Route::post('/{kpiReport}/catatan',  [KpiHsseController::class, 'updateCatatan'])->name('catatan.update');

    // Toggle mark item
    Route::post('/{kpiReport}/mark',     [KpiHsseController::class, 'toggleMark'])->name('mark.toggle');

    // ── Evidence ───────────────────────────────────────────────
    Route::post('/{kpiReport}/evidences/upload',       [KpiHsseController::class, 'uploadEvidence'])->name('evidences.upload');
    Route::delete('/{kpiReport}/evidences/{evidence}', [KpiHsseController::class, 'deleteEvidence'])->name('evidences.delete');

    // ── Vessels ────────────────────────────────────────────────
    Route::post('/{kpiReport}/vessels',            [KpiHsseController::class, 'storeVessel'])->name('vessels.store');
    Route::put('/{kpiReport}/vessels/{vessel}',    [KpiHsseController::class, 'updateVessel'])->name('vessels.update');
    Route::delete('/{kpiReport}/vessels/{vessel}', [KpiHsseController::class, 'destroyVessel'])->name('vessels.destroy');

    // ── Export ─────────────────────────────────────────────────
    Route::get('/{kpiReport}/export',  [KpiHsseController::class, 'export'])->name('export');
// ── Export PDF ─────────────────────────────────────────────
Route::get('/{kpiReport}/export-pdf', [KpiHsseController::class, 'exportPdf'])->name('export-pdf');
    // ── Period (utility, HSSE only) ────────────────────────────
    Route::post('/period',             [KpiHsseController::class, 'storePeriod'])->name('period.store');
});
    //master
    Route::prefix('roles')->name('roles.')->middleware(['auth'])->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('my-company')->name('companies.')->group(function() {
        Route::get('/profile', [CompanyController::class, 'editProfile'])->name('edit-profile');
        Route::post('/profile', [CompanyController::class, 'updateProfile'])->name('update-profile');
    });
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
        Route::post('/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/restore/{id}', [CompanyController::class, 'restore'])->name('restore');
    });

    // Security Event Category Routes
    Route::prefix('security-event-categories')->name('security-event-categories.')->group(function () {
        Route::get('/', [SecurityEventCategoryController::class, 'index'])->name('index');
        Route::get('/create', [SecurityEventCategoryController::class, 'create'])->name('create');
        Route::post('/', [SecurityEventCategoryController::class, 'store'])->name('store');
        Route::get('/{securityEventCategory}', [SecurityEventCategoryController::class, 'show'])->name('show');
        Route::get('/{securityEventCategory}/edit', [SecurityEventCategoryController::class, 'edit'])->name('edit');
        Route::put('/{securityEventCategory}', [SecurityEventCategoryController::class, 'update'])->name('update');
        Route::delete('/{securityEventCategory}', [SecurityEventCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{securityEventCategory}/toggle-status', [SecurityEventCategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/restore/{id}', [SecurityEventCategoryController::class, 'restore'])->name('restore');
    });

    // Unsafe Acts Routes
    Route::prefix('unsafe-acts')->name('unsafe-acts.')->group(function () {
        Route::get('/', [UnsafeActController::class, 'index'])->name('index');
        Route::get('/create', [UnsafeActController::class, 'create'])->name('create');
        Route::post('/', [UnsafeActController::class, 'store'])->name('store');
        Route::get('/{unsafeAct}', [UnsafeActController::class, 'show'])->name('show');
        Route::get('/{unsafeAct}/edit', [UnsafeActController::class, 'edit'])->name('edit');
        Route::put('/{unsafeAct}', [UnsafeActController::class, 'update'])->name('update');
        Route::delete('/{unsafeAct}', [UnsafeActController::class, 'destroy'])->name('destroy');
        Route::post('/{unsafeAct}/toggle-status', [UnsafeActController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{unsafeAct}/reset-usage', [UnsafeActController::class, 'resetUsageCount'])->name('reset-usage');
        Route::post('/restore/{id}', [UnsafeActController::class, 'restore'])->name('restore');
    });


    Route::prefix('unsafe-conditions')->name('unsafe-conditions.')->group(function () {
        Route::get('/', [UnsafeConditionController::class, 'index'])->name('index');
        Route::get('/create', [UnsafeConditionController::class, 'create'])->name('create');
        Route::post('/', [UnsafeConditionController::class, 'store'])->name('store');
        Route::get('/{unsafeCondition}', [UnsafeConditionController::class, 'show'])->name('show');
        Route::get('/{unsafeCondition}/edit', [UnsafeConditionController::class, 'edit'])->name('edit');
        Route::put('/{unsafeCondition}', [UnsafeConditionController::class, 'update'])->name('update');
        Route::delete('/{unsafeCondition}', [UnsafeConditionController::class, 'destroy'])->name('destroy');
        Route::post('/{unsafeCondition}/toggle-status', [UnsafeConditionController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('crew')->name('crew.')->group(function () {
        // CRUD Operations
        Route::get('/', [CrewController::class, 'index'])->name('index');
        Route::post('/', [CrewController::class, 'store'])->name('store');
        Route::put('/{crew}', [CrewController::class, 'update'])->name('update');
        Route::delete('/{crew}', [CrewController::class, 'destroy'])->name('destroy');

        // Assignment Operations
        Route::post('/{crew}/assign', [CrewController::class, 'assignToVessel'])->name('assign');
        Route::post('/{crew}/unassign', [CrewController::class, 'unassignFromVessel'])->name('unassign');
        Route::post('/{crew}/transfer', [CrewController::class, 'transferToVessel'])->name('transfer');

        // History
        Route::get('/{crew}/history', [CrewController::class, 'getAssignmentHistory'])->name('history');
    });

    Route::prefix('areas')->name('areas.')->group(function () {
        Route::get('/', [AreaController::class, 'index'])->name('index');
        Route::get('/create', [AreaController::class, 'create'])->name('create');
        Route::post('/', [AreaController::class, 'store'])->name('store');
        Route::get('/{area}', [AreaController::class, 'show'])->name('show');
        Route::get('/{area}/edit', [AreaController::class, 'edit'])->name('edit');
        Route::put('/{area}', [AreaController::class, 'update'])->name('update');
        Route::delete('/{area}', [AreaController::class, 'destroy'])->name('destroy');
        Route::post('/{area}/toggle-status', [AreaController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('pelanggaran')->name('pelanggaran.')->group(function () {
        Route::get('/', [PelanggaranController::class, 'index'])->name('index');
        Route::get('/create', [PelanggaranController::class, 'create'])->name('create');
        Route::post('/', [PelanggaranController::class, 'store'])->name('store');
        Route::get('/{pelanggaran}', [PelanggaranController::class, 'show'])->name('show');
        Route::get('/{pelanggaran}/edit', [PelanggaranController::class, 'edit'])->name('edit');
        Route::put('/{pelanggaran}', [PelanggaranController::class, 'update'])->name('update');
        Route::delete('/{pelanggaran}', [PelanggaranController::class, 'destroy'])->name('destroy');
        Route::post('/{pelanggaran}/toggle-status', [PelanggaranController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('action-categories')->name('action-categories.')->group(function () {
        Route::get('/', [ActionCategoryController::class, 'index'])->name('index');
        Route::get('/create', [ActionCategoryController::class, 'create'])->name('create');
        Route::post('/', [ActionCategoryController::class, 'store'])->name('store');
        Route::get('/{actionCategory}', [ActionCategoryController::class, 'show'])->name('show');
        Route::get('/{actionCategory}/edit', [ActionCategoryController::class, 'edit'])->name('edit');
        Route::put('/{actionCategory}', [ActionCategoryController::class, 'update'])->name('update');
        Route::delete('/{actionCategory}', [ActionCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{actionCategory}/toggle-status', [ActionCategoryController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Group Management Routes
    Route::prefix('groups')->name('groups.')->group(function () {
        // Basic CRUD
        Route::get('/', [GroupController::class, 'index'])->name('index');
        Route::get('/create', [GroupController::class, 'create'])->name('create');
        Route::post('/', [GroupController::class, 'store'])->name('store');
        Route::get('/{group}', [GroupController::class, 'show'])->name('show');
        Route::get('/{group}/edit', [GroupController::class, 'edit'])->name('edit');
        Route::put('/{group}', [GroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');
        Route::post('/{group}/toggle-status', [GroupController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{group}/members', [GroupController::class, 'members'])->name('members');
        Route::post('/{group}/members/add', [GroupController::class, 'addMember'])->name('members.add');
        Route::post('/{group}/members/remove', [GroupController::class, 'removeMember'])->name('members.remove');
        Route::get('/{group}/members', [GroupController::class, 'members'])->name('members');
Route::post('/{group}/members/batch', [GroupController::class, 'addMembersBatch'])->name('members.batch');
Route::post('/{group}/members/remove', [GroupController::class, 'removeMember'])->name('members.remove');
    });

    Route::prefix('vessels')->name('vessels.')->group(function () {
        // Vessel CRUD
        Route::get('/', [VesselController::class, 'index'])->name('index');
        Route::get('/create', [VesselController::class, 'create'])->name('create');
        Route::post('/', [VesselController::class, 'store'])->name('store');
        Route::get('/{vessel}', [VesselController::class, 'show'])->name('show');
        Route::get('/{vessel}/edit', [VesselController::class, 'edit'])->name('edit');
        Route::put('/{vessel}', [VesselController::class, 'update'])->name('update');
        Route::delete('/{vessel}', [VesselController::class, 'destroy'])->name('destroy');
        Route::post('/{vessel}/toggle-status', [VesselController::class, 'toggleStatus'])->name('toggle-status');

        // Crew Assignment Routes
        Route::prefix('{vessel}/crew')->name('crew.')->group(function () {
            Route::post('/assign', [VesselController::class, 'assignCrew'])->name('assign');
            Route::post('/{crew_member}/unassign', [VesselController::class, 'unassignCrew'])->name('unassign');
        });
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('entity-functions')->name('entity-functions.')->group(function () {
        Route::get('/', [EntityFunctionController::class, 'index'])->name('index');
        Route::get('/hierarchy', [EntityFunctionController::class, 'hierarchy'])->name('hierarchy');
        Route::get('/create', [EntityFunctionController::class, 'create'])->name('create');
        Route::get('/export', [EntityFunctionController::class, 'export'])->name('export');
        Route::post('/import', [EntityFunctionController::class, 'import'])->name('import');
        Route::post('/', [EntityFunctionController::class, 'store'])->name('store');
        Route::get('/{entityFunction}', [EntityFunctionController::class, 'show'])->name('show');
        Route::get('/{entityFunction}/edit', [EntityFunctionController::class, 'edit'])->name('edit');
        Route::put('/{entityFunction}', [EntityFunctionController::class, 'update'])->name('update');
        Route::delete('/{entityFunction}', [EntityFunctionController::class, 'destroy'])->name('destroy');
        Route::post('/{entityFunction}/toggle-status', [EntityFunctionController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->name('index');
        Route::get('/create', [CampaignController::class, 'create'])->name('create');
        Route::post('/', [CampaignController::class, 'store'])->name('store');
        Route::get('/{campaign}', [CampaignController::class, 'show'])->name('show');
        Route::get('/{campaign}/edit', [CampaignController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [CampaignController::class, 'update'])->name('update');
        Route::delete('/{campaign}', [CampaignController::class, 'destroy'])->name('destroy');
        Route::post('/{campaign}/toggle-publish', [CampaignController::class, 'togglePublish'])->name('toggle-publish');
        Route::post('/{campaign}/toggle-featured', [CampaignController::class, 'toggleFeatured'])->name('toggle-featured');
    });

    // (crew, admin, koordinator)
    Route::controller(DailyCheckupController::class)
        ->prefix('daily-checkup')
        ->name('daily-checkup.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/bulk', 'storeBulk')->name('store-bulk');
            Route::post('/verify', 'verify')->name('verify');
            Route::post('/validate', 'validate')->name('validate');
            Route::prefix('export')->name('export.')->group(function () {
                Route::post('/excel', 'exportExcel')->name('excel');
                Route::post('/pdf', 'exportPdf')->name('pdf');
            });
            Route::get('/vessel/{vessel}', 'showVessel')->name('vessel.show');
            Route::get('/{healthCheck}', 'showCheckup')->name('checkup.show');
            Route::delete('/{healthCheck}', 'destroy')->name('destroy');
            // Dalam group daily-checkup
        Route::get('/coordinator/vessel/{vessel}/input', [DailyCheckupController::class, 'coordinatorInput'])
            ->name('coordinator.input');
        Route::post('/coordinator/store', [DailyCheckupController::class, 'coordinatorStore'])
            ->name('coordinator.store');
        Route::post('/coordinator/store-bulk', [DailyCheckupController::class, 'coordinatorStoreBulk'])
            ->name('coordinator.store-bulk');
            });

    //admin
    Route::prefix('cover-templates')->name('cover-templates.')->group(function () {
        Route::get('/', [CoverTemplateController::class, 'index'])->name('index');
        Route::get('/create', [CoverTemplateController::class, 'create'])->name('create');
        Route::post('/', [CoverTemplateController::class, 'store'])->name('store');
        Route::get('/{coverTemplate}', [CoverTemplateController::class, 'show'])->name('show');
        Route::get('/{coverTemplate}/edit', [CoverTemplateController::class, 'edit'])->name('edit');
        Route::put('/{coverTemplate}', [CoverTemplateController::class, 'update'])->name('update');
        Route::delete('/{coverTemplate}', [CoverTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{coverTemplate}/toggle-status', [CoverTemplateController::class, 'toggleStatus'])->name('toggle-status');
    });


    Route::prefix('campaign-salman')->name('campaign-salman.')->group(function () {
        // ── STATIS (tanpa parameter) — WAJIB DI ATAS ──────────────
        Route::get('/',                       [CampaignSalmanController::class, 'index'])->name('index');
        Route::get('/create',                 [CampaignSalmanController::class, 'create'])->name('create');
        Route::post('/',                      [CampaignSalmanController::class, 'store'])->name('store');
        Route::post('/export-zip-check',      [CampaignSalmanController::class, 'exportZipCheck'])->name('export-zip-check');   // ← BARU
        Route::post('/export-zip',            [CampaignSalmanController::class, 'exportZip'])->name('export-zip');
        Route::post('/export-excel',          [CampaignSalmanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-zip-progress',    [CampaignSalmanController::class, 'exportZipProgress'])->name('export-zip-progress');

        // ── DENGAN PARAMETER — harus di bawah ─────────────────────
        Route::get('/{campaignSalman}',               [CampaignSalmanController::class, 'show'])->name('show');
        Route::get('/{campaignSalman}/edit',          [CampaignSalmanController::class, 'edit'])->name('edit');
        Route::put('/{campaignSalman}',               [CampaignSalmanController::class, 'update'])->name('update');
        Route::delete('/{campaignSalman}',            [CampaignSalmanController::class, 'destroy'])->name('destroy');
        Route::get('/{campaignSalman}/preview',       [CampaignSalmanController::class, 'previewPdf'])->name('preview');
        Route::get('/{campaignSalman}/download',      [CampaignSalmanController::class, 'downloadPdf'])->name('download');
        Route::post('/{campaignSalman}/delete-image', [CampaignSalmanController::class, 'deleteImage'])->name('delete-image');
    });
    Route::prefix('broadcast')->name('broadcast.')->group(function () {
        Route::get('/', [BroadcastController::class, 'history'])->name('index');
        Route::get('/history', [BroadcastController::class, 'history'])->name('history');
        Route::get('/{id}', [BroadcastController::class, 'show'])->name('show');
        Route::delete('/{id}', [BroadcastController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/retry', [BroadcastController::class, 'retry'])->name('retry');
        Route::get('/create/manual', [BroadcastController::class, 'createManual'])->name('create.manual');
        Route::get('/create/group-contact', [BroadcastController::class, 'createGroupContact'])->name('create.group-contact');
        Route::post('/send/manual', [BroadcastController::class, 'sendManual'])->name('send.manual');
        Route::post('/send/group-contact', [BroadcastController::class, 'sendGroupContact'])->name('send.group-contact');
    });

    Route::prefix('cermat/reports')->name('cermat.reports.')->group(function () {
        Route::get('/', [CermatReportController::class, 'index'])->name('index');
        Route::get('/data', [CermatReportController::class, 'data'])->name('data');
        Route::get('/create', [CermatReportController::class, 'create'])->name('create');
        Route::get('/{report}/edit', [CermatReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [CermatReportController::class, 'update'])->name('update');
        Route::post('/', [CermatReportController::class, 'store'])->name('store');
        Route::get('/{report}', [CermatReportController::class, 'show'])->name('show');
        Route::delete('/{report}', [CermatReportController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/submit-approval', [CermatReportController::class, 'submitApproval'])->name('submitApproval');
        Route::get('/{report}/review', [CermatReportController::class, 'showReviewForm'])->name('review');
        Route::post('/{report}/review', [CermatReportController::class, 'submitReview'])->name('submitReview');
        Route::get('/{report}/action', [CermatReportController::class, 'showActionForm'])->name('action');
        Route::post('/{report}/action-items', [CermatReportController::class, 'submitActionItem'])->name('submitActionItem');
        Route::patch('/{actionItem}/update-status', [CermatReportController::class, 'updateActionStatus'])->name('updateStatus');
        Route::get('/{report}/close', [CermatReportController::class, 'showCloseForm'])->name('close');
        Route::post('/{report}/close', [CermatReportController::class, 'submitCloseReport'])->name('submitClose');
        Route::post('/reports/{report}/resubmit', [CermatReportController::class, 'resubmit'])->name('resubmit');
        // Di dalam group cermat routes
        Route::get('cermat-reports/{report}/pdf', [CermatReportController::class, 'downloadPdf'])->name('downloadPdf');
        Route::post('reports/{report}/action-items/bulk', [CermatReportController::class, 'submitBulkActionItems'])->name('bulkAddActionItems');
            Route::get('/reports/export', [CermatReportController::class, 'export'])->name('export');

    });
    Route::prefix('cermat/action-items')->name('cermat.action-items.')->group(function () {
        Route::patch('/{actionItem}/update-status', [CermatReportController::class, 'updateActionStatus'])->name('updateStatus');
        Route::patch('/{actionItem}/extend', [CermatReportController::class, 'extendActionItem'])->name('extend');
                Route::delete('/cermat/action-items/{actionItem}', [CermatReportController::class, 'deleteActionItem'])
    ->name('delete');
    });

    //akses my action user
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/my-actions', [MyActionItemsController::class, 'index'])->name('my-actions.index');
        Route::get('/my-actions/{actionItem}/edit', [MyActionItemsController::class, 'edit'])->name('my-actions.edit');
        Route::put('/my-actions/{actionItem}', [MyActionItemsController::class, 'update'])->name('my-actions.update');
        Route::delete('/my-actions/photos/{photo}', [MyActionItemsController::class, 'destroyPhoto'])->name('my-actions.photos.destroy');
    });
Route::prefix('contracts')->name('contracts.')->group(function () {
    Route::get('export', [ContractController::class, 'export'])->name('export');
    Route::get('import', [ContractController::class, 'importForm'])->name('import.form');
    Route::post('import', [ContractController::class, 'import'])->name('import');
});
// ================================================================
// HSSE ON BOARD EVALUATION
// PENTING: Route statis (tanpa parameter) WAJIB di atas route
//          dinamis /{hsseEvaluation} agar tidak konflik.
// ================================================================
Route::prefix('hsse-evaluation')
    ->name('hsse-evaluation.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        // ── Statis ────────────────────────────────────────────────
        Route::get('/dashboard',
            [HsseEvaluationController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/dashboard-data',
            [HsseEvaluationController::class, 'dashboardData'])
            ->name('dashboard-data');
        Route::get('/top10-assessor',                              // ← pindah ke sini
            [HsseEvaluationController::class, 'top10AssessorData'])
            ->name('top10-assessor');
        Route::get('/vessels-by-company',
            [HsseEvaluationController::class, 'getVesselsByCompany'])
            ->name('vessels-by-company');
        Route::get('/crew-by-vessel',
            [HsseEvaluationController::class, 'getCrewByVessel'])
            ->name('crew-by-vessel');
        Route::get('/export-excel',
            [HsseEvaluationController::class, 'exportExcel'])
            ->name('export-excel');
        Route::get('/',
            [HsseEvaluationController::class, 'index'])
            ->name('index');
        Route::get('/create',
            [HsseEvaluationController::class, 'create'])
            ->name('create');
        Route::post('/',
            [HsseEvaluationController::class, 'store'])
            ->name('store');

        // ── Dinamis (parameter) — HARUS di bawah semua statis ─────
        Route::get('/{hsseEvaluation}',
            [HsseEvaluationController::class, 'show'])
            ->name('show');
        Route::get('/{hsseEvaluation}/edit',
            [HsseEvaluationController::class, 'edit'])
            ->name('edit');
        Route::put('/{hsseEvaluation}',
            [HsseEvaluationController::class, 'update'])
            ->name('update');
        Route::delete('/{hsseEvaluation}',
            [HsseEvaluationController::class, 'destroy'])
            ->name('destroy');
    });
// resource route DI LUAR prefix "contracts"
Route::resource('contracts', ContractController::class);

});
Route::prefix('contacts')->name('contacts.')->group(function () {

    // ── Static routes (harus sebelum {contact}) ─────────────
    Route::get('import',            [ContactController::class, 'importForm'])->name('import.form');
    Route::post('import',           [ContactController::class, 'import'])->name('import');
    Route::get('template/download', [ContactController::class, 'downloadTemplate'])->name('template.download');

    // ── Toggle status ────────────────────────────────────────
    Route::post('{contact}/toggle-status', [ContactController::class, 'toggleStatus'])->name('toggle-status');

    // ── Standard CRUD resource ───────────────────────────────
    Route::get('/',               [ContactController::class, 'index'])->name('index');
    Route::get('/create',         [ContactController::class, 'create'])->name('create');
    Route::post('/',              [ContactController::class, 'store'])->name('store');
    Route::get('/{contact}',      [ContactController::class, 'show'])->name('show');
    Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
    Route::put('/{contact}',      [ContactController::class, 'update'])->name('update');
    Route::delete('/{contact}',   [ContactController::class, 'destroy'])->name('destroy');
});
// ── AUTH: Crew Assessment (butuh login) ───────────────────────────────────────
Route::prefix('crew-assessment')
    ->name('crew-assessment.')
    ->middleware('auth')
    ->group(function () {

    // Static routes PERTAMA
    Route::get('/',             [CrewAssessmentController::class, 'index'])->name('index');
    Route::get('/data',         [CrewAssessmentController::class, 'indexData'])->name('index.data');
    Route::get('/dashboard',    [CrewAssessmentController::class, 'dashboard'])->name('dashboard');
    Route::get('/export/excel', [CrewAssessmentController::class, 'exportExcel'])->name('export-excel');
    Route::get('/create',       [CrewAssessmentController::class, 'create'])->name('create');
    Route::post('/',            [CrewAssessmentController::class, 'store'])->name('store');
        Route::post('/import',         [CrewAssessmentController::class, 'import'])->name('import');


    // Attachments (static — sebelum wildcard)
    Route::get('/attachment/{attachment}/download', [CrewAssessmentController::class, 'downloadAttachment'])->name('attachment.download');
    Route::delete('/attachment/{attachment}',        [CrewAssessmentController::class, 'destroyAttachment'])->name('attachment.destroy');

    // AJAX
    Route::get('/ajax/vessels-by-company', [CrewAssessmentController::class, 'getVesselsByCompany'])->name('ajax.vessels');
    Route::get('/ajax/crew-by-vessel',     [CrewAssessmentController::class, 'getCrewByVessel'])->name('ajax.crew');
    Route::get('/ajax/crew-by-company',    [CrewAssessmentController::class, 'getCrewByCompany'])->name('ajax.crew-by-company');
    Route::get('/ajax/search-crew',        [CrewAssessmentController::class, 'searchCrew'])->name('ajax.search-crew'); // ← TAMBAHAN: search crew lintas perusahaan & vessel

    // Wildcard TERAKHIR
    Route::get('/{crewAssessment}',       [CrewAssessmentController::class, 'show'])->name('show');
    Route::get('/{crewAssessment}/edit',  [CrewAssessmentController::class, 'edit'])->name('edit');
    Route::put('/{crewAssessment}',       [CrewAssessmentController::class, 'update'])->name('update');
    Route::delete('/{crewAssessment}',    [CrewAssessmentController::class, 'destroy'])->name('destroy');

});
Route::prefix('verifikasi-sertifikat')
    ->name('certificate-verification.')
    ->group(function () {
        Route::get('/',        [CertificateVerificationController::class, 'index'])->name('index');
        Route::post('/cek',    [CertificateVerificationController::class, 'verify'])->name('verify');
    });
