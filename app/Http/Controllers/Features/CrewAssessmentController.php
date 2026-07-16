<?php
namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Models\CrewAssessment;
use App\Models\CrewAssessmentAttachment;
use App\Models\Company;
use App\Models\Vessel;
use App\Models\CrewMember;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Border, Alignment};
use App\Imports\CrewAssessmentImport;
use Maatwebsite\Excel\Facades\Excel;

class CrewAssessmentController extends Controller
{
    use AuthorizesRequests;

    // ── 2 MB = 2048 KB ───────────────────────────────────────────────────────
    private const MAX_UPLOAD_KB = 2048;

    private function canManage(): bool
    {
        return Auth::user()->hasAnyRole(['super-admin', 'hsse']);
    }

    // ═══ DASHBOARD ═══════════════════════════════════════════════════════════

    public function dashboard()
    {
        $this->authorize('view crew assessment');
        $canManage = $this->canManage();
        $user      = Auth::user();
        $base = fn () => $canManage
            ? CrewAssessment::query()
            : CrewAssessment::where('company_id', $user->company_id);

        $totalAll        = $base()->count();
        $totalLulus      = $base()->where('result', 'Lulus')->count();
        $totalPending    = $base()->where('result', 'Pending')->count();
        $totalTidakLulus = $base()->where('result', 'Tidak Lulus')->count();
        $thisMonth       = $base()
            ->whereMonth('assessment_date', now()->month)
            ->whereYear('assessment_date', now()->year)
            ->count();

        // 12-month trend
        $rawTrend = $base()
            ->selectRaw("DATE_FORMAT(assessment_date,'%Y-%m') as month, count(*) as total")
            ->where('assessment_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $trend = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $trend->push(['month' => now()->subMonths($i)->format('M Y'), 'total' => $rawTrend[$key] ?? 0]);
        }

        $byMev      = $base()->selectRaw('mev_type, count(*) as total')->groupBy('mev_type')->orderByDesc('total')->get();
        $byResult   = $base()->selectRaw('result, count(*) as total')->groupBy('result')->orderByDesc('total')->get();
        $byLocation = $base()->selectRaw('assessment_location, count(*) as total')->groupBy('assessment_location')->orderByDesc('total')->get();
        $byPosition = $base()->selectRaw('position_proposed, count(*) as total')->groupBy('position_proposed')->orderByDesc('total')->limit(10)->get();

        $pendingList = $base()
            ->with(['company:id,name', 'vessel:id,name', 'crewMember:id,name'])
            ->where('result', 'Pending')
            ->latest('assessment_date')
            ->limit(10)
            ->get();

        $recentList = $base()
            ->with(['company:id,name', 'vessel:id,name', 'crewMember:id,name', 'creator:id,name'])
            ->latest('assessment_date')
            ->limit(10)
            ->get();

        $byCompany = $canManage
            ? CrewAssessment::with('company:id,name')
                ->selectRaw('company_id, result, count(*) as total')
                ->groupBy('company_id', 'result')
                ->get()
                ->groupBy('company_id')
            : collect();

        return view('features.crew-assessment.dashboard', compact(
            'totalAll', 'totalLulus', 'totalPending', 'totalTidakLulus', 'thisMonth',
            'trend', 'byMev', 'byResult', 'byLocation', 'byPosition',
            'pendingList', 'recentList', 'byCompany', 'canManage'
        ));
    }

    // ═══ INDEX ════════════════════════════════════════════════════════════════

    public function index()
    {
        $this->authorize('view crew assessment');
        $user      = Auth::user();
        $companies = $this->canManage() ? Company::orderBy('name')->get() : collect();
        $vessels   = $this->canManage()
            ? Vessel::orderBy('name')->get()
            : Vessel::where('company_id', $user->company_id)->orderBy('name')->get();

        return view('features.crew-assessment.index', compact('companies', 'vessels'));
    }

    // ═══ INDEX DATA (DataTables) ══════════════════════════════════════════════

