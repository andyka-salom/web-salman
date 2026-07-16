@extends('layouts.app')

@section('title', 'Broadcast ke Grup Kontak')

@push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/src/filepond/filepond.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/src/filepond/FilePondPluginImagePreview.min.css') }}">
    <link href="{{ asset('plugins/css/light/filepond/custom-filepond.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/css/dark/filepond/custom-filepond.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .checkbox-list-scroll {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--card-border-color);
            border-radius: 8px;
            padding: 8px;
        }
        .checkbox-item {
            padding: 8px 10px;
            border-bottom: 1px dashed var(--card-border-color);
            transition: background 0.15s;
            border-radius: 6px;
        }
        .checkbox-item:last-child { border-bottom: none; }
        .checkbox-item:hover { background: rgba(113,106,202,0.04); }
        .checkbox-item.disabled-item { opacity: 0.45; }
        .form-check-label { cursor: pointer; width: 100%; }
        .target-option-card {
            border: 1.5px solid var(--card-border-color);
            border-radius: 10px;
            padding: 14px 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            text-align: center;
            display: block;
        }
        .target-option-card:hover {
            border-color: #716aca;
            background: rgba(113,106,202,0.04);
        }
        .target-option-card.selected {
            border-color: #716aca;
            background: rgba(113,106,202,0.07);
            box-shadow: 0 2px 8px rgba(113,106,202,0.15);
        }
        .target-icon {
            width: 36px;
            height: 36px;
            margin-bottom: 8px;
            color: #716aca;
        }
        .tab-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 9px;
            padding: 0 5px;
            margin-left: 5px;
            background: rgba(113,106,202,0.15);
            color: #716aca;
        }
        .selected-count-bar {
            background: rgba(113,106,202,0.07);
            border: 1px solid rgba(113,106,202,0.15);
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            color: #716aca;
            font-weight: 600;
            display: none;
            margin-bottom: 12px;
        }
        .wa-text { color: #25D366; font-size: 12px; }
        .search-input-sm {
            border-radius: 7px;
            font-size: 13px;
            padding: 7px 12px;
            margin-bottom: 8px;
        }
        .select-all-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px 8px;
            border-bottom: 1px solid var(--card-border-color);
            margin-bottom: 4px;
        }
        .select-all-row label { font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; }
    </style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing mb-4">
            <div class="col-12">
                <h3 class="m-0 fw-bold">Broadcast ke Kontak Tersimpan</h3>
                <p class="text-muted mb-0">Kirim pesan ke User, Crew, Contacts, atau Grup yang sudah terdaftar.</p>
            </div>
        </div>

        <form action="{{ route('broadcast.send.group-contact') }}" method="POST" enctype="multipart/form-data" id="broadcastForm">
            @csrf

            <div class="row">
                {{-- ── Kolom Kiri: Target ── --}}
                <div class="col-xl-5 col-lg-5 col-md-12 mb-4">
                    <div class="widget-content widget-content-area br-8 p-4 h-100">
                        <h5 class="mb-4 fw-bold border-bottom pb-2" style="color:#716aca;">
                            1. Pilih Target Penerima
                        </h5>

                        {{-- Radio Cards --}}
                        <div class="row g-2 mb-4">
                            <div class="col-3">
                                <label class="target-option-card {{ old('target_selection','group') == 'group' ? 'selected' : '' }}" for="select_group">
                                    <input class="form-check-input d-none" type="radio" name="target_selection" id="select_group" value="group"
                                           {{ old('target_selection','group') == 'group' ? 'checked' : '' }} onchange="toggleTarget('group')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="target-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    <span class="d-block fw-bold" style="font-size:11px;">Grup</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <label class="target-option-card {{ old('target_selection') == 'manual' ? 'selected' : '' }}" for="select_manual">
                                    <input class="form-check-input d-none" type="radio" name="target_selection" id="select_manual" value="manual"
                                           {{ old('target_selection') == 'manual' ? 'checked' : '' }} onchange="toggleTarget('manual')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="target-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <span class="d-block fw-bold" style="font-size:11px;">Manual</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <label class="target-option-card {{ old('target_selection') == 'all' ? 'selected' : '' }}" for="select_all">
                                    <input class="form-check-input d-none" type="radio" name="target_selection" id="select_all" value="all"
                                           {{ old('target_selection') == 'all' ? 'checked' : '' }} onchange="toggleTarget('all')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="target-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                    <span class="d-block fw-bold" style="font-size:11px;">Semua</span>
                                </label>
                            </div>
                        </div>
                        @error('target_selection') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                        {{-- ── Group Container ── --}}
                        <div id="target_group_container" style="display:none;">
                            <label for="group_id" class="form-label fw-bold" style="font-size:13px;">
                                Pilih Grup <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                <option value="">-- Pilih Grup --</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->group_name }} ({{ $group->member_count ?? 0 }} anggota)
                                </option>
                                @endforeach
                            </select>
                            @error('group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted d-block mt-1" style="font-size:11px;">
                                Semua member grup (User, Crew, Contact) yang memiliki nomor akan dikirimi pesan.
                            </small>
                        </div>

                        {{-- ── Manual Container ── --}}
                        <div id="target_manual_container" style="display:none;">
                            {{-- Selected Count Bar --}}
                            <div class="selected-count-bar" id="selectedCountBar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span id="selectedCountText">0 penerima dipilih</span>
                            </div>

                            <ul class="nav nav-tabs mb-3" id="contactTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#user-content" type="button" role="tab">
                                        Users
                                        <span class="tab-count-badge">{{ $users->count() }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#crew-content" type="button" role="tab">
                                        Crew
                                        <span class="tab-count-badge">{{ $crewMembers->count() }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contact-content" type="button" role="tab">
                                        Contacts
                                        <span class="tab-count-badge" style="background:rgba(37,211,102,0.15);color:#25D366;">{{ $contacts->count() }}</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="contactTabContent">

                                {{-- ── User Tab ── --}}
                                <div class="tab-pane fade show active" id="user-content" role="tabpanel">
                                    <input type="text" class="form-control search-input-sm" id="searchUser" placeholder="Cari nama user...">
                                    <div class="select-all-row">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input select-all-check" type="checkbox" id="selectAllUsers" data-group="user">
                                            <label class="form-check-label" for="selectAllUsers">Pilih Semua User</label>
                                        </div>
                                        <span class="tab-count-badge" id="userCheckedCount">0</span>
                                    </div>
                                    <div class="checkbox-list-scroll">
                                        @forelse($users as $user)
                                        <div class="form-check checkbox-item user-item" data-search="{{ strtolower($user->name . ' ' . $user->phone) }}">
                                            <input class="form-check-input manual-checkbox user-checkbox" type="checkbox"
                                                   name="user_ids[]" value="{{ $user->id }}" id="user_{{ $user->id }}"
                                                   {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="user_{{ $user->id }}">
                                                <span class="fw-semibold d-block" style="font-size:13px;">{{ $user->name }}</span>
                                                <small class="text-muted">{{ $user->phone }}</small>
                                            </label>
                                        </div>
                                        @empty
                                        <p class="text-center text-muted py-3 mb-0 small">Tidak ada data user.</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- ── Crew Tab ── --}}
                                <div class="tab-pane fade" id="crew-content" role="tabpanel">
                                    <input type="text" class="form-control search-input-sm" id="searchCrew" placeholder="Cari nama crew...">
                                    <div class="select-all-row">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input select-all-check" type="checkbox" id="selectAllCrew" data-group="crew">
                                            <label class="form-check-label" for="selectAllCrew">Pilih Semua Crew</label>
                                        </div>
                                        <span class="tab-count-badge" id="crewCheckedCount">0</span>
                                    </div>
                                    <div class="checkbox-list-scroll">
                                        @forelse($crewMembers as $crew)
                                        <div class="form-check checkbox-item crew-item" data-search="{{ strtolower($crew->name . ' ' . $crew->phone) }}">
                                            <input class="form-check-input manual-checkbox crew-checkbox" type="checkbox"
                                                   name="crew_member_ids[]" value="{{ $crew->id }}" id="crew_{{ $crew->id }}"
                                                   {{ in_array($crew->id, old('crew_member_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="crew_{{ $crew->id }}">
                                                <span class="fw-semibold d-block" style="font-size:13px;">{{ $crew->name }}</span>
                                                <small class="text-muted">{{ $crew->phone }} · {{ $crew->position ?? 'N/A' }}</small>
                                            </label>
                                        </div>
                                        @empty
                                        <p class="text-center text-muted py-3 mb-0 small">Tidak ada data crew.</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- ── Contact Tab (NEW) ── --}}
                                <div class="tab-pane fade" id="contact-content" role="tabpanel">
                                    <input type="text" class="form-control search-input-sm" id="searchContact" placeholder="Cari nama, nomor WA, atau jabatan...">
                                    <div class="select-all-row">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input select-all-check" type="checkbox" id="selectAllContacts" data-group="contact">
                                            <label class="form-check-label" for="selectAllContacts">Pilih Semua Contacts</label>
                                        </div>
                                        <span class="tab-count-badge" style="background:rgba(37,211,102,0.15);color:#25D366;" id="contactCheckedCount">0</span>
                                    </div>
                                    <div class="checkbox-list-scroll">
                                        @forelse($contacts as $contact)
                                        <div class="form-check checkbox-item contact-item"
                                             data-search="{{ strtolower($contact->name . ' ' . $contact->whatsapp_number . ' ' . ($contact->position ?? '')) }}">
                                            <input class="form-check-input manual-checkbox contact-checkbox" type="checkbox"
                                                   name="contact_ids[]" value="{{ $contact->id }}" id="contact_{{ $contact->id }}"
                                                   {{ in_array($contact->id, old('contact_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="contact_{{ $contact->id }}">
                                                <span class="fw-semibold d-block" style="font-size:13px;">{{ $contact->name }}</span>
                                                <span class="wa-text d-flex align-items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326z"/></svg>
                                                    {{ $contact->whatsapp_number }}
                                                    @if($contact->position) <small class="text-muted ms-1">· {{ $contact->position }}</small> @endif
                                                </span>
                                            </label>
                                        </div>
                                        @empty
                                        <p class="text-center text-muted py-3 mb-0 small">Tidak ada kontak tersedia.</p>
                                        @endforelse
                                    </div>
                                </div>

                            </div>{{-- /.tab-content --}}

                            @error('user_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('crew_member_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('contact_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- ── All Container ── --}}
                        <div id="target_all_container" style="display:none;">
                            <div class="alert alert-light-primary border-primary d-flex align-items-start gap-2" role="alert" style="font-size:13px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-1 flex-shrink-0"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <div>
                                    Pesan akan dikirim ke <strong>SEMUA</strong> User, Crew Member, dan Contact aktif yang memiliki nomor WhatsApp/telepon valid.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Kolom Kanan: Pesan ── --}}
                <div class="col-xl-7 col-lg-7 col-md-12 mb-4">
                    <div class="widget-content widget-content-area br-8 p-4 h-100">
                        <h5 class="mb-4 fw-bold border-bottom pb-2" style="color:#716aca;">
                            2. Tulis Pesan Broadcast
                        </h5>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold" style="font-size:13px;">
                                Judul <small class="text-muted fw-normal">(Opsional)</small>
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="Contoh: Info Kegiatan Agustus 2025">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label fw-bold" style="font-size:13px;">
                                Isi Pesan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" name="message" rows="8"
                                      placeholder="Tulis pesan Anda di sini..." required>{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold" style="font-size:13px;">
                                Lampiran Media <small class="text-muted fw-normal">(Opsional)</small>
                            </label>
                            <input type="file" class="filepond" name="media_file" data-max-file-size="10MB">
                            @error('media_file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="scheduled_at" class="form-label fw-bold" style="font-size:13px;">
                                Jadwalkan <small class="text-muted fw-normal">(Opsional)</small>
                            </label>
                            <input type="datetime-local"
                                   class="form-control @error('scheduled_at') is-invalid @enderror"
                                   id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                            @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('broadcast.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="submitBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send me-2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                <span id="btnText">Kirim Broadcast</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginFileValidateType.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImageExifOrientation.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImagePreview.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/filepondPluginFileValidateSize.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── FilePond ──────────────────────────────────────────────
    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginImageExifOrientation, FilePondPluginFileValidateSize, FilePondPluginFileValidateType);
    FilePond.create(document.querySelector('.filepond'), {
        storeAsFile: true,
        allowMultiple: false,
        acceptedFileTypes: ['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'video/*'],
        labelIdle: 'Drag & Drop file atau <span class="filepond--label-action">Pilih File</span>',
    });

    // ── Init target ───────────────────────────────────────────
    const initial = document.querySelector('input[name="target_selection"]:checked')?.value || 'group';
    toggleTarget(initial);

    // ── Search setup ──────────────────────────────────────────
    setupSearch('searchUser',    '.user-item');
    setupSearch('searchCrew',    '.crew-item');
    setupSearch('searchContact', '.contact-item');

    // ── Select All checkboxes ────────────────────────────────
    document.querySelectorAll('.select-all-check').forEach(function (sa) {
        sa.addEventListener('change', function () {
            const group   = this.dataset.group;
            const checked = this.checked;
            document.querySelectorAll(`.${group}-checkbox:not(:disabled):not([style*="display: none"])`)
                    .forEach(function (cb) {
                        const item = cb.closest('.checkbox-item');
                        if (!item || item.style.display !== 'none') cb.checked = checked;
                    });
            updateSelectedCount();
            updateTabCounts();
        });
    });

    // ── Individual checkbox change ────────────────────────────
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('manual-checkbox')) {
            updateSelectedCount();
            updateTabCounts();
            syncSelectAll(e.target);
        }
    });

    // ── Loading state on submit ───────────────────────────────
    document.getElementById('broadcastForm').addEventListener('submit', function () {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('btnText').textContent = 'Memproses...';
    });
});

// ── Toggle Target ──────────────────────────────────────────────
function toggleTarget(selection) {
    const containers = {
        group:  document.getElementById('target_group_container'),
        manual: document.getElementById('target_manual_container'),
        all:    document.getElementById('target_all_container'),
    };

    const groupId      = document.getElementById('group_id');
    const manualInputs = document.querySelectorAll('.manual-checkbox');

    // hide all, disable all inputs
    Object.values(containers).forEach(c => c.style.display = 'none');
    groupId.disabled = true;
    groupId.required = false;
    manualInputs.forEach(cb => cb.disabled = true);

    // reset card styles
    document.querySelectorAll('.target-option-card').forEach(c => c.classList.remove('selected'));

    // apply selection
    if (containers[selection]) containers[selection].style.display = 'block';

    const radioLabel = document.querySelector(`label[for="select_${selection}"]`);
    if (radioLabel) radioLabel.classList.add('selected');

    if (selection === 'group') {
        groupId.disabled = false;
        groupId.required = true;
    } else if (selection === 'manual') {
        manualInputs.forEach(cb => cb.disabled = false);
        groupId.value = '';
        updateSelectedCount();
        updateTabCounts();
    } else if (selection === 'all') {
        groupId.value = '';
    }
}

// ── Search ────────────────────────────────────────────────────
function setupSearch(inputId, itemClass) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll(itemClass).forEach(function (item) {
            const match = (item.dataset.search || '').includes(q);
            item.style.display = match ? '' : 'none';
        });
        // sync select-all state after search
        const group = itemClass.replace('.', '').replace('-item', '');
        syncSelectAllByGroup(group);
    });
}

// ── Count helpers ─────────────────────────────────────────────
function updateSelectedCount() {
    const total = document.querySelectorAll('.manual-checkbox:checked').length;
    const bar   = document.getElementById('selectedCountBar');
    const text  = document.getElementById('selectedCountText');
    if (bar && text) {
        text.textContent = total + ' penerima dipilih';
        bar.style.display = total > 0 ? 'block' : 'none';
    }
}

function updateTabCounts() {
    const counts = {
        user:    document.querySelectorAll('.user-checkbox:checked').length,
        crew:    document.querySelectorAll('.crew-checkbox:checked').length,
        contact: document.querySelectorAll('.contact-checkbox:checked').length,
    };
    const els = {
        user:    document.getElementById('userCheckedCount'),
        crew:    document.getElementById('crewCheckedCount'),
        contact: document.getElementById('contactCheckedCount'),
    };
    Object.keys(counts).forEach(k => { if (els[k]) els[k].textContent = counts[k]; });
}

function syncSelectAll(checkbox) {
    const group = ['user', 'crew', 'contact'].find(g => checkbox.classList.contains(`${g}-checkbox`));
    if (group) syncSelectAllByGroup(group);
}

function syncSelectAllByGroup(group) {
    const saId = { user: 'selectAllUsers', crew: 'selectAllCrew', contact: 'selectAllContacts' }[group];
    if (!saId) return;
    const sa        = document.getElementById(saId);
    if (!sa) return;
    const allVisible  = Array.from(document.querySelectorAll(`.${group}-checkbox:not(:disabled)`))
                             .filter(cb => cb.closest('.checkbox-item')?.style.display !== 'none');
    const allChecked  = allVisible.length > 0 && allVisible.every(cb => cb.checked);
    const someChecked = allVisible.some(cb => cb.checked);
    sa.checked       = allChecked;
    sa.indeterminate = !allChecked && someChecked;
}
</script>
@endpush
