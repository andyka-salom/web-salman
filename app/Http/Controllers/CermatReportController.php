<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppNotification;
// Models
use App\Models\ActionItem;
use App\Models\Area;
use App\Models\EntityFunction;
use App\Models\CermatReport;
use App\Models\Pelanggaran;
use App\Models\ReportAttachment;
use App\Models\SecurityEventCategory;
use App\Models\UnsafeAct;
use App\Models\ActionCategory;
use App\Models\UnsafeCondition;
use App\Models\User;
// Support
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CermatReportsExport;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
// Form Requests
use App\Http\Requests\Cermat\StoreCermatReportRequest;
use App\Http\Requests\Cermat\SubmitApprovalRequest;
use App\Http\Requests\Cermat\SubmitReviewRequest;
use App\Http\Requests\Cermat\StoreActionItemRequest;
use App\Http\Requests\Cermat\UpdateActionStatusRequest;
use App\Http\Requests\Cermat\CloseReportRequest;
use App\Http\Requests\Cermat\ExtendActionItemRequest;
use App\Http\Requests\Cermat\UpdateCermatReportRequest;

class CermatReportController extends Controller
{
    /**
     * Cache duration in seconds (1 hour)
     */
    private const CACHE_DURATION = 3600;

    /**
     * Maximum number of action item extensions
     */
    private const MAX_EXTENSIONS = 3;

    /**
     * Maximum bulk action items
     */
    private const MAX_BULK_ACTIONS = 10;

    /**
     * Image optimization settings
     */
    private const MAX_IMAGE_WIDTH = 1200;
    private const IMAGE_QUALITY   = 80;

    /**
     * Maximum attempts for report number generation
     */
    private const MAX_REPORT_NUMBER_ATTEMPTS = 5;

    /**
     * Risk assessment consequence categories
     */
    private const CONSEQUENCE_CATEGORIES = [
        'people',
        'environment',
        'reputation',
        'asset',
        'public'
    ];

    /**
     * Valid report scopes
     */
    private const VALID_SCOPES = [
        'my_reports',
        'verification',
        'review',
        'action_items',
        'all'
    ];