    public function indexData(Request $request)
    {
        $this->authorize('view crew assessment');
        $user  = Auth::user();
        $query = CrewAssessment::with(['company', 'vessel', 'crewMember', 'attachments']);

        if (! $this->canManage()) {
            $query->where('company_id', $user->company_id);
        } else {
            if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        }

        if ($request->filled('vessel_id'))           $query->where('vessel_id', $request->vessel_id);
        if ($request->filled('mev_type'))             $query->where('mev_type', $request->mev_type);
        if ($request->filled('result'))               $query->where('result', $request->result);
        if ($request->filled('assessment_location'))  $query->where('assessment_location', $request->assessment_location);
        if ($request->filled('position_proposed'))    $query->where('position_proposed', $request->position_proposed);
        if ($request->filled('position_type'))        $query->where('position_type', $request->position_type);
        if ($request->filled('start_date'))           $query->where('assessment_date', '>=', $request->start_date);
        if ($request->filled('end_date'))             $query->where('assessment_date', '<=', $request->end_date);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no_sertifikat',     fn ($r) => $r->certificate_number ?? '—')
            ->addColumn('nama',              fn ($r) => e($r->crewMember?->name ?? $r->crew_name_text ?? '—'))
            ->addColumn('tgl_lahir',         fn ($r) => '—') // CrewMember tidak punya birth_date
            ->addColumn('coc_col',           fn ($r) => $r->coc ?? $r->crewMember?->position ?? '—')
            ->addColumn('jabatan_diusulkan', fn ($r) => $r->position_proposed ?? '—')
            ->addColumn('jabatan_tipe',      fn ($r) => $r->position_type ?? '—')
            ->addColumn('kapal',             fn ($r) => $r->vessel?->name ?? $r->vessel_name_text ?? '—')
            ->addColumn('exp_pertamina',     fn ($r) => $r->experience_pertamina ?? '—')
            ->addColumn('exp_master',        fn ($r) => $r->experience_master    ?? '—')
            ->addColumn('exp_diluar',        fn ($r) => $r->experience_outside   ?? '—')
            ->addColumn('mev',               fn ($r) => $r->mev_type ?? '—')
            ->addColumn('tgl_assessment',    fn ($r) => $r->assessment_date->format('d/m/Y'))
            ->addColumn('lokasi',            fn ($r) => $r->assessment_location ?? '—')
            ->addColumn('assessor_mar',      fn ($r) => $r->assessor_mar ?? '—')
            ->addColumn('assessor_hse',      fn ($r) => $r->assessor_hse ?? '—')
            ->addColumn('assessor_fmc',      fn ($r) => $r->assessor_fmc ?? '—')
            ->addColumn('hasil_badge', function ($r) {
                $c = $r->result_color;
                $l = $r->result ?? '—';
                return '<span class="badge bg-' . $c . '">' . e($l) . '</span>';
            })
            ->addColumn('perusahaan',  fn ($r) => $r->company?->name ?? $r->company_name_text ?? '—')
            ->addColumn('lamp_count',  fn ($r) => $r->attachments->count())
            ->addColumn('action', function ($r) {
                $btn  = '<div class="d-flex gap-1 justify-content-center">';
                $btn .= '<a href="' . route('crew-assessment.show', $r->id) . '" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>';
                if ($this->canManage()) {
                    $btn .= '<a href="' . route('crew-assessment.edit', $r->id) . '" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button class="btn btn-sm btn-outline-danger delete-record" data-url="' . route('crew-assessment.destroy', $r->id) . '" data-name="' . e($r->crewMember?->name ?? '—') . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['hasil_badge', 'action'])
            ->make(true);
    }

    // ═══ CREATE ═══════════════════════════════════════════════════════════════

    public function create()
    {
        $this->authorize('manage crew assessment');
        $user      = Auth::user();
        $companies = Company::orderBy('name')->get();
        $vessels   = $this->canManage()
            ? Vessel::orderBy('name')->get()
            : Vessel::where('company_id', $user->company_id)->orderBy('name')->get();

        return view('features.crew-assessment.create', compact('companies', 'vessels'));
    }

    // ═══ STORE ════════════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $this->authorize('manage crew assessment');

        $this->resolveOrCreateFormEntities($request);

        $validated = $request->validate($this->rules());

        // Crew bisa dari perusahaan manapun — tidak perlu cocok dengan company assessment
        $crewMember = null;
        if (!empty($validated['crew_member_id'])) {
            $crewMember = CrewMember::find($validated['crew_member_id']);
        }

        // Auto-isi COC dari posisi kru jika kosong
        if ($crewMember && empty($validated['coc']) && $crewMember->position) {
            $validated['coc'] = $crewMember->position;
        }

        $validated['created_by'] = Auth::id();
        $validated['status']     = $this->deriveStatus($validated);

        DB::transaction(function () use ($request, $validated) {
            $assessment = CrewAssessment::create($validated);
            $this->handleAttachments($request, $assessment);
        });

        return redirect()->route('crew-assessment.index')
            ->with('success', 'Assessment berhasil disimpan.');
    }

    // ═══ SHOW ═════════════════════════════════════════════════════════════════

    public function show(CrewAssessment $crewAssessment)
    {
        $this->authorize('view crew assessment');
        $this->authorizeCompanyAccess($crewAssessment);

        // Eager load semua relasi yang dibutuhkan view
        $crewAssessment->load([
            'company:id,name',
            'vessel:id,name',
            'crewMember:id,name,nik,position,phone,is_active,company_id',
            'crewMember.company:id,name',
            'creator:id,name',
            'attachments.uploader:id,name',
        ]);

        return view('features.crew-assessment.show', compact('crewAssessment'));
    }

    // ═══ EDIT ═════════════════════════════════════════════════════════════════

    public function edit(CrewAssessment $crewAssessment)
    {
        $this->authorize('manage crew assessment');
        $this->authorizeCompanyAccess($crewAssessment);

        $crewAssessment->load([
            'company:id,name',
            'vessel:id,name',
            'crewMember:id,name,nik,position',
            'attachments',
        ]);

        $companies = Company::orderBy('name')->get();

        // Kapal milik company assessment ini (untuk dropdown vessel)
        $vessels = Vessel::where('company_id', $crewAssessment->company_id)
            ->orderBy('name')->get();

        // Crew tidak lagi di-load di sini — ditangani via Select2 AJAX searchCrew
        // sehingga crew bisa dicari lintas perusahaan & vessel

        return view('features.crew-assessment.edit',
            compact('crewAssessment', 'companies', 'vessels'));
    }

    // ═══ UPDATE ═══════════════════════════════════════════════════════════════

    public function update(Request $request, CrewAssessment $crewAssessment)
    {
        $this->authorize('manage crew assessment');
        $this->authorizeCompanyAccess($crewAssessment);

        $this->resolveOrCreateFormEntities($request);

        $validated = $request->validate($this->rulesForUpdate());

        // Crew bisa dari perusahaan manapun — tidak perlu cocok dengan company assessment
        DB::transaction(function () use ($request, $validated, $crewAssessment) {
            $crewAssessment->update($validated);

            // Hapus lampiran yang ditandai
            if (! empty($validated['delete_attachment_ids'])) {
                $crewAssessment->attachments()
                    ->whereIn('id', $validated['delete_attachment_ids'])
                    ->get()
                    ->each(fn ($a) => $a->deleteFile());
            }

            $this->handleAttachments($request, $crewAssessment, 'new_attachments', 'new_attachment_labels');
        });

        return redirect()
            ->route('crew-assessment.show', $crewAssessment->id)
            ->with('success', 'Assessment berhasil diperbarui.');
    }

    // ═══ DESTROY ══════════════════════════════════════════════════════════════

    public function destroy(CrewAssessment $crewAssessment)
    {
        $this->authorize('manage crew assessment');
        $this->authorizeCompanyAccess($crewAssessment);

        DB::transaction(function () use ($crewAssessment) {
            $crewAssessment->attachments->each(fn ($a) => $a->deleteFile());
            $crewAssessment->delete();
        });

        return response()->json(['success' => true, 'message' => 'Assessment berhasil dihapus.']);
    }

    // ═══ ATTACHMENTS ══════════════════════════════════════════════════════════

    public function downloadAttachment(CrewAssessmentAttachment $attachment)
    {
        $this->authorize('view crew assessment');
        $this->authorizeCompanyAccess($attachment->assessment);

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($attachment->disk);
        return $disk->download($attachment->path, $attachment->original_name);
    }

    public function destroyAttachment(CrewAssessmentAttachment $attachment)
    {
        $this->authorize('manage crew assessment');
        $this->authorizeCompanyAccess($attachment->assessment);
        $attachment->deleteFile();
        return response()->json(['success' => true, 'message' => 'Lampiran dihapus.']);
    }

    // ═══ AJAX ═════════════════════════════════════════════════════════════════

    public function getVesselsByCompany(Request $request)
    {
        $this->authorize('view crew assessment');
        $request->validate(['company_id' => 'required|exists:companies,id']);

        $user = Auth::user();
        if (! $this->canManage() && (string) $request->company_id !== (string) $user->company_id) {
            return response()->json([], 403);
        }

        return response()->json(
            Vessel::where('company_id', $request->company_id)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    /**
     * Kembalikan kru berdasarkan vessel_id.
     * Strategy:
     *  1. Coba kru yang aktif terassign ke vessel ini.
     *  2. Jika kosong, fallback: semua kru aktif di company vessel tersebut.
     * Ini menghindari dropdown kosong karena crew tidak assign ke vessel.
     */
    public function getCrewByVessel(Request $request)
    {
        $this->authorize('view crew assessment');
        $request->validate(['vessel_id' => 'required|exists:vessels,id']);

        $vessel = Vessel::findOrFail($request->vessel_id, ['id', 'company_id', 'name']);

        // Coba kru yang terassign ke vessel ini (is_active & not unassigned)
        $crew = CrewMember::where('is_active', true)
            ->where('company_id', $vessel->company_id)
            ->whereHas('vesselAssignments', fn ($q) =>
                $q->where('vessel_id', $request->vessel_id)
                  ->where('is_active', true)
                  ->whereNull('unassigned_at')
            )
            ->orderBy('name')
            ->get(['id', 'name', 'position', 'nik']);

        // Fallback: semua kru aktif di company
        if ($crew->isEmpty()) {
            $crew = CrewMember::where('is_active', true)
                ->where('company_id', $vessel->company_id)
                ->orderBy('name')
                ->get(['id', 'name', 'position', 'nik']);
        }

        return response()->json($crew->map(fn ($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'position' => $c->position ?? '',
            'nik'      => $c->nik      ?? '',
        ]));
    }

    public function getCrewByCompany(Request $request)
    {
        $this->authorize('view crew assessment');
        $request->validate(['company_id' => 'required|exists:companies,id']);

        $crew = CrewMember::where('is_active', true)
            ->where('company_id', $request->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'position', 'nik']);

        return response()->json($crew->map(fn ($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'position' => $c->position ?? '',
            'nik'      => $c->nik      ?? '',
        ]));
    }

    /**
     * Search crew members by name / NIK lintas perusahaan & vessel.
     * Crew bisa berpindah-pindah perusahaan dan vessel, sehingga pencarian
     * tidak dibatasi oleh company_id atau vessel_id.
     * Digunakan oleh Select2 AJAX pada form create & edit assessment.
     */
    public function searchCrew(Request $request)
    {
        $this->authorize('view crew assessment');
        $request->validate(['q' => 'nullable|string|max:100']);

        $term = trim($request->input('q', ''));

        $crew = CrewMember::with('company:id,name')
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('nik', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'position', 'nik', 'company_id']);

        return response()->json([
            'results' => $crew->map(fn ($c) => [
                'id'       => $c->id,
                'text'     => $c->name . ($c->nik ? ' — ' . $c->nik : ''),
                'name'     => $c->name,
                'position' => $c->position ?? '',
                'nik'      => $c->nik      ?? '',
                'company'  => $c->company?->name ?? '',
            ]),
        ]);
    }

    // ═══ EXPORT EXCEL ═════════════════════════════════════════════════════════

    public function exportExcel(Request $request)
    {
        $this->authorize('view crew assessment');
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $user  = Auth::user();
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end   = Carbon::parse($request->end_date)->endOfDay();

        $query = CrewAssessment::with(['company', 'vessel', 'crewMember', 'creator'])
            ->whereBetween('assessment_date', [$start, $end]);

        if (! $this->canManage()) {
            $query->where('company_id', $user->company_id);
        } else {
            if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        }
        if ($request->filled('vessel_id'))          $query->where('vessel_id', $request->vessel_id);
        if ($request->filled('mev_type'))            $query->where('mev_type', $request->mev_type);
        if ($request->filled('result'))              $query->where('result', $request->result);
        if ($request->filled('assessment_location')) $query->where('assessment_location', $request->assessment_location);

        $rows = $query->orderBy('assessment_date')->orderBy('created_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet()->setTitle('Assessment');

        $NAVY = '0F2D56'; $ORANGE = 'FF8C00'; $WHITE = 'FFFFFF'; $GRAY = 'F2F2F2';
        $GREEN = '00B050'; $RED = 'FF0000';

        // Title row
        $sheet->mergeCells('A1:T1');
        $sheet->setCellValue('A1', 'DATABASE CREW ASSESSMENT');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Subtitle
        $sheet->mergeCells('A2:T2');
        $sheet->setCellValue('A2',
            'Periode: ' . $start->format('d M Y') . ' s/d ' . $end->format('d M Y')
            . '   |   Total: ' . $rows->count() . ' record   |   Export: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '444444']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Group headers row 3
        $merges = ['A3:A4','B3:B4','C3:C4','D3:D4','E3:E4','F3:G3','H3:H4','I3:K3','L3:L4','M3:M4','N3:P3','Q3:Q4','R3:R4','S3:S4','T3:T4'];
        foreach ($merges as $m) $sheet->mergeCells($m);

        $grpHeaders = ['A3'=>'NO','B3'=>'No. Sertifikat','C3'=>'Nama','D3'=>'NIK','E3'=>'COC','F3'=>'JABATAN','H3'=>'NAMA KAPAL','I3'=>'PENGALAMAN','L3'=>'MEV','M3'=>'Tanggal Assessment','N3'=>'Assessor','Q3'=>'HASIL','R3'=>'KETERANGAN','S3'=>'Perusahaan','T3'=>'Input Oleh'];
        $subHeaders  = ['F4'=>'Diusulkan','G4'=>'Promote/Refresh','I4'=>'PERTAMINA','J4'=>'MASTER','K4'=>'DILUAR','N4'=>'MAR','O4'=>'HSE','P4'=>'FMC'];

        foreach ($grpHeaders as $cell => $val) $sheet->setCellValue($cell, $val);
        foreach ($subHeaders  as $cell => $val) $sheet->setCellValue($cell, $val);

        $hStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '8EA9C1']]],
        ];
        $sheet->getStyle('A3:T4')->applyFromArray($hStyle);

