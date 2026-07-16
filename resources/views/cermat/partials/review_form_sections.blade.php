{{-- resources/views/cermat/partials/review_form_sections.blade.php --}}

@php
    $oldInitialRisk = $report->riskAssessments->where('type', 'initial')->keyBy('consequence_category');
    $oldResidualRisk = $report->riskAssessments->where('type', 'residual')->keyBy('consequence_category');
@endphp

<div class="row g-lg-5 g-4 mb-5">
    {{-- Kolom Kiri - Form Review --}}
    <div class="col-lg-8">
        <div class="d-flex flex-column gap-4">

            <!-- Bagian 1: Klasifikasi & Kategori -->
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <strong>1</strong>
                        </div>
                        <h5 class="mb-0 fw-bold">Klasifikasi & Kategori</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-semibold">Klasifikasi Laporan <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach(['Positive Aspect', 'Anomaly', 'Near Miss', 'Incident'] as $class)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="classification"
                                               value="{{ $class }}" id="class_{{ Str::slug($class) }}"
                                               {{ old('classification', $report->classification) == $class ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="class_{{ Str::slug($class) }}">{{ $class }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">Jenis Event <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="event_type" value="hse"
                                           id="eventTypeHse" {{ old('event_type', $report->event_type ?? 'hse') == 'hse' ? 'checked' : '' }}
                                           onchange="toggleSecurityCategory()" required>
                                    <label class="form-check-label" for="eventTypeHse">HSE</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="event_type" value="security"
                                           id="eventTypeSecurity" {{ old('event_type', $report->event_type) == 'security' ? 'checked' : '' }}
                                           onchange="toggleSecurityCategory()" required>
                                    <label class="form-check-label" for="eventTypeSecurity">Security</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="securityCategoryContainer" style="display: none;">
                        <label class="form-label fw-semibold" for="security_event_category_id">
                            Kategori Insiden Security <span class="text-danger">*</span>
                        </label>
                        <select id="security_event_category_id" name="security_event_category_id"
                                class="form-select @error('security_event_category_id') is-invalid @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($securityEventCategories as $category)
                                <option value="{{ $category->id }}"
                                        {{ old('security_event_category_id', $report->security_event_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('security_event_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Potensi Risiko Awal -->
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <strong>2</strong>
                            </div>
                            <h5 class="mb-0 fw-bold">Potensi Risiko Awal (Initial)</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#riskMatrixModal">
                            <i class="bi bi-question-circle me-1"></i> Panduan Matriks
                        </button>
                    </div>

                    <p class="text-muted small mb-3">Isi nilai dari 0 hingga 5 berdasarkan panduan matriks risiko.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th class="text-start">Konsekuensi</th>
                                    <th>Severity Aktual</th>
                                    <th>Severity Potensial</th>
                                    <th>Likelihood</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $consequences = [
                                        'people' => 'People',
                                        'environment' => 'Environment',
                                        'reputation' => 'Reputation',
                                        'asset' => 'Asset'
                                    ];
                                @endphp
                                @foreach ($consequences as $key => $label)
                                    @php
                                        $initial = old('initial_risk.'.$key, $oldInitialRisk->get($key));
                                    @endphp
                                    <tr>
                                        <td class="align-middle text-start ps-3">{{ $label }}</td>
                                        <td>
                                            <input type="number" name="initial_risk[{{ $key }}][real_severity]"
                                                   class="form-control form-control-sm text-center"
                                                   min="0" max="5"
                                                   value="{{ is_array($initial) ? ($initial['real_severity'] ?? '') : ($initial->real_severity ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" name="initial_risk[{{ $key }}][potential_severity]"
                                                   class="form-control form-control-sm text-center"
                                                   min="0" max="5"
                                                   value="{{ is_array($initial) ? ($initial['potential_severity'] ?? '') : ($initial->potential_severity ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" name="initial_risk[{{ $key }}][likelihood]"
                                                   class="form-control form-control-sm text-center"
                                                   min="0" max="5"
                                                   value="{{ is_array($initial) ? ($initial['likelihood'] ?? '') : ($initial->likelihood ?? '') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Mitigasi & Detail Tambahan -->
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <strong>3</strong>
                        </div>
                        <h5 class="mb-0 fw-bold">Mitigasi & Detail Tambahan</h5>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="short_term_mitigation">
                            Tindakan Mitigasi Jangka Pendek <span class="text-danger">*</span>
                        </label>
                        <textarea id="short_term_mitigation" name="short_term_mitigation"
                                  class="form-control @error('short_term_mitigation') is-invalid @enderror"
                                  rows="4" required
                                  placeholder="Jelaskan tindakan perbaikan yang perlu segera dilakukan untuk mengontrol risiko...">{{ old('short_term_mitigation', $report->short_term_mitigation) }}</textarea>
                        @error('short_term_mitigation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pelanggaran_id" class="form-label fw-semibold">Pelanggaran CLSR (Jika Ada)</label>
                            <select name="pelanggaran_id" id="pelanggaran_id" class="form-select">
                                <option value="">-- Tidak Ada Pelanggaran --</option>
                                @foreach($pelanggaranList as $item)
                                    <option value="{{ $item->id }}"
                                            {{ old('pelanggaran_id', $report->pelanggaran_id) == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="synergi_register_no" class="form-label fw-semibold">Synergi Register No.</label>
                            <input type="text" id="synergi_register_no" name="synergi_register_no"
                                   class="form-control"
                                   value="{{ old('synergi_register_no', $report->synergi_register_no) }}"
                                   placeholder="Contoh: I-12345">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                Cost to Business (USD) <small class="text-muted">(Opsional)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Actual:</span>
                                <input type="number" name="cost_to_business_actual" class="form-control"
                                       placeholder="0.00" step="0.01"
                                       value="{{ old('cost_to_business_actual', $report->cost_to_business_actual) }}">
                                <span class="input-group-text">Potential:</span>
                                <input type="number" name="cost_to_business_potential" class="form-control"
                                       placeholder="0.00" step="0.01"
                                       value="{{ old('cost_to_business_potential', $report->cost_to_business_potential) }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Sirkulasi Terbatas? <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_limited_circulation"
                                           value="1" id="limitedYes"
                                           {{ old('is_limited_circulation', $report->is_limited_circulation) == '1' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="limitedYes">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_limited_circulation"
                                           value="0" id="limitedNo"
                                           {{ old('is_limited_circulation', $report->is_limited_circulation ?? '0') == '0' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="limitedNo">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Notifikasi ke Manajemen? <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="notified_line_management"
                                       value="1" id="notifYes"
                                       {{ old('notified_line_management', $report->notified_line_management) == '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="notifYes">Ya</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="notified_line_management"
                                       value="0" id="notifNo"
                                       {{ old('notified_line_management', $report->notified_line_management ?? '0') == '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="notifNo">Tidak</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian 4: Risiko Sisa -->
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <strong>4</strong>
                        </div>
                        <h5 class="mb-0 fw-bold">Risiko Sisa (Residual)</h5>
                    </div>

                    <p class="text-muted small mb-3">Isi nilai dari 0 hingga 5 setelah tindakan mitigasi diterapkan.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th class="text-start">Konsekuensi</th>
                                    <th>Severity Potensial</th>
                                    <th>Likelihood</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($consequences as $key => $label)
                                    @php
                                        $residual = old('residual_risk.'.$key, $oldResidualRisk->get($key));
                                    @endphp
                                    <tr>
                                        <td class="align-middle text-start ps-3">{{ $label }}</td>
                                        <td>
                                            <input type="number" name="residual_risk[{{ $key }}][potential_severity]"
                                                   class="form-control form-control-sm text-center"
                                                   min="0" max="5"
                                                   value="{{ is_array($residual) ? ($residual['potential_severity'] ?? '') : ($residual->potential_severity ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" name="residual_risk[{{ $key }}][likelihood]"
                                                   class="form-control form-control-sm text-center"
                                                   min="0" max="5"
                                                   value="{{ is_array($residual) ? ($residual['likelihood'] ?? '') : ($residual->likelihood ?? '') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Catatan HSSE -->
            <div class="card">
                <div class="card-body p-4">
                    <label class="form-label fw-semibold" for="hsse_notes">
                        Catatan & Analisis HSSE (Opsional)
                    </label>
                    <textarea id="hsse_notes" name="hsse_notes"
                              class="form-control @error('hsse_notes') is-invalid @enderror"
                              rows="4"
                              placeholder="Analisis lebih lanjut atau komentar tambahan dari HSSE...">{{ old('hsse_notes', $report->hsse_notes) }}</textarea>
                    @error('hsse_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan - Referensi & Aksi Review --}}
    <div class="col-lg-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- Card Aksi Review -->
            <div class="card mb-4">
                <div class="card-body p-4 text-center">
                    <h4 class="fw-bold">Simpan Review</h4>
                    <p class="text-muted small">Simpan data klasifikasi dan risiko yang telah Anda isi.</p>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save-fill me-2"></i> Simpan Review
                        </button>
                        <a href="{{ route('cermat.reports.show', $report) }}" class="btn btn-link text-secondary mt-2">
                            Batal
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card Referensi -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-file-text me-2"></i>Referensi Laporan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="d-block text-muted small mb-1">Pelapor</span>
                        <p class="mb-0 fw-semibold">{{ $report->reporter->name }}</p>
                    </div>
                    <div class="mb-3">
                        <span class="d-block text-muted small mb-1">Tanggal Kejadian</span>
                        <p class="mb-0 fw-semibold">{{ $report->report_datetime->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <span class="d-block text-muted small mb-1">Uraian Singkat</span>
                        <p class="mb-0">{{ Str::limit($report->details, 150) }}</p>
                    </div>
                    <div>
                        <span class="d-block text-muted small mb-1">Tindakan Langsung</span>
                        <p class="mb-0">{{ Str::limit($report->immediate_action_taken, 150) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSecurityCategory() {
        const securityRadio = document.getElementById('eventTypeSecurity');
        const container = document.getElementById('securityCategoryContainer');
        if (securityRadio && container) {
            container.style.display = securityRadio.checked ? 'block' : 'none';
        }
    }

    // Run on page load
    toggleSecurityCategory();
</script>
@endpush
