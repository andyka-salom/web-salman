<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan CeRMAT {{ $report->report_number }}</title>
    <style>
        /* --- PAGE SETUP: A3 LANDSCAPE --- */
        @page {
            size: A3 landscape;
            margin: 10mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* --- TABLE STYLING --- */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* --- UTILITY CLASSES --- */
        .no-border { border: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .text-bold { font-weight: bold; }
        .text-blue { color: #003366; }
        .text-red { color: #cc0000; }
        .text-small { font-size: 8pt; }
        .italic { font-style: italic; }

        /* --- COLORS --- */
        .bg-yellow { background-color: #ffffcc; }
        .bg-orange { background-color: #ffcc99; }
        .bg-green { background-color: #ccffcc; }
        .bg-gray { background-color: #f0f0f0; }

        /* --- LAYOUT --- */
        .main-container { border: 2px solid #000; }
        .col-33 {
            width: 33.33%;
            border-right: 2px solid #000;
            vertical-align: top;
            padding: 0;
        }
        .col-33:last-child { border-right: none; }

        .header-section {
            padding: 4px 6px;
            border-bottom: 1px solid #000;
            font-weight: bold;
            color: #003366;
            font-size: 9pt;
        }

        .label {
            font-weight: bold;
            font-size: 8.5pt;
            display: block;
            margin-bottom: 2px;
            color: #333;
        }

        .text-box {
            min-height: 1.4em;
            text-align: left;
            white-space: pre-line;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .chk-box {
            display: inline-block;
            border: 1px solid #333;
            width: 12px;
            height: 12px;
            line-height: 11px;
            text-align: center;
            font-size: 9pt;
            margin-right: 3px;
            background: #fff;
        }

        .stamp-box {
            border: 1px solid #006400;
            background-color: #fafffa;
            color: #006400;
            padding: 4px;
            margin-top: 5px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 7.5pt;
            text-align: center;
            border-radius: 2px;
            line-height: 1.2;
        }

        .stamp-reporter {
            border: 1px solid #333;
            color: #333;
            background: #f0f0f0;
        }

        /* FIX: Photo frame tanpa background putih tapi tetap dalam border */
        .photo-frame {
            width: 100%;
            min-height: 200px;
            text-align: center;
            padding: 3px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            overflow: hidden;
        }

        /* FIX: Image styling agar tidak keluar dari border */
        .photo-frame img {
            max-width: 98%;
            width: auto;
            height: auto;
            max-height: 170px;
            object-fit: scale-down;
            display: block;
        }

        /* FIX: Action item list responsive */
        .action-list {
            margin: 4px 0;
            padding-left: 15px;
            font-size: 8.5pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .action-list li {
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .risk-table td {
            font-size: 8pt;
            padding: 2px 4px;
            text-align: center;
        }
        .risk-table td:first-child { text-align: left; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;

        // --- HELPER TIMEZONE WITA (FIXED: Tidak melakukan timezone conversion) ---
        function formatWita($date, $format = 'd M Y H:i') {
            if (!$date) return '-';
            $finalFormat = str_contains($format, 'H') ? $format : $format . ' H:i';
            // Langsung format tanpa timezone conversion
            return Carbon::parse($date)->format($finalFormat);
        }

        // Helper Image Base64 (reads uploads from the public disk — local or S3 —
        // and falls back to public/ for static assets such as logo.png)
        function imageToBase64($path) {
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            try {
                if ($path && $disk->exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
                    return 'data:image/' . $type . ';base64,' . base64_encode($disk->get($path));
                }
            } catch (\Throwable $e) {
                // fall through to static-asset lookup
            }

            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
            return null;
        }

        $logoSrc = imageToBase64('logo.png');

        // FIX: Pisahkan foto evidence dan close-out
        $evidenceImages = [];
        $closeoutImages = [];

        // Foto dari report attachments (Evidence - di bagian atas)
        if($report->attachments && $report->attachments->isNotEmpty()){
            foreach($report->attachments as $att){
                $b64 = imageToBase64($att->file_path);
                if($b64) $evidenceImages[] = $b64;
            }
        }

        // FIX: Foto bukti close-out dari action items (Close-out - di bagian bawah)
        if($report->actionItems && $report->actionItems->isNotEmpty()){
            foreach($report->actionItems as $actionItem){
                if($actionItem->photos && $actionItem->photos->isNotEmpty()){
                    foreach($actionItem->photos as $photo){
                        $b64 = imageToBase64($photo->file_path);
                        if($b64) $closeoutImages[] = $b64;
                    }
                }
            }
        }

        // --- HELPER RISIKO ---
        $getRisk = function($type, $category) use ($report) {
            return $report->riskAssessments
                ->where('type', $type)
                ->where('consequence_category', $category)
                ->first();
        };
    @endphp

    <!-- HEADER DOKUMEN -->
    <table class="no-border" style="margin-bottom: 8px;">
        <tr>
            <td class="no-border" width="25%" style="vertical-align: bottom;">
                <span class="text-red italic" style="font-size: 26pt; font-weight: 800;">CeRMAT</span>
            </td>
            <td class="no-border text-center" width="50%" style="vertical-align: bottom;">
                <div style="font-weight: bold; font-size: 14pt; border-bottom: 2px solid #000; display: inline-block; padding: 0 10px 2px 10px;">
                    No: {{ $report->report_number }}
                </div>
            </td>
            <td class="no-border text-right" width="25%" style="vertical-align: bottom;">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" style="height: 45px;" alt="Logo">
                @else
                    <div class="text-blue text-bold" style="font-size: 14pt;">PERTAMINA</div>
                    <div class="text-small">HULU MAHAKAM</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- TABLE LAYOUT UTAMA (3 KOLOM) -->
    <table class="main-container">
        <tr>
            <!-- ================= KOLOM 1: PELAPOR ================= -->
            <td class="col-33">
                <div class="header-section bg-gray">
                    Diisi oleh Pelapor <span class="text-small italic" style="color: #000; font-weight: normal;">(Filled by reporter)</span>
                </div>

                <!-- Uraian -->
                <div style="padding: 6px;">
                    <span class="label">Uraian (Details):</span>
                    <div class="text-box" style="min-height: 80px;">{!! nl2br(e(trim($report->details))) !!}</div>
                </div>

                <!-- Nilai Kerugian -->
                <div style="padding: 6px; border-top: 1px dotted #ccc;">
                    <span class="label">Nilai Kerugian (Value of loss):</span>
                    @if($report->value_of_loss)
                        USD {{ number_format($report->value_of_loss, 2) }}
                    @else
                        <span class="italic text-small" style="color: #666;">To be determined</span>
                    @endif
                </div>

                <!-- Lokasi & Waktu -->
                <table style="border-top: 1px solid #000;">
                    <tr>
                        <td class="no-border" width="50%" style="border-right: 1px dotted #ccc !important;">
                            <span class="label">Lokasi (Site):</span>
                            {{ $report->area->name ?? '-' }}
                        </td>
                        <td class="no-border">
                            <span class="label">Detail (Area)/Nama Kapal:</span>
                            {{ $report->location_details ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="no-border" style="border-top: 1px dotted #ccc !important; border-right: 1px dotted #ccc !important;">
                            <span class="label">Tanggal (Date):</span>
                            {{ $report->report_datetime ? formatWita($report->report_datetime, 'd F Y') : '-' }}
                        </td>
                        <td class="no-border" style="border-top: 1px dotted #ccc !important;">
                            <span class="label">Jam (Time):</span>
                            {{ $report->report_datetime ? formatWita($report->report_datetime, 'H:i') . ' WITA' : '-' }}
                        </td>
                    </tr>
                </table>

                <!-- Tindakan Langsung -->
                <div style="padding: 6px; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                    <span class="label">Tindakan langsung yang dilakukan (Immediate action taken):</span>
                    <div class="text-box" style="min-height: 40px;">{!! nl2br(e(trim($report->immediate_action_taken))) !!}</div>
                </div>

                <!-- STOP Card -->
                <div style="padding: 6px; border-bottom: 1px solid #000; background-color: #f9f9f9;">
                    <span class="text-bold">STOP Job Issued?</span> &nbsp;&nbsp;
                    [ {{ $report->stop_card_issued ? 'X' : ' ' }} ] Ya/Yes &nbsp;&nbsp;&nbsp; [ {{ !$report->stop_card_issued ? 'X' : ' ' }} ] Tidak/No
                </div>

                <!-- Penyebab Langsung -->
                <div style="padding: 6px; border-bottom: 1px solid #000;">
                    <span class="label">Penyebab Langsung (Immediate Causes):</span>
                    <div style="margin-top: 4px;">
                        <span class="text-bold text-small">- Tindakan Tidak Aman (Unsafe Acts):</span>
                        <div style="padding-left: 8px; font-size: 8.5pt;">
                            @forelse($report->unsafeActs as $act) &bull; {{ $act->description }}<br> @empty <span class="italic text-small" style="color:#999">- None</span> @endforelse
                        </div>
                    </div>
                    <div style="margin-top: 6px;">
                        <span class="text-bold text-small">- Kondisi Tidak Aman (Unsafe Conditions):</span>
                        <div style="padding-left: 8px; font-size: 8.5pt;">
                            @forelse($report->unsafeConditions as $cond) &bull; {{ $cond->description }}<br> @empty <span class="italic text-small" style="color:#999">- None</span> @endforelse
                        </div>
                    </div>
                </div>

                <!-- Saran -->
                <div style="padding: 6px;">
                    <span class="label">Saran Pencegahan & Perbaikan (Suggestion):</span>
                    <div class="text-box" style="min-height: 40px;">{!! nl2br(e(trim($report->suggestion_for_improvement))) !!}</div>
                </div>

                <!-- FOOTER PELAPOR -->
                <div class="bg-yellow" style="border-top: 2px solid #000; padding: 6px;">
                    <div class="text-bold" style="border-bottom: 1px dotted #000; margin-bottom: 4px;">Pelapor - Diisi jika diperlukan / Filled if necessary</div>

                    <!-- SIGNATURE PELAPOR -->
                    <table class="no-border">
                        <tr>
                            <td class="no-border" width="55%">Nama (Name): <strong>{{ $report->reporter->name ?? '-' }}</strong></td>
                            <td class="no-border text-center" rowspan="3" style="width: 45%;">
                                Td. Tangan (Signature):
                                @if($report->reporter)
                                    <div class="stamp-box stamp-reporter" style="margin: 0;">
                                        <strong>SUBMITTED</strong><br>
                                        {{ $report->reporter->name }}<br>
                                        <span style="font-size: 6.5pt; color:#333;">{{ $report->reporter->email ?? '' }}</span><br>
                                        {{ formatWita($report->created_at, 'd/M/Y H:i') }} WITA
                                    </div>
                                @else
                                    <div style="margin-top: 20px; border-bottom: 1px dotted #000;"></div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="no-border">Fungsi (Entity): {{ $report->reporter->entityFunction->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="no-border">Email: {{ $report->reporter->email ?? '-' }}</td>
                        </tr>
                    </table>

                    <!-- FOOTER ATASAN PELAPOR -->
                    <div style="margin-top: 6px; border-top: 1px solid #000; padding-top: 4px;">
                        <span class="label">Atasan Pelapor (Line Supervisor):</span>
                        <table class="no-border">
                            <tr>
                                <td class="no-border" width="40%">Fungsi: PHM</td>
                                <td class="no-border text-center">
                                    Td. Tangan (Signature):
                                    @if($report->supervisor_approved_at && $report->lineSupervisor)
                                        <div class="stamp-box">
                                            <strong>APPROVED</strong><br>
                                            {{ $report->lineSupervisor->name }}<br>
                                            <span style="font-size: 6.5pt; color:#333;">{{ $report->lineSupervisor->email }}</span><br>
                                            {{ formatWita($report->supervisor_approved_at, 'd/M/Y H:i') }} WITA
                                        </div>
                                    @else
                                        <div style="margin-top: 20px; border-bottom: 1px dotted #000;"></div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>

            <!-- ================= KOLOM 2: HSSE & RSES ================= -->
            <td class="col-33">
                <div class="header-section bg-gray">
                    Diisi oleh Otoritas K3LL dan RSES
                    <div class="text-small italic" style="color: #000; font-weight: normal; display: inline;">(HSSE Authority & RSES)</div>
                </div>

                <!-- Reported Event -->
                <div class="bg-orange text-bold" style="padding: 4px 6px; border-bottom: 1px solid #000; font-size: 9pt;">
                    Peristiwa yang dilaporkan (Reported Event):
                </div>
                <div style="padding: 4px; border-bottom: 1px solid #000;">
                    <table class="no-border">
                        <tr>
                            <td class="no-border" width="50%">
                                <span class="chk-box">{{ $report->event_type == 'hse' ? 'X' : '' }}</span> HSE Event<br>
                                <span class="chk-box">{{ $report->event_type == 'security' ? 'X' : '' }}</span> Security Event
                            </td>
                            <td class="no-border">
                                Sec. Category:<br>
                                {{ $report->securityEventCategory->name ?? '-' }}
                            </td>
                            <td class="no-border" style="background: #000; color: #fff; font-weight: bold; text-align: center; font-size: 8pt; vertical-align: middle;">
                                {{ $report->is_limited_circulation ? 'LIMITED CIRCULATION' : 'GENERAL CIRCULATION' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="no-border" colspan="3" style="padding-top: 5px;">
                                P-horse: <strong>{{ $report->synergi_register_no ?? '__________' }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="padding: 4px; border-bottom: 1px solid #000; background: #f9f9f9; font-size: 8.5pt;">
                    <span class="chk-box">{{ $report->classification == 'positive_aspect' ? 'X' : '' }}</span> Positive Aspect &nbsp;
                    <span class="chk-box">{{ $report->classification == 'unsafe_condition' ? 'X' : '' }}</span> Unsafe condition &nbsp;
                    <span class="chk-box">{{ $report->classification == 'unsafe_action' ? 'X' : '' }}</span> Unsafe Action &nbsp;
                    <span class="chk-box">{{ $report->classification == 'near_miss' ? 'X' : '' }}</span> Near Miss &nbsp;
                    <span class="chk-box">{{ $report->classification == 'incident' ? 'X' : '' }}</span> Incident
                </div>

                <!-- INITIAL RISK MATRIX -->
                <div class="bg-orange text-bold" style="padding: 4px 6px; border-bottom: 1px solid #000; font-size: 9pt;">
                    Potensi Risiko Awal (Initial Potential Risk)
                </div>
                <table class="risk-table">
                    <tr class="bg-yellow text-bold">
                        <td width="30%">Konsekuensi</td>
                        <td width="20%">Real Severity</td>
                        <td width="20%">Pot. Severity</td>
                        <td width="30%">Likelihood</td>
                    </tr>
                    @foreach(['people' => 'Manusia', 'environment' => 'Lingkungan', 'reputation' => 'Reputasi', 'asset' => 'Aset', 'public' => 'Publik'] as $key => $label)
                        @php $r = $getRisk('initial', $key); @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $r->real_severity ?? '-' }}</td>
                            <td>{{ $r->potential_severity ?? '-' }}</td>
                            <td>{{ $r->likelihood ?? '-' }}</td>
                        </tr>
                    @endforeach
                </table>

                <div style="padding: 6px; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                    <span class="text-bold text-small">Pelanggaran / Infringement of CLSR:</span><br>
                    <span style="display:block; margin-bottom: 3px;">{{ $report->pelanggaran->name ?? '-' }}</span>
                </div>

                <!-- COST TO BUSINESS -->
                <div style="padding: 6px; border-bottom: 1px solid #000;">
                    <span class="text-bold text-small">Cost to Business (USD):</span>
                    Actual: <strong>{{ $report->cost_to_business_actual ? number_format($report->cost_to_business_actual, 2) : '_______' }}</strong>
                    &nbsp;&nbsp;
                    Pot.: <strong>{{ $report->cost_to_business_potential ? number_format($report->cost_to_business_potential, 2) : '_______' }}</strong>
                </div>

                <!-- NOTIFICATION -->
                <div style="padding: 6px; border-bottom: 1px solid #000;">
                    <span class="text-bold text-small">Immediate notification to Line Management?</span>
                    &nbsp; [ {{ $report->notified_line_management ? 'X' : ' ' }} ] Yes [ {{ !$report->notified_line_management ? 'X' : ' ' }} ] No
                </div>

                <div style="padding: 6px; border-bottom: 1px solid #000; min-height: 40px;">
                    <span class="label">Tindakan Perbaikan Jangka Pendek (Short Term Mitigation):</span>
                    <div class="text-small text-box">{!! nl2br(e(trim($report->short_term_mitigation))) !!}</div>
                </div>

                <!-- RESIDUAL RISK MATRIX -->
                <div class="bg-orange text-bold" style="padding: 4px 6px; border-bottom: 1px solid #000; font-size: 9pt;">
                    Risiko Sisa (Residual Risk)
                </div>
                <table class="risk-table" style="border-bottom: 1px solid #000;">
                    <tr class="bg-yellow text-bold">
                        <td width="50%">Konsekuensi</td>
                        <td width="25%">Severity</td>
                        <td width="25%">Likelihood</td>
                    </tr>
                    @foreach(['people' => 'Manusia', 'environment' => 'Lingkungan', 'reputation' => 'Reputasi', 'asset' => 'Aset'] as $key => $label)
                        @php $r = $getRisk('residual', $key); @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $r->potential_severity ?? '-' }}</td>
                            <td>{{ $r->likelihood ?? '-' }}</td>
                        </tr>
                    @endforeach
                </table>

                <div style="padding: 6px; border-bottom: 1px solid #000;">
                    <table class="no-border">
                        <tr>
                            <td class="no-border" width="20%">Date:</td>
                            <td class="no-border">
                                Signature & Name (HSSE):
                                @if($report->hsse_reviewed_at && $report->hsseOfficer)
                                    <div class="stamp-box" style="margin: 0; padding: 2px;">
                                        <strong>REVIEWED</strong><br>
                                        {{ $report->hsseOfficer->name }}<br>
                                        <span style="font-size: 6.5pt; color:#333;">{{ $report->hsseOfficer->email }}</span><br>
                                        {{ formatWita($report->hsse_reviewed_at, 'd/M/Y H:i') }} WITA
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- FIX: RSES recommendations dengan word-wrap -->
                <div class="bg-green text-bold" style="padding: 4px 6px; border-bottom: 1px solid #000; font-size: 9pt;">
                    RSES recommendations/actions:
                </div>
                <div style="padding: 6px; min-height: 100px;">
                    <u class="text-small italic">* Action Description - Responsible (By) - Target Date :</u>
                    <ul class="action-list">
                        @forelse($report->actionItems as $item)
                            <li>
                                {{ $item->description }} -
                                <b>{{ $item->responsible->name ?? 'PIC' }}</b> -
                                {{ $item->target_date ? Carbon::parse($item->target_date)->format('d/m/y') : '-' }}
                            </li>
                        @empty
                            <li>-</li><li>-</li>
                        @endforelse
                    </ul>
                </div>
            </td>

            <!-- ================= KOLOM 3: FOTO & CLOSING ================= -->
            <td class="col-33">
                <div class="header-section no-border">
                    Foto / Sketsa jika ada <span class="text-small italic" style="font-weight: normal;">(Photo or sketch, if any)</span>
                </div>

                <!-- FIX: Kontainer Foto Evidence (Bagian Atas) - dengan padding untuk jaga border -->
                <div style="padding: 6px; min-height: 280px; border-bottom: 1px solid #000; overflow: hidden;">
                    <div class="photo-frame" style="min-height: 260px;">
                        @if(count($evidenceImages) > 0)
                            @foreach($evidenceImages as $img)
                                <img src="{{ $img }}" alt="Evidence Photo">
                            @endforeach
                        @else
                            <span style="color:#aaa; display:block; padding-top:120px; font-size: 8pt;">No Evidence Photo</span>
                        @endif
                    </div>
                </div>

                <!-- Close-out Acknowledgement dengan Foto Close-out -->
                <div style="border-top: 1px solid #000; padding: 6px;">
                    <div class="text-bold" style="background: #f0f0f0; padding: 4px 6px; margin: -6px -6px 6px -6px; border-bottom: 1px solid #000;">
                        Pengakuan Penutupan / Close-out Acknowledgement
                    </div>

                    <div class="text-small italic" style="margin-bottom: 4px;">Deskripsi Tindakan yang Dilaksanakan (Action Implemented):</div>
                    <div class="text-box" style="background: #fdfdfd; border: 1px dotted #ccc; padding: 4px; min-height: 50px;">
                        @if($report->status === 'closed' || $report->status === 'no_action_required')
                            {!! nl2br(e(trim($report->close_out_description))) !!}
                        @else
                            <span class="italic text-small" style="color: #999;">- Not yet closed -</span>
                        @endif
                    </div>

                    <!-- FIX: Foto Close-out di bagian Close-out - dengan overflow control -->
                    @if(count($closeoutImages) > 0)
                        <div style="margin-top: 8px; border-top: 1px dotted #ccc; padding-top: 6px; overflow: hidden;">
                            <div class="text-small text-bold" style="margin-bottom: 4px;">Foto Bukti Close-out:</div>
                            <div class="photo-frame" style="min-height: 120px; max-height: 160px;">
                                @foreach($closeoutImages as $img)
                                    <img src="{{ $img }}" alt="Close-out Proof Photo" style="max-height: 120px; max-width: 98%;">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div style="margin-top: 8px; border-top: 1px solid #000; padding-top: 6px;">
                        <span class="label">Pelaksana (Performer):</span>
                        <table class="no-border">
                            <tr>
                                <td class="no-border" width="50%">
                                    Name: {{ $report->closeOutPerformer->name ?? '-' }}
                                </td>
                                <td class="no-border text-center">
                                    @if($report->close_out_at && $report->closeOutPerformer)
                                        <div class="stamp-box" style="margin:0; border-color: #000; color: #000; background: #fff;">
                                            <strong>CLOSED</strong><br>
                                            {{ $report->closeOutPerformer->name ?? '-' }}<br>
                                            <span style="font-size: 6pt;">{{ $report->closeOutPerformer->email }}</span><br>
                                            {{ formatWita($report->close_out_at, 'd/M/Y H:i') }} WITA
                                        </div>
                                    @else
                                        Sign: ___________
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

            </td>
        </tr>
    </table>

    <table class="no-border" style="margin-top: 4px;">
        <tr>
            <td class="bg-orange" style="width: 50%; border: 1px solid #000; padding: 4px;">
                CeRMATi sekeliling anda untuk meningkatkan Keselamatan & Keamanan
            </td>
            <td class="bg-green" style="width: 50%; border: 1px solid #000; padding: 4px; font-style: italic;">
                Do CeRMAT in your surrounding to improve Safety & Security
            </td>
        </tr>
    </table>
</body>
</html>