        foreach (['F3:G4', 'I3:K4', 'N3:P4'] as $rng) {
            $sheet->getStyle($rng)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($ORANGE);
        }

        $sheet->getRowDimension(3)->setRowHeight(22);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $resultColors = ['Lulus' => $GREEN, 'Pending' => 'FFC000', 'Tidak Lulus' => $RED];
        $rowNum = 5;

        foreach ($rows as $i => $r) {
            $cm   = $r->crewMember;
            $cols = [
                'A' => $i + 1,
                'B' => $r->certificate_number ?? '',
                'C' => $cm?->name             ?? '—',
                'D' => $cm?->nik              ?? '—',
                'E' => $r->coc ?? $cm?->position ?? '—',
                'F' => $r->position_proposed  ?? '—',
                'G' => $r->position_type      ?? '—',
                'H' => $r->vessel?->name      ?? '—',
                'I' => $r->experience_pertamina ?? '',
                'J' => $r->experience_master    ?? '',
                'K' => $r->experience_outside   ?? '',
                'L' => $r->mev_type             ?? '',
                'M' => $r->assessment_date->format('d.m.Y'),
                'N' => $r->assessor_mar ?? '',
                'O' => $r->assessor_hse ?? '',
                'P' => $r->assessor_fmc ?? '',
                'Q' => $r->result       ?? '',
                'R' => $r->notes        ?? '',
                'S' => $r->company?->name  ?? '—',
                'T' => $r->creator?->name  ?? '—',
            ];

            foreach ($cols as $col => $val) $sheet->setCellValue($col . $rowNum, $val);

            $bg = ($i % 2 === 0) ? $WHITE : $GRAY;
            $sheet->getStyle("A{$rowNum}:T{$rowNum}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D7E0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'font'      => ['size' => 9],
            ]);