    /**
     * Delay sebelum job WA dieksekusi (detik)
     * Memberi waktu DB commit selesai sebelum job berjalan
     */
    private const NOTIFICATION_DISPATCH_DELAY = 5;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display CeRMAT reports listing
     */
    public function index()
    {
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'CeRMAT Reports', 'url' => route('cermat.reports.index')],
        ];

        $user = Auth::user();

        $supervisorPendingCount = CermatReport::where('line_supervisor_id', $user->id)
            ->where('supervisor_status', CermatReport::SUPERVISOR_STATUS_AWAITING)
            ->count();

        $hssePendingCount = 0;
        if ($user->hasRole('hsse')) {
            $hssePendingCount = CermatReport::where('status', CermatReport::STATUS_IN_REVIEW)
                ->whereIn('hsse_status', [
                    CermatReport::HSSE_STATUS_PENDING_REVIEW,
                    CermatReport::HSSE_STATUS_REVIEWED
                ])
                ->count();
        }

        $myActionCount = ActionItem::where('responsible_id', $user->id)
            ->whereNotIn('status', [
                ActionItem::STATUS_COMPLETED,
                ActionItem::STATUS_CANT_DO
            ])
            ->count();

        return view('cermat.index', compact(
            'breadcrumbs',
            'supervisorPendingCount',
            'hssePendingCount',
            'myActionCount'
        ));
    }

    /**
     * DataTables data source with role-based filtering
     */
    public function data(Request $request)
    {
        $user  = Auth::user();
        $scope = $request->query('scope', 'my_reports');

        if (!in_array($scope, self::VALID_SCOPES, true)) {
            $scope = 'my_reports';
        }

        $query = CermatReport::with([
            'reporter:id,name,company_id',
            'reporter.company:id,name',
            'area:id,name'
        ])->select('cermat_reports.*');

        $this->applyScopeFilter($query, $scope, $user);

        if ($scope === 'all') {
            $this->applyAdvancedFilters($query, $request, $user);
        }

        return DataTables::of($query)
            ->editColumn('report_datetime', fn($report) =>
                Carbon::parse($report->report_datetime)->format('d M Y, H:i')
            )
            ->addColumn('status_html', fn($report) =>
                $this->renderStatusBadge($report->status)
            )
            ->addColumn('action', fn($report) =>
                '<a href="' . route('cermat.reports.show', $report->id) . '"
                    class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle text-decoration-none">
                    Lihat Detail
                </a>'
            )
            ->rawColumns(['status_html', 'action'])
            ->make(true);
    }

    /**
     * Show create report form
     */
    public function create()
    {
        $breadcrumbs = [
            ['label' => 'CeRMAT Reports', 'url' => route('cermat.reports.index')],
            ['label' => 'Create', 'url' => '#'],
        ];

        $data         = $this->getFormDropdownData();
        $report       = new CermatReport();
        $report->report_datetime = now();
        $currentUser  = Auth::user();
        $supervisor   = $this->getDirectSupervisor($currentUser);

        return view('cermat.form', array_merge($data, compact('breadcrumbs', 'report', 'supervisor')));
    }

    /**
     * Store new report
     */
    public function store(StoreCermatReportRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $reportNumber = $this->generateReportNumber();
            $currentUser  = Auth::user();
            $supervisor   = $this->getDirectSupervisor($currentUser);

            if ($supervisor) {
                $validatedData['line_supervisor_id'] = $supervisor->id;
            } else {
                $validatedData['line_supervisor_id'] = null;
                $validatedData['supervisor_status']  = CermatReport::SUPERVISOR_STATUS_NOT_REQUIRED;
            }

            $report = $this->createReport($validatedData, $request, $reportNumber);

            if ($request->hasFile('attachments')) {
                $this->storeAttachments($report, $request->file('attachments'), 'initial_report');
            }

            $report->syncUnsafeActsWithTracking($validatedData['selectedUnsafeActs'] ?? []);
            $report->syncUnsafeConditionsWithTracking($validatedData['selectedUnsafeConditions'] ?? []);

            DB::commit();

            // ✅ Dispatch job ke queue — controller tidak menunggu WA selesai dikirim
            SendWhatsAppNotification::dispatch(
                type: $supervisor ? 'supervisor_decision' : 'status_change',
                report: $report,
                decisionType: $supervisor ? 'submit' : null,
            )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));

            Log::info('CeRMAT report created successfully', [
                'report_id'     => $report->id,
                'report_number' => $report->report_number,
                'reporter_id'   => Auth::id(),
                'supervisor_id' => $supervisor?->id
            ]);

            return redirect()
                ->route('cermat.reports.show', $report)
                ->with('success', "Laporan #{$report->report_number} berhasil dikirim.");

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate entry')) {
                Log::warning('Duplicate report number detected, retrying...', [
                    'error'   => $e->getMessage(),
                    'user_id' => Auth::id()
                ]);
                usleep(200000);
                return $this->store($request);
            }

            Log::error('Failed to create CeRMAT report', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat laporan. Kesalahan database.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to create CeRMAT report', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat laporan. Kesalahan server.');
        }
    }

    /**
     * Show edit report form
     */
    public function edit(CermatReport $report)
    {
        if (!$report->canUserEdit()) {
            abort(403, 'Anda tidak diizinkan mengubah laporan ini pada status saat ini.');
        }

        $breadcrumbs = [
            ['label' => 'CeRMAT Reports', 'url' => route('cermat.reports.index')],
            ['label' => 'Edit', 'url' => '#'],
        ];

        $data        = $this->getFormDropdownData();
        $currentUser = Auth::user();
        $supervisor  = $this->getDirectSupervisor($currentUser);

        return view('cermat.form', array_merge($data, compact('report', 'breadcrumbs', 'supervisor')));
    }

    /**
     * Update existing report
     */
    public function update(UpdateCermatReportRequest $request, CermatReport $report)
    {
        if (!$report->canUserEdit()) {
            return back()->with('error', 'Laporan tidak dapat diubah pada status saat ini.');
        }

        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $this->handleAttachmentDeletion($report, $validatedData['delete_attachments'] ?? []);

            if ($request->hasFile('attachments')) {
                $this->storeAttachments($report, $request->file('attachments'), 'update');
            }

            if ($report->isReporter(Auth::id())) {
                $currentUser = Auth::user();
                $supervisor  = $this->getDirectSupervisor($currentUser);

                if ($supervisor) {
                    $validatedData['line_supervisor_id'] = $supervisor->id;
                }
            }

            unset($validatedData['delete_attachments']);
            $report->update(array_filter($validatedData, fn($value) => !is_null($value)));

            $report->syncUnsafeActsWithTracking($validatedData['selectedUnsafeActs'] ?? []);
            $report->syncUnsafeConditionsWithTracking($validatedData['selectedUnsafeConditions'] ?? []);

            DB::commit();

            Log::info('CeRMAT report updated successfully', [
                'report_id'     => $report->id,
                'report_number' => $report->report_number,
                'updated_by'    => Auth::id()
            ]);

            return redirect()
                ->route('cermat.reports.show', $report)
                ->with('success', 'Laporan berhasil diperbarui.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update CeRMAT report', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui laporan.');
        }
    }

    /**
     * Show report details
     */
    public function show(CermatReport $report)
    {
        $report->load([
            'reporter', 'lineSupervisor', 'hsseOfficer', 'closeOutPerformer', 'area',
            'unsafeActs', 'unsafeConditions', 'pelanggaran', 'securityEventCategory',
            'actionItems.responsible', 'actionItems.actionCategory', 'actionItems.photos',
            'attachments.uploader', 'riskAssessments'
        ]);

        $audits = $report->audits()->with('user')->latest()->get();

        $breadcrumbs = [
            ['label' => 'CeRMAT Reports', 'url' => route('cermat.reports.index')],
            ['label' => 'Detail ' . $report->report_number, 'url' => '#'],
        ];

        return view('cermat.show', compact('report', 'audits', 'breadcrumbs'));
    }

    /**
     * Submit supervisor approval/rejection
     */
    public function submitApproval(SubmitApprovalRequest $request, CermatReport $report)
    {
        if (!$report->isSupervisor(Auth::id())) {
            abort(403, 'Anda bukan Supervisor yang berhak.');
        }

        $decision  = $request->decision;
        $validated = $request->validated();

        if ($decision === 'reject' && empty($validated['supervisor_notes'])) {
            return back()
                ->withInput()
                ->withErrors(['supervisor_notes' => 'Alasan penolakan wajib diisi.']);
        }

        try {
            DB::beginTransaction();

            $updateData = $this->prepareSupervisorDecisionData($decision, $validated);
            $report->update($updateData);

            DB::commit();

            // ✅ Dispatch job ke queue
            SendWhatsAppNotification::dispatch(
                type: 'supervisor_decision',
                report: $report,
                decisionType: $decision,
            )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));

            Log::info('Supervisor decision submitted', [
                'report_id'  => $report->id,
                'decision'   => $decision,
                'supervisor_id' => Auth::id()
            ]);

            return redirect()
                ->route('cermat.reports.show', $report)
                ->with('success', 'Keputusan Supervisor berhasil disimpan.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Supervisor approval failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memproses persetujuan.');
        }
    }

    /**
     * Resubmit report after rejection
     */
    public function resubmit(CermatReport $report)
    {
        if (!$report->isReporter(Auth::id()) ||
            $report->supervisor_status !== CermatReport::SUPERVISOR_STATUS_REJECTED) {
            abort(403, 'Laporan tidak dapat diajukan ulang.');
        }

        try {
            $report->update([
                'status'                  => CermatReport::STATUS_IN_REVIEW,
                'supervisor_status'       => CermatReport::SUPERVISOR_STATUS_AWAITING,
                'hsse_status'             => CermatReport::HSSE_STATUS_PENDING_REVIEW,
                'supervisor_rejected_at'  => null,
                'rejection_reason'        => null,
                'submitted_at'            => now(),
            ]);

            // ✅ Dispatch job ke queue
            SendWhatsAppNotification::dispatch(
                type: 'supervisor_decision',
                report: $report,
                decisionType: 'resubmit',
            )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));

            Log::info('Report resubmitted', [
                'report_id'   => $report->id,
                'reporter_id' => Auth::id()
            ]);

            return redirect()
                ->route('cermat.reports.show', $report)
                ->with('success', 'Laporan berhasil dikirim ulang.');

        } catch (Throwable $e) {
            Log::error('Report resubmit failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal mengirim ulang laporan.');
        }
    }

    /**
     * Show HSSE review form
     */
    public function showReviewForm(CermatReport $report)
    {
        $report->load([
            'riskAssessments',
            'actionItems.responsible',
            'actionItems.actionCategory',
            'actionItems.photos'
        ]);

        $pelanggaranList = Cache::remember(
            'dropdown:pelanggaran',
            self::CACHE_DURATION,
            fn() => Pelanggaran::orderBy('name')->get(['id', 'name'])
        );

        $securityEventCategories = Cache::remember(
            'dropdown:security_cats',
            self::CACHE_DURATION,
            fn() => SecurityEventCategory::active()->orderBy('name')->get(['id', 'name'])
        );

        $actionCategories = Cache::remember(
            'dropdown:action_cats',
            self::CACHE_DURATION,
            fn() => ActionCategory::orderBy('code')->get()
        );

        $users = Cache::remember(
            'dropdown:users_active',
            self::CACHE_DURATION,
            fn() => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );

        $breadcrumbs = [
            ['label' => 'CeRMAT Reports', 'url' => route('cermat.reports.index')],
            ['label' => 'Review', 'url' => '#'],
        ];

        return view('cermat.review', compact(
            'report',
            'pelanggaranList',
            'securityEventCategories',
            'actionCategories',
            'users',
            'breadcrumbs'
        ));
    }

    /**
     * Submit HSSE review and risk assessment
     */
    public function submitReview(SubmitReviewRequest $request, CermatReport $report)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $report->update(array_merge($validatedData, [
                'hsse_officer_id'  => Auth::id(),
                'hsse_reviewed_at' => $report->hsse_reviewed_at ?? now(),
                'hsse_status'      => CermatReport::HSSE_STATUS_REVIEWED,
            ]));

            $this->saveRiskAssessments($report, $validatedData);

            DB::commit();

            Log::info('HSSE review submitted', [
                'report_id'       => $report->id,
                'hsse_officer_id' => Auth::id()
            ]);

            return redirect()
                ->route('cermat.reports.review', $report)
                ->with('success', 'Review HSSE berhasil disimpan.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('HSSE review failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()
                ->with('error', 'Gagal menyimpan review.')
                ->withInput();
        }
    }

    /**
     * Submit single action item
     */
    public function submitActionItem(StoreActionItemRequest $request, CermatReport $report)
    {
        try {
            DB::beginTransaction();

            $validated  = $request->validated();
            $actionItem = $report->actionItems()->create($validated);

            $statusChanged = $this->updateReportStatusForAction($report);

            DB::commit();

            // ✅ Dispatch job notifikasi action item ke queue
            SendWhatsAppNotification::dispatch(
                type: 'action_item',
                report: $report,
                actionItem: $actionItem,
                recipientId: $validated['responsible_id'],
            )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));

            // ✅ Dispatch job notifikasi perubahan status jika status berubah
            if ($statusChanged) {
                SendWhatsAppNotification::dispatch(
                    type: 'status_change',
                    report: $report,
                )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY + 3));
            }

            Log::info('Action item created', [
                'report_id'      => $report->id,
                'action_item_id' => $actionItem->id,
                'created_by'     => Auth::id()
            ]);

            return back()->with('success', 'Tindakan perbaikan berhasil ditambahkan.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to create action item', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menambahkan tindakan.');
        }
    }

    /**
     * Submit bulk action items
     */
    public function submitBulkActionItems(Request $request, CermatReport $report)
    {
        $validated = $request->validate([
            'actions'                        => 'required|array|min:1|max:' . self::MAX_BULK_ACTIONS,
            'actions.*.description'          => 'required|string|max:1000',
            'actions.*.action_category_id'   => 'required|exists:action_categories,id',
            'actions.*.target_date'          => 'required|date|after_or_equal:today',
            'actions.*.responsible_id'       => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $responsibleUsers = [];

            foreach ($validated['actions'] as $actionData) {
                $actionItem = $report->actionItems()->create($actionData);

                if (!in_array($actionData['responsible_id'], $responsibleUsers, true)) {
                    $responsibleUsers[] = $actionData['responsible_id'];
                }

                // ✅ Dispatch notifikasi per action item
                SendWhatsAppNotification::dispatch(
                    type: 'action_item',
                    report: $report,
                    actionItem: $actionItem,
                    recipientId: $actionData['responsible_id'],
                )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));
            }

            $statusChanged = $this->updateReportStatusForAction($report);

            DB::commit();

            // ✅ Dispatch notifikasi perubahan status jika berubah
            if ($statusChanged) {
                SendWhatsAppNotification::dispatch(
                    type: 'status_change',
                    report: $report,
                )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY + 3));
            }

            Log::info('Bulk action items created', [
                'report_id'  => $report->id,
                'count'      => count($validated['actions']),
                'created_by' => Auth::id()
            ]);

            return back()->with('success', 'Tindakan masal berhasil ditambahkan.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to create bulk action items', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menambahkan tindakan masal.');
        }
    }

    /**
     * Extend action item deadline
     */
    public function extendActionItem(ExtendActionItemRequest $request, ActionItem $actionItem)
    {
        if (!Auth::user()->hasRole('hsse')) {
            abort(403, 'Hanya HSSE yang dapat memperpanjang deadline.');
        }

        $validated       = $request->validated();
        $extensionNumber = $actionItem->extension_count + 1;

        if ($extensionNumber > self::MAX_EXTENSIONS) {
            return back()->with('error', 'Batas perpanjangan (' . self::MAX_EXTENSIONS . 'x) tercapai.');
        }

        try {
            $actionItem->update([
                "extend_date_{$extensionNumber}"   => $validated['new_target_date'],
                "extend_reason_{$extensionNumber}" => $validated['extend_reason'],
                'target_date'                      => $validated['new_target_date'],
                'extension_count'                  => $extensionNumber,
            ]);

            Log::info('Action item deadline extended', [
                'action_item_id'   => $actionItem->id,
                'extension_number' => $extensionNumber,
                'new_date'         => $validated['new_target_date'],
                'extended_by'      => Auth::id()
            ]);

            return back()->with('success', 'Target tanggal diperpanjang.');

        } catch (Throwable $e) {
            Log::error('Failed to extend action item', [
                'action_item_id' => $actionItem->id,
                'error'          => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memperpanjang waktu.');
        }
    }

    /**
     * Update action item status
     */
    public function updateActionStatus(UpdateActionStatusRequest $request, ActionItem $actionItem)
    {
        if (!($actionItem->responsible_id === Auth::id() || Auth::user()->hasRole('hsse'))) {
            abort(403, 'Akses ditolak.');
        }

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $this->updateActionItemStatus($actionItem, $validated);

            if ($request->hasFile('proof_photos')) {
                $this->handleProofPhotos($actionItem, $request->file('proof_photos'));
            }

            $shouldNotifyCloseout = $this->checkAndUpdateReportCloseout($actionItem);

            DB::commit();

            // ✅ Dispatch notifikasi closeout jika semua action selesai
            if ($shouldNotifyCloseout) {
                SendWhatsAppNotification::dispatch(
                    type: 'status_change',
                    report: $actionItem->cermatReport,
                )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));
            }

            Log::info('Action item status updated', [
                'action_item_id' => $actionItem->id,
                'new_status'     => $validated['status'],
                'updated_by'     => Auth::id()
            ]);

            return back()->with('success', 'Status tindakan diperbarui.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update action status', [
                'action_item_id' => $actionItem->id,
                'error'          => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memperbarui status.');
        }
    }

    /**
     * Close report (HSSE final closeout)
     */
    public function submitCloseReport(CloseReportRequest $request, CermatReport $report)
    {
        if (!Auth::user()->hasRole('hsse') ||
            $report->status !== CermatReport::STATUS_AWAITING_CLOSEOUT) {
            abort(403, 'Akses ditolak.');
        }

        try {
            DB::beginTransaction();

            $report->update([
                'status'                  => CermatReport::STATUS_CLOSED,
                'close_out_at'            => now(),
                'close_out_performer_id'  => Auth::id(),
                'close_out_description'   => $request->validated()['close_out_description'],
            ]);

            DB::commit();

            // ✅ Dispatch job ke queue
            SendWhatsAppNotification::dispatch(
                type: 'status_change',
                report: $report,
            )->delay(now()->addSeconds(self::NOTIFICATION_DISPATCH_DELAY));

            Log::info('Report closed', [
                'report_id' => $report->id,
                'closed_by' => Auth::id()
            ]);

            return redirect()
                ->route('cermat.reports.show', $report)
                ->with('success', 'Laporan berhasil ditutup.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to close report', [
                'report_id' => $report->id,
                'error'     => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menutup laporan.');
        }
    }

    /**
     * Download report as PDF
     */
    public function downloadPdf(CermatReport $report)
    {
        $report->load([
            'reporter', 'lineSupervisor', 'hsseOfficer', 'closeOutPerformer', 'area',
            'unsafeActs', 'unsafeConditions', 'pelanggaran', 'securityEventCategory',
            'actionItems.responsible', 'actionItems.actionCategory',
            'attachments', 'riskAssessments'
        ]);

        $safeReportNumber = str_replace(['/', '\\'], '_', $report->report_number);
        $filename         = 'Laporan_CeRMAT_' . $safeReportNumber . '.pdf';

        return Pdf::loadView('cermat.pdf', compact('report'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->download($filename);
    }

    /**
     * Export reports to Excel
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['super-admin', 'hsse', 'koordinator', 'rses'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengexport data.');
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $companyId = !$user->hasRole('super-admin') ? $user->company_id : null;
        $fileName  = 'Laporan_CeRMAT_' . now()->format('Ymd_His') . '.xlsx';

        Log::info('Reports exported', [
            'exported_by' => $user->id,
            'company_id'  => $companyId,
            'start_date'  => $startDate,
            'end_date'    => $endDate
        ]);

        return Excel::download(
            new CermatReportsExport($startDate, $endDate, $companyId),
            $fileName
        );
    }

    /**
     * Delete report - Hard delete for super-admin, soft delete for others
     */
    public function destroy(CermatReport $report): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user         = Auth::user();
            $reportId     = $report->id;
            $reportNumber = $report->report_number;

            $report->attachments()->each(function ($attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            });

            $report->actionItems()->each(function ($actionItem) {
                $actionItem->photos()->each(function ($photo) {
                    if (Storage::disk('public')->exists($photo->file_path)) {
                        Storage::disk('public')->delete($photo->file_path);
                    }
                });
            });

            if ($user->hasRole('super-admin')) {
                $report->forceDelete();
                $deleteType = 'permanently deleted';
            } else {
                $report->delete();
                $deleteType = 'soft deleted';
            }

            DB::commit();

            Log::info('Report deleted', [
                'report_id'     => $reportId,
                'report_number' => $reportNumber,
                'delete_type'   => $deleteType,
                'deleted_by'    => $user->id,
                'user_role'     => $user->roles->pluck('name')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus.'
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete report', [
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan.'
            ], 500);
        }
    }

    // ============================================================
    // PRIVATE HELPER METHODS
    // ============================================================

    /**
     * Apply scope-based filtering to query
     */
    private function applyScopeFilter($query, string $scope, User $user): void
    {
        switch ($scope) {
            case 'verification':
                $query->where('line_supervisor_id', $user->id)
                    ->where('supervisor_status', CermatReport::SUPERVISOR_STATUS_AWAITING);
                break;

            case 'review':
                $query->where('status', CermatReport::STATUS_IN_REVIEW)
                    ->whereIn('hsse_status', [
                        CermatReport::HSSE_STATUS_PENDING_REVIEW,
                        CermatReport::HSSE_STATUS_REVIEWED
                    ]);
                break;

            case 'action_items':
                $query->whereHas('actionItems', fn($q) =>
                    $q->where('responsible_id', $user->id)
                        ->whereNotIn('status', [
                            ActionItem::STATUS_COMPLETED,
                            ActionItem::STATUS_CANT_DO
                        ])
                );
                break;

            case 'all':
                if ($user->hasAnyRole(['hsse', 'super-admin', 'rses'])) {
                    // No filter - global access
                } elseif ($user->hasRole('koordinator')) {
                    $query->whereHas('reporter', fn($q) =>
                        $q->where('company_id', $user->company_id)
                    );
                } else {
                    $query->where('reporter_id', $user->id);
                }
                break;

            case 'my_reports':
            default:
                $query->where('reporter_id', $user->id);
                break;
        }
    }

    /**
     * Apply advanced filters for 'all' scope
     */
    private function applyAdvancedFilters($query, Request $request, User $user): void
    {
        if ($request->filled('company_id') && !$user->hasRole('koordinator')) {
            $query->whereHas('reporter', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        if ($request->filled('date_start')) {
            $query->where('report_datetime', '>=', Carbon::parse($request->date_start)->startOfDay());
        }

        if ($request->filled('date_end')) {
            $query->where('report_datetime', '<=', Carbon::parse($request->date_end)->endOfDay());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }

    /**
     * Get direct supervisor (N+1) based on entity function hierarchy
     */
    private function getDirectSupervisor(User $user): ?User
    {
        $userWithHierarchy = User::with('entityFunction.parent')->find($user->id);

        if (!$userWithHierarchy || !$userWithHierarchy->entityFunction) {
            return $this->getHsseUser();
        }

        $entityFunction = $userWithHierarchy->entityFunction;

        if (!$entityFunction->parent_id) {
            return $this->getHsseUser();
        }

        $parentEntityFunction = $entityFunction->parent;

        if (!$parentEntityFunction) {
            return $this->getHsseUser();
        }

        $supervisor = User::where('is_active', true)
            ->where('entity_function_id', $parentEntityFunction->id)
            ->first();

        return $supervisor ?? $this->getHsseUser();
    }

    /**
     * Get HSSE user as fallback supervisor
     */
    private function getHsseUser(): ?User
    {
        return Cache::remember(
            'hsse_user_fallback',
            self::CACHE_DURATION,
            function () {
                return User::where('is_active', true)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'hsse');
                    })
                    ->first();
            }
        );
    }

    /**
     * Get dropdown data for forms
     */
    private function getFormDropdownData(): array
    {
        $areas = Cache::remember(
            'dropdown:areas',
            self::CACHE_DURATION,
            fn() => Area::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );

        $unsafeActs = Cache::remember(
            'dropdown:unsafe_acts',
            self::CACHE_DURATION,
            fn() => UnsafeAct::where('is_active', true)->orderBy('description')->get(['id', 'description'])
        );

        $unsafeConditions = Cache::remember(
            'dropdown:unsafe_conditions',
            self::CACHE_DURATION,
            fn() => UnsafeCondition::where('is_active', true)->orderBy('description')->get(['id', 'description'])
        );

        return compact('areas', 'unsafeActs', 'unsafeConditions');
    }

    /**
     * Generate unique report number with retry logic and database locking
     */
    private function generateReportNumber(): string
    {
        $attempt = 0;

        while ($attempt < self::MAX_REPORT_NUMBER_ATTEMPTS) {
            try {
                $year  = Carbon::now()->format('Y');
                $month = Carbon::now()->format('m');

                $lastReport = CermatReport::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                $count = 1;

                if ($lastReport && $lastReport->report_number) {
                    if (preg_match('/\/(\d+)$/', $lastReport->report_number, $matches)) {
                        $count = (int) $matches[1] + 1;
                    }
                }

                $reportNumber = sprintf('CERMAT/%s/%s/%04d', $year, $month, $count);

                $exists = CermatReport::where('report_number', $reportNumber)
                    ->lockForUpdate()
                    ->exists();

                if (!$exists) {
                    Log::debug('Report number generated successfully', [
                        'report_number' => $reportNumber,
                        'attempt'       => $attempt + 1
                    ]);
                    return $reportNumber;
                }

                $attempt++;
                Log::warning('Report number collision detected, retrying...', [
                    'report_number' => $reportNumber,
                    'attempt'       => $attempt
                ]);

                if ($attempt < self::MAX_REPORT_NUMBER_ATTEMPTS) {
                    usleep(100000);
                }

            } catch (\Exception $e) {
                Log::error('Error generating report number', [
                    'attempt' => $attempt + 1,
                    'error'   => $e->getMessage()
                ]);

                $attempt++;

                if ($attempt >= self::MAX_REPORT_NUMBER_ATTEMPTS) {
                    throw $e;
                }

                usleep(100000);
            }
        }

        // Fallback dengan timestamp suffix
        $year      = Carbon::now()->format('Y');
        $month     = Carbon::now()->format('m');
        $baseCount = CermatReport::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        $fallbackNumber = sprintf(
            'CERMAT/%s/%s/%04d-%s',
            $year,
            $month,
            $baseCount,
            substr(uniqid(), -4)
        );

        Log::warning('Using fallback report number with unique suffix', [
            'report_number' => $fallbackNumber,
            'reason'        => 'Max attempts reached'
        ]);

        return $fallbackNumber;
    }

    /**
     * Create new report with generated report number
     */
    private function createReport(array $validatedData, Request $request, string $reportNumber): CermatReport
    {
        return CermatReport::create(array_merge($validatedData, [
            'report_number'    => $reportNumber,
            'reporter_id'      => Auth::id(),
            'status'           => CermatReport::STATUS_IN_REVIEW,
            'supervisor_status' => $validatedData['line_supervisor_id']
                ? CermatReport::SUPERVISOR_STATUS_AWAITING
                : CermatReport::SUPERVISOR_STATUS_NOT_REQUIRED,
            'hsse_status'      => CermatReport::HSSE_STATUS_PENDING_REVIEW,
            'submitted_at'     => now(),
            'stop_card_issued' => $request->boolean('stop_card_issued'),
            'requires_feedback' => $request->boolean('requires_feedback'),
        ]));
    }

    /**
     * Handle attachment deletion
     */
    private function handleAttachmentDeletion(CermatReport $report, array $deleteIds): void
    {
        if (empty($deleteIds)) {
            return;
        }

        $attachmentsToDelete = ReportAttachment::where('cermat_report_id', $report->id)
            ->whereIn('id', $deleteIds)
            ->get();

        foreach ($attachmentsToDelete as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            $attachment->delete();
        }
    }

    /**
     * Store report attachments with optimization
     */
    private function storeAttachments(CermatReport $report, array $files, string $stage): void
    {
        $manager = new ImageManager(new Driver());

        foreach ($files as $file) {
            try {
                $extension = strtolower($file->getClientOriginalExtension());
                $isImage   = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                $path      = $file->store('report_attachments', 'public');

                if ($isImage) {
                    try {
                        $image = $manager->read($file);

                        if ($image->width() > self::MAX_IMAGE_WIDTH) {
                            $image->scaleDown(width: self::MAX_IMAGE_WIDTH);
                        }

                        $encoded = match ($extension) {
                            'jpg', 'jpeg' => $image->toJpeg(quality: self::IMAGE_QUALITY),
                            'png'         => $image->toPng(),
                            'webp'        => $image->toWebp(quality: self::IMAGE_QUALITY),
                            'gif'         => $image->toGif(),
                            default       => $image->toJpeg(quality: self::IMAGE_QUALITY)
                        };

                        Storage::disk('public')->put($path, (string) $encoded);

                    } catch (\Exception $e) {
                        Log::warning('Image optimization failed, using original', [
                            'path'  => $path,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $report->attachments()->create([
                    'uploaded_by_id' => Auth::id(),
                    'upload_stage'   => $stage,
                    'file_path'      => $path,
                    'file_name'      => $file->getClientOriginalName(),
                    'mime_type'      => $file->getMimeType(),
                    'file_size'      => $file->getSize(),
                ]);

            } catch (\Exception $e) {
                Log::error('Attachment upload failed', [
                    'report_id' => $report->id,
                    'error'     => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Prepare supervisor decision data
     */
    private function prepareSupervisorDecisionData(string $decision, array $validated): array
    {
        return match ($decision) {
            'approve' => [
                'supervisor_status'      => CermatReport::SUPERVISOR_STATUS_APPROVED,
                'supervisor_approved_at' => now(),
                'supervisor_notes'       => $validated['supervisor_notes'],
                'rejection_reason'       => null,
            ],
            'reject' => [
                'supervisor_status'      => CermatReport::SUPERVISOR_STATUS_REJECTED,
                'supervisor_rejected_at' => now(),
                'rejection_reason'       => $validated['supervisor_notes'],
                'supervisor_notes'       => null,
            ],
            'no_action' => [
                'status'                 => CermatReport::STATUS_NO_ACTION_REQUIRED,
                'supervisor_status'      => CermatReport::SUPERVISOR_STATUS_APPROVED,
                'supervisor_approved_at' => now(),
                'supervisor_notes'       => $validated['supervisor_notes'],
            ],
            default => [],
        };
    }

    /**
     * Save risk assessments for report
     */
    private function saveRiskAssessments(CermatReport $report, array $validatedData): void
    {
        foreach (['initial', 'residual'] as $type) {
            if (!isset($validatedData["{$type}_risk"])) {
                continue;
            }

            foreach (self::CONSEQUENCE_CATEGORIES as $category) {
                if (!isset($validatedData["{$type}_risk"][$category])) {
                    continue;
                }

                $riskData = $validatedData["{$type}_risk"][$category];
                $riskData['real_severity']      = $riskData['real_severity'] ?? null;
                $riskData['potential_severity'] = $riskData['potential_severity'] ?? null;
                $riskData['likelihood']         = $riskData['likelihood'] ?? null;

                $riskModel = $report->riskAssessments()->updateOrCreate(
                    ['type' => $type, 'consequence_category' => $category],
                    $riskData
                );

                if (method_exists($riskModel, 'calculateRiskScore')) {
                    $riskModel->calculateRiskScore();
                }
            }
        }
    }

    /**
     * Update report status when action item added
     */
    private function updateReportStatusForAction(CermatReport $report): bool
    {
        if ($report->status === CermatReport::STATUS_IN_REVIEW || $report->actionItems()->count() === 1) {
            $report->update([
                'status'      => CermatReport::STATUS_ACTION_IN_PROGRESS,
                'hsse_status' => CermatReport::HSSE_STATUS_RECOMMENDED
            ]);
            return true;
        }

        return false;
    }

    /**
     * Update action item status and completion
     */
    private function updateActionItemStatus(ActionItem $actionItem, array $validated): void
    {
        $updateData = ['status' => $validated['status']];

        if (in_array($validated['status'], [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO], true)) {
            $updateData['completion_notes'] = $validated['completion_notes'];
            $updateData['completed_at']     = now();
        }

        $actionItem->update($updateData);
    }

    /**
     * Handle proof photo uploads with optimization
     */
    private function handleProofPhotos(ActionItem $actionItem, array $photos): void
    {
        $manager = new ImageManager(new Driver());

        foreach ($photos as $photo) {
            try {
                $extension = strtolower($photo->getClientOriginalExtension());
                $filename  = uniqid() . '_' . time() . '.' . $extension;
                $path      = $photo->storeAs('action-item-proofs', $filename, 'public');

                try {
                    $image = $manager->read($photo);

                    if ($image->width() > self::MAX_IMAGE_WIDTH) {
                        $image->scaleDown(width: self::MAX_IMAGE_WIDTH);
                    }

                    $encoded = match ($extension) {
                        'jpg', 'jpeg' => $image->toJpeg(quality: self::IMAGE_QUALITY),
                        'png'         => $image->toPng(),
                        'gif'         => $image->toGif(),
                        default       => $image->toJpeg(quality: self::IMAGE_QUALITY)
                    };

                    Storage::disk('public')->put($path, (string) $encoded);

                } catch (\Exception $e) {
                    Log::warning('Proof photo optimization failed', [
                        'action_item_id' => $actionItem->id,
                        'error'          => $e->getMessage()
                    ]);
                }

                $actionItem->photos()->create([
                    'file_path'  => $path,
                    'file_name'  => $photo->getClientOriginalName(),
                    'mime_type'  => $photo->getClientMimeType(),
                    'file_size'  => $photo->getSize(),
                    'photo_type' => 'after'
                ]);

            } catch (\Exception $e) {
                Log::error('Proof photo upload failed', [
                    'action_item_id' => $actionItem->id,
                    'error'          => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Check if all actions completed and update report status
     */
    private function checkAndUpdateReportCloseout(ActionItem $actionItem): bool
    {
        $report = $actionItem->cermatReport;

        $allCompleted = $report->actionItems->every(fn($item) =>
            in_array($item->status, [
                ActionItem::STATUS_COMPLETED,
                ActionItem::STATUS_CANT_DO
            ], true)
        );

        if ($allCompleted && $report->status === CermatReport::STATUS_ACTION_IN_PROGRESS) {
            $report->update(['status' => CermatReport::STATUS_AWAITING_CLOSEOUT]);
            return true;
        }

        return false;
    }

    /**
     * Render status badge HTML
     */
    private function renderStatusBadge(string $status): string
    {
        $statusSlug = strtolower(str_replace([' ', '_'], '-', $status));

        $badgeClass = match (true) {
            str_contains($statusSlug, 'action-in-progress'),
            str_contains($statusSlug, 'in-review')  => 'info',
            str_contains($statusSlug, 'awaiting')   => 'warning',
            str_contains($statusSlug, 'closed')     => 'success',
            str_contains($statusSlug, 'rejected')   => 'danger',
            default                                 => 'secondary',
        };

        return sprintf(
            '<span class="badge badge-light-%s">%s</span>',
            $badgeClass,
            htmlspecialchars($status, ENT_QUOTES, 'UTF-8')
        );
    }
}
