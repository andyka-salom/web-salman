@extends('layouts.app')

@section('title', 'Review Laporan #' . $report->report_number)

@push('styles')
{{-- Plugins --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet"/>

<style>
    :root {
        --primary-brand: #4361ee;
        --primary-light: #eef2ff;
        --secondary-bg: #f8f9fa;
        --border-color: #e0e6ed;
        --text-muted: #6c757d;
        --card-radius: 12px;
    }

    body { background-color: #f1f5f9; }

    /* --- General Cards & Layout --- */
    .card-modern {
        border: 1px solid white;
        background: white;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    .form-section-header {
        display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;
        border-bottom: 1px dashed var(--border-color); padding-bottom: 1rem;
    }
    .step-circle {
        width: 32px; height: 32px; background: var(--primary-brand); color: white;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-weight: bold; flex-shrink: 0;
    }

    /* --- Form Elements --- */
    .form-label-fancy {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
        font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;
    }
    .radio-tile-group { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .radio-tile-input { display: none; }
    .radio-tile-label {
        padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 50px;
        cursor: pointer; transition: all 0.2s; font-size: 0.85rem; font-weight: 500;
        color: #444; background: white;
    }
    .radio-tile-input:checked + .radio-tile-label {
        background-color: var(--primary-brand); color: white;
        border-color: var(--primary-brand); box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
    }

    /* --- Risk Matrix Table --- */
    .risk-table th { font-weight: 600; font-size: 0.8rem; background: var(--secondary-bg); text-align: center; vertical-align: middle; }
    .risk-table td { vertical-align: middle; }
    .risk-input { text-align: center; }

    /* --- Timeline Modern --- */
    .timeline-modern { position: relative; padding-left: 2.5rem; }
    .timeline-modern::before { content: ''; position: absolute; left: 16px; top: 5px; bottom: 5px; width: 3px; background: #e0e6ed; border-radius: 2px; }
    .timeline-modern-item { position: relative; margin-bottom: 1.5rem; z-index: 1; }
    .timeline-modern-item:last-child { margin-bottom: 0; }
    .timeline-modern-marker {
        position: absolute; left: -40px; top: 0; width: 36px; height: 36px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10;
        background: #fff; border: 3px solid #e0e6ed; color: #6c757d; font-size: 14px;
    }
    .timeline-modern-content {
        background-color: #fff; border-radius: 0.5rem; padding: 1.25rem;
        border: 1px solid #e0e6ed; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .timeline-modern-content:hover { transform: translateX(3px); border-color: var(--primary-brand); }

    /* Status Colors */
    .status-completed .timeline-modern-marker { border-color: #198754; color: #198754; background: #e7f6ed; }
    .status-cant-do .timeline-modern-marker { border-color: #dc3545; color: #dc3545; background: #fbecec; }
    .status-in-progress .timeline-modern-marker { border-color: #0dcaf0; color: #0dcaf0; background: #e3fafa; }
    .status-do .timeline-modern-marker { border-color: var(--primary-brand); color: var(--primary-brand); background: #f0f4ff; }

    /* --- Styling Modal Proporsional --- */
    .modal-content { border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    .modal-header { padding: 1.5rem; border-bottom: 1px solid #f1f5f9; background-color: #fff; border-radius: 12px 12px 0 0; }
    .modal-body { padding: 1.5rem; background-color: #fff; }
    .modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid #f1f5f9; background-color: #f8f9fa; border-radius: 0 0 12px 12px; }

    /* File Upload Modern Fix */
    .file-upload-wrapper {
        position: relative;
        border: 2px dashed #ced4da;
        border-radius: .75rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: 0.2s;
        background-color: #fcfcfc;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 160px;
    }
    .file-upload-wrapper:hover { border-color: var(--primary-brand); background-color: var(--primary-light); }
    .file-upload-wrapper input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }
    .file-upload-content i { font-size: 2.5rem; color: #adb5bd; margin-bottom: 0.5rem; }

    /* Draft Area */
    .draft-area { background: white; border-radius: var(--card-radius); padding: 1.5rem; border: 1px solid var(--border-color); height: 100%; position: relative; }
    .draft-item {
        background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-brand);
        border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem;
        position: relative; overflow: visible !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .draft-delete { position: absolute; top: 10px; right: 10px; color: #ef4444; cursor: pointer; opacity: 0.6; transition: 0.2s; }
    .draft-delete:hover { opacity: 1; transform: scale(1.1); }
    .empty-state { border: 2px dashed var(--border-color); border-radius: 12px; padding: 3rem; text-align: center; background: #fcfcfc; transition: 0.2s; }
    .empty-state:hover { border-color: var(--primary-brand); background: var(--primary-light); }

    /* Misc */
    .btn-copy { background: var(--primary-light); color: var(--primary-brand); border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .btn-copy:hover { background: var(--primary-brand); color: white; }
    .ts-dropdown { z-index: 10055 !important; }
    #submitContainer { position: sticky; bottom: 20px; z-index: 10050; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); }

    /* Preview Images */
    .file-preview-container { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem; }
    .preview-image-wrapper { position: relative; width: 80px; height: 80px; }
    .preview-image { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #e0e6ed; }
    .remove-preview-btn { position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; background: #dc3545; color: white; border-radius: 50%; font-size: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
    @php
        use App\Models\ActionItem;
        use Illuminate\Support\Str;

        // 1. Siapkan data kategori untuk JavaScript mapping
        $jsActionCategories = $actionCategories->map(function($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'range' => $cat->duration_range, // e.g., <7, 8-15, 16-30, >30
                'code' => $cat->code,
            ];
        })->sortBy('priority')->values();

        $actionItems = $report->actionItems;
    @endphp

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h6 class="text-uppercase text-muted mb-1 fw-bold ls-1">Formulir Review</h6>
                <h2 class="fw-bold text-dark m-0">Laporan <span class="text-primary">#{{ $report->report_number }}</span></h2>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 p-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-octagon-fill fs-4 me-2 text-danger"></i>
                    <strong class="text-danger">Terdapat kesalahan input:</strong>
                </div>
                <ul class="mb-0 small text-danger ps-4">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3 p-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row g-4">
            <!-- ============================================== -->
            <!-- SEKSI 1: FORM REVIEW (Klasifikasi & Risk)      -->
            <!-- ============================================== -->
            <div class="col-12">
                <form action="{{ route('cermat.reports.submitReview', $report) }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        <!-- Kolom Kiri: Form Input -->
                        <div class="col-lg-8">
                            <!-- Bagian 1: Klasifikasi & Kategori -->
                            <div class="card-modern p-4">
                                <div class="form-section-header">
                                    <div class="step-circle">1</div>
                                    <h5 class="fw-bold m-0">Klasifikasi & Kategori</h5>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label-fancy">Klasifikasi Laporan <span class="text-danger">*</span></label>
                                    <div class="radio-tile-group">
                                        @foreach(['Positive Aspect', 'Unsafe condition', 'Unsafe Action', 'Near Miss', 'Incident'] as $class)
                                            @php $val = Str::snake($class); @endphp
                                            <input type="radio" class="radio-tile-input" name="classification"
                                                   id="class_{{ Str::slug($class) }}"
                                                   value="{{ $val }}"
                                                   {{ old('classification', $report->classification) == $val ? 'checked' : '' }} required>
                                            <label class="radio-tile-label" for="class_{{ Str::slug($class) }}">{{ $class }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-fancy">Jenis Event <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="event_type" value="hse" id="eventTypeHse" {{ old('event_type', $report->event_type ?? 'hse') == 'hse' ? 'checked' : '' }} onchange="toggleSecurityCategory()">
                                                <label class="form-check-label" for="eventTypeHse">HSE</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="event_type" value="security" id="eventTypeSecurity" {{ old('event_type', $report->event_type) == 'security' ? 'checked' : '' }} onchange="toggleSecurityCategory()">
                                                <label class="form-check-label" for="eventTypeSecurity">Security</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3" id="securityCategoryContainer" style="display: none;">
                                        <label class="form-label-fancy">Kategori Security</label>
                                        <select name="security_event_category_id" class="form-select">
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach($securityEventCategories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ old('security_event_category_id', $report->security_event_category_id) == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Bagian 2: Potensi Risiko Awal -->
                            <div class="card-modern p-4">
                                <div class="form-section-header justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="step-circle">2</div>
                                        <h5 class="fw-bold m-0">Potensi Risiko Awal (Initial)</h5>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#riskMatrixModal">
                                        <i class="bi bi-table me-1"></i>Panduan Matriks
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 risk-table">
                                        <thead>
                                            <tr>
                                                <th class="text-start" style="width: 20%">Konsekuensi</th>
                                                <th>Severity Aktual</th>
                                                <th>Severity Potensial</th>
                                                <th>Likelihood</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $consequences = ['people' => 'People', 'environment' => 'Environment', 'reputation' => 'Reputation', 'asset' => 'Asset'];
                                            @endphp
                                            @foreach ($consequences as $key => $label)
                                                @php
                                                    $existingRisk = $report->riskAssessments
                                                        ->where('type', 'initial')
                                                        ->where('consequence_category', $key)
                                                        ->first();
                                                @endphp
                                                <tr>
                                                    <td class="fw-bold text-dark ps-3">{{ $label }}</td>
                                                    <td>
                                                        <input type="number" name="initial_risk[{{ $key }}][real_severity]"
                                                               class="form-control form-control-sm risk-input" min="0" max="5"
                                                               value="{{ old('initial_risk.'.$key.'.real_severity', $existingRisk->real_severity ?? '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="initial_risk[{{ $key }}][potential_severity]"
                                                               class="form-control form-control-sm risk-input" min="0" max="5"
                                                               value="{{ old('initial_risk.'.$key.'.potential_severity', $existingRisk->potential_severity ?? '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="initial_risk[{{ $key }}][likelihood]"
                                                               class="form-control form-control-sm risk-input" min="0" max="5"
                                                               value="{{ old('initial_risk.'.$key.'.likelihood', $existingRisk->likelihood ?? '') }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Bagian 3: Mitigasi & Detail -->
                            <div class="card-modern p-4">
                                <div class="form-section-header">
                                    <div class="step-circle">3</div>
                                    <h5 class="fw-bold m-0">Mitigasi & Detail Tambahan</h5>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-fancy">Mitigasi Jangka Pendek</label>
                                    <textarea name="short_term_mitigation" class="form-control" rows="3" placeholder="Jelaskan tindakan langsung... (Opsional)">{{ old('short_term_mitigation', $report->short_term_mitigation) }}</textarea>
                                    <small class="text-muted">Field ini bersifat opsional</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-fancy">Pelanggaran CLSR</label>
                                        <select name="pelanggaran_id" class="form-select">
                                            <option value="">-- Tidak Ada --</option>
                                            @foreach($pelanggaranList as $item)
                                                <option value="{{ $item->id }}" {{ old('pelanggaran_id', $report->pelanggaran_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-fancy">P-horse.</label>
                                        <input type="text" name="synergi_register_no" class="form-control" value="{{ old('synergi_register_no', $report->synergi_register_no) }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-fancy">Cost to Business (USD)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Actual</span>
                                            <input type="number" name="cost_to_business_actual" class="form-control" step="0.01" value="{{ old('cost_to_business_actual', $report->cost_to_business_actual) }}">
                                            <span class="input-group-text">Potential</span>
                                            <input type="number" name="cost_to_business_potential" class="form-control" step="0.01" value="{{ old('cost_to_business_potential', $report->cost_to_business_potential) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bagian 4: Risiko Sisa -->
                            <div class="card-modern p-4">
                                <div class="form-section-header">
                                    <div class="step-circle">4</div>
                                    <h5 class="fw-bold m-0">Risiko Sisa (Residual)</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 risk-table">
                                        <thead>
                                            <tr>
                                                <th class="text-start" style="width: 20%">Konsekuensi</th>
                                                <th>Severity Potensial</th>
                                                <th>Likelihood</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($consequences as $key => $label)
                                                @php
                                                    $existingResidual = $report->riskAssessments
                                                        ->where('type', 'residual')
                                                        ->where('consequence_category', $key)
                                                        ->first();
                                                @endphp
                                                <tr>
                                                    <td class="fw-bold text-dark ps-3">{{ $label }}</td>
                                                    <td>
                                                        <input type="number" name="residual_risk[{{ $key }}][potential_severity]"
                                                               class="form-control form-control-sm risk-input" min="0" max="5"
                                                               value="{{ old('residual_risk.'.$key.'.potential_severity', $existingResidual->potential_severity ?? '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="residual_risk[{{ $key }}][likelihood]"
                                                               class="form-control form-control-sm risk-input" min="0" max="5"
                                                               value="{{ old('residual_risk.'.$key.'.likelihood', $existingResidual->likelihood ?? '') }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Sidebar & Submit -->
                        <div class="col-lg-4">
                            <div class="position-sticky" style="top: 1rem; z-index: 10;">
                                <!-- Card Submit -->
                                <div class="card-modern p-4 text-center bg-white border-primary">
                                    <h5 class="fw-bold mb-3">Simpan Review</h5>
                                    <p class="text-muted small mb-4">Pastikan seluruh data wajib (*) telah terisi dengan benar sebelum menyimpan.</p>
                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-2 shadow-sm">
                                        <i class="bi bi-save-fill me-2"></i>Simpan Review
                                    </button>
                                    <a href="{{ route('cermat.reports.show', $report) }}" class="btn btn-link text-muted small text-decoration-none">Batal & Kembali</a>
                                </div>

                                <!-- Card Referensi -->
                                <div class="card-modern">
                                    <div class="p-3 bg-light border-bottom rounded-top">
                                        <h6 class="fw-bold m-0 text-dark"><i class="bi bi-info-circle me-2"></i>Info Laporan</h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="mb-3">
                                            <span class="text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700;">Pelapor</span>
                                            <p class="mb-0 fw-medium">{{ $report->reporter->name }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700;">Waktu Kejadian</span>
                                            <p class="mb-0 fw-medium">{{ $report->report_datetime->format('d M Y, H:i') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700;">Uraian Singkat</span>
                                            <p class="mb-0 small text-muted bg-light p-2 rounded">{{ Str::limit($report->details, 200) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- SEKSI 2: ACTION ITEMS (Tindakan & Drafting)    -->
            <!-- ============================================== -->
            <div class="col-12 mt-2">
                <hr class="border-secondary-subtle mb-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-weight: bold;"><i class="bi bi-list-check"></i></div>
                    <h4 class="fw-bold m-0">Tindakan Perbaikan (Action Items)</h4>
                </div>

                <div class="row g-4 h-10">
                    <!-- Kiri: Timeline History & Management -->
                    <div class="col-lg-5">
                        <div class="card-modern p-4 h-100 bg-white">
                            <h6 class="fw-bold mb-4 text-muted text-uppercase ls-1"><i class="bi bi-clock-history me-2"></i>Daftar Tindakan (Kelola & Copy)</h6>

                            @if($actionItems->count() > 0)
                                <div class="timeline-modern">
                                    @foreach($actionItems as $item)
                                        <div class="timeline-modern-item status-{{ Str::slug($item->status) }}">
                                            <!-- Marker Status -->
                                            <div class="timeline-modern-marker">
                                                @if($item->status == \App\Models\ActionItem::STATUS_COMPLETED) <i class="bi bi-check-lg"></i>
                                                @elseif($item->status == \App\Models\ActionItem::STATUS_CANT_DO) <i class="bi bi-x"></i>
                                                @elseif($item->status == \App\Models\ActionItem::STATUS_IN_PROGRESS) <i class="bi bi-arrow-repeat"></i>
                                                @else <i class="bi bi-clipboard"></i>
                                                @endif
                                            </div>

                                            <div class="timeline-modern-content">
                                                <!-- Header -->
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    {{-- MODIFIKASI: Menampilkan Kategori Tindakan lebih jelas --}}
                                                    <span class="badge bg-light text-dark border py-2 px-3 fw-bold shadow-sm">
                                                        {{ $item->actionCategory->name ?? 'General' }} ({{ $item->actionCategory->code ?? 'GEN' }})
                                                    </span>

                                                    <div class="d-flex gap-1">
                                                        <!-- Tombol Copy -->
                                                        <button type="button" class="btn-copy btn-copy-action"
                                                                title="Duplikat ke Draft"
                                                                data-desc="{{ $item->description }}"
                                                                data-pic-id="{{ $item->responsible_id }}"
                                                                data-date="{{ $item->target_date ? $item->target_date->format('Y-m-d') : '' }}">
                                                            <i class="bi bi-files me-1"></i>Copy
                                                        </button>

                                                        <!-- Dropdown Menu -->
                                                        @if(!in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]))
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                                                    <i class="bi bi-three-dots-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                    @if($item->status === \App\Models\ActionItem::STATUS_DO)
                                                                    <li>
                                                                        <form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST">
                                                                            @csrf @method('PATCH')
                                                                            <input type="hidden" name="status" value="{{ \App\Models\ActionItem::STATUS_IN_PROGRESS }}">
                                                                            <button type="submit" class="dropdown-item fw-medium"><i class="bi bi-play-circle-fill me-2 text-info"></i>Mulai Kerjakan</button>
                                                                        </form>
                                                                    </li>
                                                                    @endif
                                                                    <li><a class="dropdown-item text-success fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#actionStatusModal-{{ $item->id }}-completed"><i class="bi bi-check-circle-fill me-2"></i>Tandai Selesai</a></li>
                                                                    <li><a class="dropdown-item text-danger fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#actionStatusModal-{{ $item->id }}-cantdo"><i class="bi bi-x-circle-fill me-2"></i>Tidak Dikerjakan</a></li>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item text-primary fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#extendTimeModal-{{ $item->id }}"><i class="bi bi-calendar-plus-fill me-2"></i>Perpanjang Waktu</a></li>
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Body -->
                                                <p class="mb-2 text-dark fw-medium small lh-sm">{{ Str::limit($item->description, 100) }}</p>

                                                <!-- Footer -->
                                                <div class="d-flex align-items-center gap-2 small text-muted border-top pt-2 mt-2">
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:20px; height:20px; font-size:10px;">{{ substr($item->responsible->name ?? 'A', 0, 1) }}</div>
                                                    <span class="text-truncate" style="max-width: 100px;">{{ $item->responsible->name ?? '-' }}</span>
                                                    @if($item->target_date)
                                                        <span class="ms-auto {{ $item->target_date->isPast() && $item->status != 'completed' ? 'text-danger fw-bold' : '' }}">
                                                            {{ $item->target_date->format('d M y') }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Extra Info -->
                                                @if(in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]) && $item->completion_notes)
                                                    <div class="completion-notes mt-2">
                                                        <strong class="d-block text-uppercase" style="font-size: 0.7rem;">Catatan:</strong>
                                                        "{{ $item->completion_notes }}"
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- === MODAL: SELESAIKAN TINDAKAN (FIXED SIZE) === --}}
                                        <div class="modal fade" id="actionStatusModal-{{ $item->id }}-completed" tabindex="-1" aria-hidden="true">
                                            {{-- Dihilangkan modal-lg untuk ukuran standar, dan dipastikan centered --}}
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Models\ActionItem::STATUS_COMPLETED }}">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Selesaikan Tindakan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-4">
                                                                <label class="form-label fw-bold text-dark">Catatan Penyelesaian <span class="text-danger">*</span></label>
                                                                <textarea class="form-control bg-light" name="completion_notes" rows="4" placeholder="Jelaskan detail tindakan yang dilakukan..." required></textarea>
                                                            </div>
                                                            <div>
                                                                <label class="form-label fw-bold text-dark">Bukti Foto</label>
                                                                <div class="file-upload-wrapper" id="upload-wrapper-{{ $item->id }}">
                                                                    <input type="file" name="proof_photos[]" class="proof-photo-input" accept="image/*" multiple data-item-id="{{ $item->id }}">
                                                                    <div class="file-upload-content d-flex flex-column align-items-center">
                                                                        <i class="bi bi-cloud-arrow-up text-primary mb-2"></i>
                                                                        <span class="fw-bold text-dark">Klik atau Geser foto ke sini</span>
                                                                        <small class="text-muted mt-1">(Maksimal 5 foto)</small>
                                                                    </div>
                                                                </div>
                                                                <div class="file-preview-container" id="preview-{{ $item->id }}"></div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success fw-bold px-4">Simpan Selesai</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- === MODAL: TIDAK BISA (CANT DO) === --}}
                                        <div class="modal fade" id="actionStatusModal-{{ $item->id }}-cantdo" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Models\ActionItem::STATUS_CANT_DO }}">
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h5 class="modal-title fw-bold">Batalkan Tindakan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <div class="modal-body">
                                                            <label class="form-label fw-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="completion_notes" rows="3" placeholder="Mengapa tindakan ini tidak dapat dilakukan?" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                                            <button type="submit" class="btn btn-danger fw-bold">Konfirmasi</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- === MODAL: EXTEND TIME === --}}
                                        <div class="modal fade" id="extendTimeModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form action="{{ route('cermat.action-items.extend', $item) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h5 class="modal-title fw-bold">Perpanjang Waktu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Target Baru <span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" name="new_target_date" min="{{ now()->toDateString() }}" required>
                                                            </div>
                                                            <div>
                                                                <label class="form-label fw-bold">Alasan Perpanjangan <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" name="extend_reason" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary fw-bold">Ajukan</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted opacity-50">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="small mt-2">Belum ada tindakan tersimpan.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kanan: Drafting Canvas -->
                    <div class="col-lg-7">
                        <div class="draft-area d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold m-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Susun Tindakan Baru</h5>
                                    <p class="text-muted small m-0">Tambah tindakan baru atau copy dari kiri.</p>
                                </div>
                                <button type="button" class="btn btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm" id="btnAddEmpty">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                                </button>
                            </div>

                            <form action="{{ route('cermat.reports.bulkAddActionItems', $report) }}" method="POST" id="bulkActionForm" class="flex-grow-1 d-flex flex-column">
                                @csrf
                                <!-- Container Draft Items -->
                                <div id="draftContainer" class="flex-grow-1" style="padding-bottom: 2rem;">
                                    <!-- Empty State -->
                                    <div id="draftEmptyState" class="empty-state h-100 d-flex flex-column align-items-center justify-content-center cursor-pointer" onclick="document.getElementById('btnAddEmpty').click()">
                                        <div class="bg-white p-3 rounded-circle shadow-sm mb-3">
                                            <i class="bi bi-basket3 fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Area Draft Kosong</h6>
                                        <p class="text-muted small mb-0">Klik tombol "Tambah" atau copy item untuk memulai.</p>
                                    </div>
                                </div>

                                <!-- Sticky Bottom Submit Bar -->
                                <div id="submitContainer" class="d-none mt-3 p-3 bg-dark text-white rounded-3 shadow d-flex justify-content-between align-items-center animate__animated animate__fadeInUp">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 30px; height: 30px;" id="draftCountBadge">0</span>
                                        <span class="small opacity-75">Tindakan siap disimpan</span>
                                    </div>
                                    <button type="submit" class="btn btn-success fw-bold px-4">
                                        Simpan Tindakan <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Data Sources for JS -->
    <div class="d-none">
        <select id="master-pic">
            <option value="">Pilih PIC...</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- MODAL PANDUAN 5x5 RISK MATRIX --}}
    <div class="modal fade animated fadeInDown" id="riskMatrixModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="riskMatrixModalLabel">Panduan 5x5 Risk Matrix</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered risk-matrix-table m-0">
                            <thead class="text-center align-middle">
                                <tr><th colspan="12" class="header-main fs-6 py-2">5 x 5 RISK MATRIX</th></tr>
                                <tr><th colspan="7" class="header-sub">HAZARD EFFECT (SEVERITY)</th><th colspan="5" class="header-sub">PROBABILITY (LIKELIHOOD)</th></tr>
                                <tr><th>People</th><th>Environment</th><th>Financial Impact</th><th>Reputation Impact & Legal</th><th>Asset & Equipment</th><th>Public Notification</th><th>Level</th><th>1<br><small>0% < x < 20%</small></th><th>2<br><small>2% < x < 40%</small></th><th>3<br><small>40% < x < 60%</small></th><th>4<br><small>60% < x < 80%</small></th><th>5<br><small>80% < x < 100%</small></th></tr>
                            </thead>
                            <tbody class="align-middle">
                                <!-- Isi matrix -->
                                <tr>
                                    <td class="desc-cell">Multiple Fatalities</td>
                                    <td class="desc-cell">Oil Spill > 100 barrel</td>
                                    <td class="desc-cell">≥80% BTR</td>
                                    <td class="desc-cell">Intl Coverage</td>
                                    <td class="desc-cell">> $5M</td>
                                    <td class="desc-cell">Complete Evac</td>
                                    <td class="text-center bg-light"><b>5</b></td>
                                    <td class="risk-level-medium">5</td><td class="risk-level-high">10</td><td class="risk-level-extreme">15</td><td class="risk-level-extreme">20</td><td class="risk-level-extreme">25</td>
                                </tr>
                                <tr>
                                    <td class="desc-cell">Single Fatality / LTI</td>
                                    <td class="desc-cell">15 – 100 barrel</td>
                                    <td class="desc-cell">60% - 80% BTR</td>
                                    <td class="desc-cell">Regional Impact</td>
                                    <td class="desc-cell">$1M - $5M</td>
                                    <td class="desc-cell">Selected Evac</td>
                                    <td class="text-center bg-light"><b>4</b></td>
                                    <td class="risk-level-low">4</td><td class="risk-level-medium">8</td><td class="risk-level-high">12</td><td class="risk-level-extreme">16</td><td class="risk-level-extreme">20</td>
                                </tr>
                                <tr>
                                    <td class="desc-cell">RWC / Non Perm Disab</td>
                                    <td class="desc-cell">5-15 bbls</td>
                                    <td class="desc-cell">40% - 60% BTR</td>
                                    <td class="desc-cell">Local Impact</td>
                                    <td class="desc-cell">$100k - $1M</td>
                                    <td class="desc-cell">Shelter in place</td>
                                    <td class="text-center bg-light"><b>3</b></td>
                                    <td class="risk-level-very-low">3</td><td class="risk-level-medium">6</td><td class="risk-level-medium">9</td><td class="risk-level-high">12</td><td class="risk-level-extreme">15</td>
                                </tr>
                                <tr>
                                    <td class="desc-cell">MTC</td>
                                    <td class="desc-cell">1-5 bbls</td>
                                    <td class="desc-cell">20% - 40% BTR</td>
                                    <td class="desc-cell">Internal Impact</td>
                                    <td class="desc-cell">$10k - $100k</td>
                                    <td class="desc-cell">Local Notice</td>
                                    <td class="text-center bg-light"><b>2</b></td>
                                    <td class="risk-level-very-low">2</td><td class="risk-level-low">4</td><td class="risk-level-medium">6</td><td class="risk-level-medium">8</td><td class="risk-level-high">10</td>
                                </tr>
                                <tr>
                                    <td class="desc-cell">FAC</td>
                                    <td class="desc-cell">< 1 bbls</td>
                                    <td class="desc-cell">≤20% BTR</td>
                                    <td class="desc-cell">No Rep Impact</td>
                                    <td class="desc-cell">< $10k</td>
                                    <td class="desc-cell">None</td>
                                    <td class="text-center bg-light"><b>1</b></td>
                                    <td class="risk-level-very-low">1</td><td class="risk-level-very-low">2</td><td class="risk-level-low">3</td><td class="risk-level-low">4</td><td class="risk-level-medium">5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light text-dark" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data Kategori dari PHP yang diubah menjadi JS
        const actionCategoryMap = @json($jsActionCategories);

        // --- 1. Security Category Toggle ---
        window.toggleSecurityCategory = function() {
            const isSecurity = document.getElementById('eventTypeSecurity')?.checked;
            const secContainer = document.getElementById('securityCategoryContainer');
            if(secContainer) {
                if(isSecurity) {
                    secContainer.style.display = 'block';
                    secContainer.classList.add('animate__animated', 'animate__fadeIn');
                } else {
                    secContainer.style.display = 'none';
                }
            }
        }
        toggleSecurityCategory();

        // --- 2. Action Items Draft Logic ---
        const draftContainer = document.getElementById('draftContainer');
        const emptyState = document.getElementById('draftEmptyState');
        const submitContainer = document.getElementById('submitContainer');
        const countBadge = document.getElementById('draftCountBadge');
        let draftIndex = 0;
        let draftCount = 0;

        /**
         * Menentukan kategori tindakan berdasarkan selisih hari dari target date.
         */
        window.determineCategory = function(dateString, index) {
            const displayElement = document.getElementById(`categoryDisplay-${index}`);
            const hiddenInput = document.getElementById(`cat-hidden-${index}`);

            // Reset tampilan
            displayElement.className = 'form-control bg-light text-primary fw-bold';
            displayElement.textContent = '-- Pilih Tanggal Target --';
            hiddenInput.value = '';

            if (!dateString) {
                return;
            }

            // Normalisasi tanggal hari ini (tanpa jam)
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Normalisasi tanggal target
            const target = new Date(dateString);
            target.setHours(0, 0, 0, 0);

            // Cek jika tanggal target di masa lalu
            if (target < today) {
                displayElement.textContent = 'Target di Masa Lalu';
                displayElement.className = 'form-control bg-danger text-white fw-bold';
                hiddenInput.value = ''; // Pastikan tidak ada ID kategori terkirim
                return;
            }

            // Hitung selisih hari (di mana Hari Ini = 0 hari ke depan)
            const msInDay = 1000 * 60 * 60 * 24;
            let diffDays = Math.round((target.getTime() - today.getTime()) / msInDay);

            let selectedCategory = null;

            for (const cat of actionCategoryMap) {
                const range = cat.range;
                let match = false;

                // Logika Penentuan Range:
                if (range === '<7' && diffDays >= 0 && diffDays < 7) {
                    // Immediate Action (0 - 6 hari dari hari ini)
                    match = true;
                } else if (range === '8-15' && diffDays >= 7 && diffDays <= 14) {
                    // Short Term Action (7 - 14 hari dari hari ini)
                    match = true;
                } else if (range === '16-30' && diffDays >= 15 && diffDays <= 29) {
                    // Medium Term Action (15 - 29 hari dari hari ini)
                    match = true;
                } else if (range === '>30' && diffDays >= 30) {
                    // Long Term Action (30 hari atau lebih)
                    match = true;
                }

                if (match) {
                    selectedCategory = cat;
                    break;
                }
            }

            if (selectedCategory) {
                displayElement.textContent = `${selectedCategory.name} (${selectedCategory.code})`;
                displayElement.classList.add('bg-success', 'text-white');
                displayElement.classList.remove('bg-light', 'text-primary');
                hiddenInput.value = selectedCategory.id;
            } else {
                displayElement.textContent = `Range Target (${diffDays} hari) Tidak Dikenal`;
                displayElement.classList.add('bg-warning', 'text-dark');
                hiddenInput.value = '';
            }
        };

        window.addDraftItem = function(data = null) {
            if (draftCount === 0) {
                emptyState?.classList.add('d-none');
                emptyState?.classList.remove('d-flex');
                submitContainer?.classList.remove('d-none');
            }

            const index = draftIndex++;
            draftCount++;
            const picOptions = document.getElementById('master-pic')?.innerHTML || '';
            const initialDate = data ? data.date : '';

            const cardHtml = `
                <div class="draft-item animate__animated animate__fadeInUp" id="draft-row-${index}">
                    <i class="bi bi-x-circle-fill draft-delete fs-5" onclick="removeDraft(${index})" title="Hapus"></i>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-fancy text-primary mb-1">Deskripsi Tindakan #${draftCount}</label>
                            <textarea name="actions[${index}][description]" class="form-control" rows="2" placeholder="Apa yang harus dilakukan?" required>${data ? data.desc : ''}</textarea>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label-fancy mb-1">Kategori</label>
                            <div class="form-control bg-light text-primary fw-bold" id="categoryDisplay-${index}">
                                -- Pilih Tanggal Target --
                            </div>
                            <input type="hidden" name="actions[${index}][action_category_id]" id="cat-hidden-${index}" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-fancy mb-1">Target <span class="text-danger">*</span></label>
                            <input type="date" name="actions[${index}][target_date]" id="date-${index}" class="form-control"
                                required value="${initialDate}"
                                onchange="determineCategory(this.value, ${index})">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-fancy mb-1">PIC <span class="text-danger">*</span></label>
                            <select name="actions[${index}][responsible_id]" class="form-select ts-pic" id="pic-${index}" required>
                                ${picOptions}
                            </select>
                        </div>
                    </div>
                </div>
            `;

            draftContainer.insertAdjacentHTML('beforeend', cardHtml);

            // Hilangkan animasi
            setTimeout(() => {
                const row = document.getElementById(`draft-row-${index}`);
                if(row) row.classList.remove('animate__animated', 'animate__fadeInUp');
            }, 800);

            // Init TomSelect for PIC
            new TomSelect(`#pic-${index}`, { create: false, maxItems: 1, placeholder: "Cari Nama...", plugins: ['dropdown_input'], dropdownParent: 'body' });

            if (data) {
                const picSelect = document.getElementById(`pic-${index}`).tomselect;
                if(data.picId) picSelect.setValue(data.picId);

                // Immediately determine category if a date is copied
                if (data.date) {
                    determineCategory(data.date, index);
                }
            }
            updateCountDisplay();
        };

        window.removeDraft = function(index) {
            const row = document.getElementById(`draft-row-${index}`);
            if(row) {
                row.classList.remove('animate__fadeInUp');
                row.classList.add('animate__fadeOutRight');
                setTimeout(() => {
                    row.remove();
                    draftCount--;
                    updateCountDisplay();
                    if (draftCount === 0) {
                        emptyState?.classList.remove('d-none');
                        emptyState?.classList.add('d-flex');
                        submitContainer?.classList.add('d-none');
                    }
                }, 300);
            }
        };

        function updateCountDisplay() { if(countBadge) countBadge.textContent = draftCount; }

        document.getElementById('btnAddEmpty')?.addEventListener('click', () => addDraftItem());
        document.querySelectorAll('.btn-copy-action').forEach(btn => {
            btn.addEventListener('click', function() {
                addDraftItem({
                    desc: this.dataset.desc,
                    picId: this.dataset.picId,
                    date: this.dataset.date
                });
                // Scroll ke item baru
                setTimeout(() => {
                    if(draftContainer.lastElementChild) {
                        draftContainer.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 100);
            });
        });

        // --- 3. File Upload & Preview (Modal Selesai) ---
        const dataTransferObjects = {};

        document.querySelectorAll('.proof-photo-input').forEach(input => {
            input.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                const previewContainer = document.getElementById(`preview-${itemId}`);
                if (!dataTransferObjects[itemId]) dataTransferObjects[itemId] = new DataTransfer();

                Array.from(this.files).forEach(file => {
                    if (dataTransferObjects[itemId].items.length < 5) dataTransferObjects[itemId].items.add(file);
                });
                this.files = dataTransferObjects[itemId].files;
                renderPreviews(itemId, previewContainer);
            });
        });

        function renderPreviews(itemId, container) {
            container.innerHTML = '';
            const dt = dataTransferObjects[itemId];
            if(dt) {
                Array.from(dt.files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'preview-image-wrapper animate__animated animate__fadeIn';
                        wrapper.innerHTML = `<img src="${e.target.result}" class="preview-image"><span class="remove-preview-btn" onclick="removeFile(${itemId}, ${index})"><i class="bi bi-x"></i></span>`;
                        container.appendChild(wrapper);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        window.removeFile = function(itemId, index) {
            const input = document.querySelector(`.proof-photo-input[data-item-id="${itemId}"]`);
            const container = document.getElementById(`preview-${itemId}`);
            if(dataTransferObjects[itemId]) {
                dataTransferObjects[itemId].items.remove(index);
                input.files = dataTransferObjects[itemId].files;
                renderPreviews(itemId, container);
            }
        };
    });
</script>
@endpush