            if ($r->result && isset($resultColors[$r->result])) {
                $sheet->getStyle("Q{$rowNum}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE], 'size' => 9],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $resultColors[$r->result]]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            $sheet->getRowDimension($rowNum)->setRowHeight(16);
            $rowNum++;
        }

        // Footer
        $sheet->mergeCells("A{$rowNum}:D{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'Total: ' . $rows->count() . ' record');
        $sheet->getStyle("A{$rowNum}:T{$rowNum}")->applyFromArray([
            'font'    => ['bold' => true, 'size' => 9],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $NAVY]]],
        ]);

        // Column widths
        $widths = [4, 14, 22, 14, 14, 12, 16, 20, 14, 10, 12, 8, 12, 10, 10, 10, 10, 30, 30, 16];
        foreach ($widths as $ci => $w) $sheet->getColumnDimensionByColumn($ci + 1)->setWidth($w);

        $sheet->freezePane('A5');
        $sheet->setAutoFilter('A4:T4');
        $sheet->getStyle("R5:R{$rowNum}")->getAlignment()->setWrapText(true);

        $filename = 'Database_Crew_Assessment_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.xlsx';
        $writer   = new XlsxWriter($spreadsheet);

        if (ob_get_length()) ob_end_clean();

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0',
            ]
        );
    }

    public function import(Request $request)
    {
        $this->authorize('manage crew assessment');

        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        $import = new CrewAssessmentImport();
        
        try {
            Excel::import($import, $request->file('file'));
            
            return redirect()->route('crew-assessment.index')
                ->with('success', "Berhasil mengimpor {$import->importedCount} data. ({$import->skippedCount} baris dilewati)");
        } catch (\Exception $e) {
            return redirect()->route('crew-assessment.index')
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    // ═══ PRIVATE HELPERS ══════════════════════════════════════════════════════

    private function authorizeCompanyAccess(CrewAssessment $model): void
    {
        if ($this->canManage()) return;
        if ((string) $model->company_id !== (string) Auth::user()->company_id) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function deriveStatus(array $data): string
    {
        if (! empty($data['valid_until']) && Carbon::parse($data['valid_until'])->isPast()) {
            return 'expired';
        }
        return 'active';
    }

    /**
     * Shared validation rules for store.
     * Attachment max size: 2 MB = 2048 KB.
     */
    private function rules(): array
    {
        $maxKb = self::MAX_UPLOAD_KB;
        return [
            'company_id'           => 'nullable|exists:companies,id',
            'company_name_text'    => 'nullable|string|max:255',
            'vessel_id'            => 'nullable|exists:vessels,id',
            'vessel_name_text'     => 'nullable|string|max:255',
            'crew_member_id'       => 'required_without:crew_name_text|nullable|exists:crew_members,id',
            'crew_name_text'       => 'nullable|string|max:255',
            'certificate_number'   => 'nullable|string|max:100',
            'coc'                  => 'nullable|string|max:100',
            'position_proposed'    => 'nullable|string|max:100',
            'position_type'        => 'nullable|string|max:100',
            'experience_pertamina' => 'nullable|string|max:100',
            'experience_master'    => 'nullable|string|max:100',
            'experience_outside'   => 'nullable|string|max:100',
            'mev_type'             => 'nullable|string|max:50',
            'assessment_date'      => 'required|date',
            'assessment_location'  => 'nullable|string|max:50',
            'assessor_mar'         => 'nullable|string|max:100',
            'assessor_hse'         => 'nullable|string|max:100',
            'assessor_fmc'         => 'nullable|string|max:100',
            'result'               => 'nullable|string|max:50',
            'notes'                => 'nullable|string|max:2000',
            'valid_from'           => 'nullable|date',
            'valid_until'          => 'nullable|date|after_or_equal:valid_from',
            'attachments'          => 'nullable|array|max:10',
            'attachments.*'        => "file|max:{$maxKb}|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png",
            'attachment_labels'    => 'nullable|array',
            'attachment_labels.*'  => 'nullable|string|max:100',
        ];
    }

    private function rulesForUpdate(): array
    {
        $maxKb = self::MAX_UPLOAD_KB;
        return [
            'company_id'               => 'nullable|exists:companies,id',
            'company_name_text'        => 'nullable|string|max:255',
            'vessel_id'                => 'nullable|exists:vessels,id',
            'vessel_name_text'         => 'nullable|string|max:255',
            'crew_member_id'           => 'required_without:crew_name_text|nullable|exists:crew_members,id',
            'crew_name_text'           => 'nullable|string|max:255',
            'certificate_number'       => 'nullable|string|max:100',
            'coc'                      => 'nullable|string|max:100',
            'position_proposed'        => 'nullable|string|max:100',
            'position_type'            => 'nullable|string|max:100',
            'experience_pertamina'     => 'nullable|string|max:100',
            'experience_master'        => 'nullable|string|max:100',
            'experience_outside'       => 'nullable|string|max:100',
            'mev_type'                 => 'nullable|string|max:50',
            'assessment_date'          => 'required|date',
            'assessment_location'      => 'nullable|string|max:50',
            'assessor_mar'             => 'nullable|string|max:100',
            'assessor_hse'             => 'nullable|string|max:100',
            'assessor_fmc'             => 'nullable|string|max:100',
            'result'                   => 'nullable|string|max:50',
            'notes'                    => 'nullable|string|max:2000',
            'status'                   => 'required|in:active,expired,revoked',
            'valid_from'               => 'nullable|date',
            'valid_until'              => 'nullable|date|after_or_equal:valid_from',
            'new_attachments'          => 'nullable|array|max:10',
            'new_attachments.*'        => "file|max:{$maxKb}|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png",
            'new_attachment_labels'    => 'nullable|array',
            'new_attachment_labels.*'  => 'nullable|string|max:100',
            'delete_attachment_ids'    => 'nullable|array',
            'delete_attachment_ids.*'  => 'exists:crew_assessment_attachments,id',
        ];
    }

    private function handleAttachments(
        Request       $request,
        CrewAssessment $assessment,
        string        $fileKey  = 'attachments',
        string        $labelKey = 'attachment_labels'
    ): void {
        if (! $request->hasFile($fileKey)) return;

        $labels = $request->input($labelKey, []);
        $disk   = config('filesystems.default', 'local');

        foreach ($request->file($fileKey) as $i => $file) {
            $path = $file->store("crew-assessments/{$assessment->id}", $disk);
            $assessment->attachments()->create([
                'uploaded_by'   => Auth::id(),
                'original_name' => $file->getClientOriginalName(),
                'stored_name'   => basename($path),
                'disk'          => $disk,
                'path'          => $path,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'label'         => $labels[$i] ?? null,
            ]);
        }
    }

    private function resolveOrCreateFormEntities(Request $request): void
    {
        // Resolve Company
        $companyInput = $request->input('company_id');
        $companyId = null;
        $companyNameText = null;

        if (!empty($companyInput)) {
            if (\Illuminate\Support\Str::isUuid($companyInput)) {
                $company = Company::find($companyInput);
                if ($company) {
                    $companyId = $company->id;
                }
            } else {
                $company = Company::where('name', $companyInput)->first();
                if ($company) {
                    $companyId = $company->id;
                } else {
                    $companyNameText = $companyInput;
                }
            }
        }
        $request->merge(['company_id' => $companyId, 'company_name_text' => $companyNameText]);

        // Resolve Vessel
        $vesselInput = $request->input('vessel_id');
        $vesselId = null;
        $vesselNameText = null;

        if (!empty($vesselInput)) {
            if (\Illuminate\Support\Str::isUuid($vesselInput)) {
                $vessel = Vessel::find($vesselInput);
                if ($vessel) {
                    $vesselId = $vessel->id;
                }
            } else {
                $vesselQuery = Vessel::where('name', $vesselInput);
                if ($companyId) {
                    $vesselQuery->where('company_id', $companyId);
                }
                $vessel = $vesselQuery->first();
                if ($vessel) {
                    $vesselId = $vessel->id;
                } else {
                    $vesselNameText = $vesselInput;
                }
            }
        }
        $request->merge(['vessel_id' => $vesselId, 'vessel_name_text' => $vesselNameText]);

        // Resolve Crew Member
        $crewInput = $request->input('crew_member_id');
        $crewMemberId = null;
        $crewNameText = null;

        if (!empty($crewInput)) {
            if (\Illuminate\Support\Str::isUuid($crewInput)) {
                $crewMember = CrewMember::find($crewInput);
                if ($crewMember) {
                    $crewMemberId = $crewMember->id;
                }
            } else {
                $crewMember = CrewMember::where('name', $crewInput)->first();
                if ($crewMember) {
                    $crewMemberId = $crewMember->id;
                } else {
                    $crewNameText = $crewInput;
                }
            }
        }
        $request->merge(['crew_member_id' => $crewMemberId, 'crew_name_text' => $crewNameText]);
    }
}
