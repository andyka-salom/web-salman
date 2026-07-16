@extends('layouts.app')

@section('title', 'Manage Members - ' . $group->group_name)

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .member-card {
        transition: all 0.3s ease;
    }
    .member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(113, 106, 202, 0.15);
    }
    .member-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }
    .member-list-container {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid var(--card-border-color);
        border-radius: 8px;
        padding: 10px;
    }
    .member-item {
        padding: 9px 4px;
        border-bottom: 1px dashed var(--card-border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .member-item:last-child { border-bottom: none; }
    .member-item.d-none-search { display: none !important; }
    .search-select-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .search-select-header .form-control { flex-grow: 1; }
    .tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 10px;
        padding: 0 6px;
        margin-left: 6px;
    }
    .member-item .member-info strong { font-size: 14px; }
    .member-item .member-info small { font-size: 12px; }
    .member-item.is-existing {
        opacity: 0.5;
        background: rgba(0,0,0,0.02);
        border-radius: 6px;
    }
    .wa-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #25D366;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Header --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">{{ $group->group_name }}</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-primary">{{ $group->member_count }} Members</span>
                                <span class="badge badge-info">{{ $group->users->count() }} Users</span>
                                <span class="badge badge-warning">{{ $group->crewMembers->count() }} Crew</span>
                                <span class="badge badge-secondary">{{ $group->contracts->count() }} Contracts</span>
                                <span class="badge badge-success">{{ $group->contacts->count() }} Contacts</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                            <a href="{{ route('groups.show', $group) }}" class="btn btn-outline-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                Back to Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add Members --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Add New Members</h5>

                    <form id="addMembersBatchForm">
                        @csrf
                        <ul class="nav nav-tabs mb-3" id="memberTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#user-pane" type="button" role="tab">
                                    Users
                                    <span class="tab-badge" style="background:rgba(113,106,202,0.15);color:#716aca;">{{ $users->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#crew-pane" type="button" role="tab">
                                    Crew
                                    <span class="tab-badge" style="background:rgba(255,165,0,0.15);color:#e67e22;">{{ $crews->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contact-pane" type="button" role="tab">
                                    Contacts
                                    <span class="tab-badge" style="background:rgba(37,211,102,0.15);color:#25D366;">{{ $contacts->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contract-pane" type="button" role="tab">
                                    Contracts
                                    <span class="tab-badge" style="background:rgba(108,117,125,0.15);color:#6c757d;">{{ $contracts->count() }}</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="memberTabsContent">

                            {{-- ── User Tab ── --}}
                            <div class="tab-pane fade show active" id="user-pane" role="tabpanel" tabindex="0">
                                <div class="search-select-header">
                                    <input type="text" class="form-control" id="userSearch" placeholder="Cari nama atau email...">
                                    <div class="form-check form-check-primary mb-0 text-nowrap">
                                        <input class="form-check-input select-all-checkbox" type="checkbox" data-target="#user-pane">
                                        <label class="form-check-label">Pilih Semua</label>
                                    </div>
                                </div>
                                <div class="member-list-container">
                                    @forelse($users as $user)
                                    <div class="member-item {{ in_array((string)$user->id, $existingUserIds) ? 'is-existing' : '' }}"
                                         data-search-term="{{ strtolower($user->name . ' ' . $user->email) }}">
                                        <div class="member-info">
                                            <strong>{{ $user->name }}</strong>
                                            <small class="text-muted d-block">{{ $user->email }}</small>
                                        </div>
                                        <div class="form-check form-check-primary mb-0">
                                            <input class="form-check-input member-checkbox"
                                                   type="checkbox"
                                                   name="selected_members[]"
                                                   value="user|{{ $user->id }}"
                                                   {{ in_array((string)$user->id, $existingUserIds) ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-muted text-center py-3 mb-0">Tidak ada user tersedia.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ── Crew Tab ── --}}
                            <div class="tab-pane fade" id="crew-pane" role="tabpanel" tabindex="0">
                                <div class="search-select-header">
                                    <input type="text" class="form-control" id="crewSearch" placeholder="Cari nama, posisi, atau nomor...">
                                    <div class="form-check form-check-warning mb-0 text-nowrap">
                                        <input class="form-check-input select-all-checkbox" type="checkbox" data-target="#crew-pane">
                                        <label class="form-check-label">Pilih Semua</label>
                                    </div>
                                </div>
                                <div class="member-list-container">
                                    @forelse($crews as $crew)
                                    <div class="member-item {{ in_array((string)$crew->id, $existingCrewIds) ? 'is-existing' : '' }}"
                                         data-search-term="{{ strtolower($crew->name . ' ' . ($crew->position ?? '') . ' ' . $crew->phone) }}">
                                        <div class="member-info">
                                            <strong>{{ $crew->name }}</strong>
                                            <small class="text-muted d-block">{{ $crew->position ?? 'N/A' }} · {{ $crew->phone }}</small>
                                        </div>
                                        <div class="form-check form-check-warning mb-0">
                                            <input class="form-check-input member-checkbox"
                                                   type="checkbox"
                                                   name="selected_members[]"
                                                   value="crew|{{ $crew->id }}"
                                                   {{ in_array((string)$crew->id, $existingCrewIds) ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-muted text-center py-3 mb-0">Tidak ada crew tersedia.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ── Contact Tab (NEW) ── --}}
                            <div class="tab-pane fade" id="contact-pane" role="tabpanel" tabindex="0">
                                <div class="search-select-header">
                                    <input type="text" class="form-control" id="contactSearch" placeholder="Cari nama, nomor WA, atau jabatan...">
                                    <div class="form-check mb-0 text-nowrap">
                                        <input class="form-check-input select-all-checkbox" type="checkbox" data-target="#contact-pane"
                                               style="border-color:#25D366;">
                                        <label class="form-check-label">Pilih Semua</label>
                                    </div>
                                </div>
                                <div class="member-list-container">
                                    @forelse($contacts as $contact)
                                    <div class="member-item {{ in_array((string)$contact->id, $existingContactIds) ? 'is-existing' : '' }}"
                                         data-search-term="{{ strtolower($contact->name . ' ' . $contact->whatsapp_number . ' ' . ($contact->position ?? '')) }}">
                                        <div class="member-info">
                                            <strong>{{ $contact->name }}</strong>
                                            <span class="d-block">
                                                <span class="wa-badge">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326z"/></svg>
                                                    {{ $contact->whatsapp_number }}
                                                </span>
                                                @if($contact->position)
                                                <small class="text-muted ms-2">{{ $contact->position }}</small>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input member-checkbox"
                                                   type="checkbox"
                                                   name="selected_members[]"
                                                   value="contact|{{ $contact->id }}"
                                                   style="border-color:#25D366;"
                                                   {{ in_array((string)$contact->id, $existingContactIds) ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-muted text-center py-3 mb-0">Tidak ada kontak tersedia.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ── Contract Tab ── --}}
                            <div class="tab-pane fade" id="contract-pane" role="tabpanel" tabindex="0">
                                <div class="search-select-header">
                                    <input type="text" class="form-control" id="contractSearch" placeholder="Cari nama kontraktor atau SAP No...">
                                    <div class="form-check form-check-secondary mb-0 text-nowrap">
                                        <input class="form-check-input select-all-checkbox" type="checkbox" data-target="#contract-pane">
                                        <label class="form-check-label">Pilih Semua</label>
                                    </div>
                                </div>
                                <div class="member-list-container">
                                    @forelse($contracts as $contract)
                                    <div class="member-item {{ in_array((string)$contract->id, $existingContractIds) ? 'is-existing' : '' }}"
                                         data-search-term="{{ strtolower($contract->nama_kontraktor . ' ' . $contract->sap_no) }}">
                                        <div class="member-info">
                                            <strong>{{ $contract->nama_kontraktor }}</strong>
                                            <small class="text-muted d-block">SAP: {{ $contract->sap_no }}</small>
                                        </div>
                                        <div class="form-check form-check-secondary mb-0">
                                            <input class="form-check-input member-checkbox"
                                                   type="checkbox"
                                                   name="selected_members[]"
                                                   value="contract|{{ $contract->id }}"
                                                   {{ in_array((string)$contract->id, $existingContractIds) ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-muted text-center py-3 mb-0">Tidak ada contract tersedia.</p>
                                    @endforelse
                                </div>
                            </div>

                        </div>{{-- /.tab-content --}}

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted" id="selectedCount">0 member dipilih</small>
                            <button type="submit" class="btn btn-success" id="addMembersBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                Add Selected Members
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Current Members --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">
                        Current Members
                        <span class="badge badge-primary ms-2">{{ $members->count() }}</span>
                    </h5>

                    @if($members->count() > 0)
                        <div class="row" id="membersList">
                            @foreach($members as $member)
                            @php
                                $avatarColor = match($member['type']) {
                                    'user'     => '#716aca',
                                    'crew'     => '#e67e22',
                                    'contact'  => '#25D366',
                                    'contract' => '#6c757d',
                                    default    => '#6c757d',
                                };
                                $typeBadgeStyle = match($member['type']) {
                                    'user'     => 'background:rgba(113,106,202,0.12);color:#716aca;',
                                    'crew'     => 'background:rgba(230,126,34,0.12);color:#e67e22;',
                                    'contact'  => 'background:rgba(37,211,102,0.12);color:#25D366;',
                                    'contract' => 'background:rgba(108,117,125,0.12);color:#6c757d;',
                                    default    => '',
                                };
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4"
                                 data-member-id="{{ $member['id'] }}"
                                 data-member-type="{{ $member['type'] }}">
                                <div class="member-card card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="member-avatar" style="background:{{ $avatarColor }};">
                                                {{ strtoupper(substr($member['name'], 0, 2)) }}
                                            </div>
                                            <div class="ms-3 flex-grow-1 overflow-hidden">
                                                <h6 class="mb-1 text-truncate">{{ $member['name'] }}</h6>
                                                <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:10px;{{ $typeBadgeStyle }}">
                                                    {{ ucfirst($member['type']) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Detail baris --}}
                                        @if($member['type'] === 'contact')
                                            <div class="mb-2">
                                                <small class="d-flex align-items-center gap-1" style="color:#25D366;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326z"/></svg>
                                                    {{ $member['phone'] }}
                                                </small>
                                            </div>
                                            @if(!empty($member['position']))
                                            <div class="mb-2">
                                                <small class="text-muted">{{ $member['position'] }}</small>
                                            </div>
                                            @endif
                                        @elseif($member['type'] === 'contract')
                                            <div class="mb-2">
                                                <small class="text-muted">SAP: {{ $member['sap_no'] ?? '-' }}</small>
                                            </div>
                                        @else
                                            @if(!empty($member['email']))
                                            <div class="mb-1">
                                                <small class="text-muted text-truncate d-block">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                                    {{ $member['email'] }}
                                                </small>
                                            </div>
                                            @endif
                                            @if(!empty($member['phone']))
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.19 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                                    {{ $member['phone'] }}
                                                </small>
                                            </div>
                                            @endif
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            @if(isset($member['is_active']))
                                                <span class="badge {{ $member['is_active'] ? 'badge-success' : 'badge-danger' }}" style="font-size:11px;">
                                                    {{ $member['is_active'] ? 'Active' : 'Inactive' }}
                                                </span>
                                            @else
                                                <span></span>
                                            @endif

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger remove-member"
                                                    data-member-id="{{ $member['id'] }}"
                                                    data-member-type="{{ $member['type'] }}"
                                                    data-member-name="{{ e($member['name']) }}"
                                                    title="Remove from group">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-3" style="opacity:0.3;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <h5 class="text-muted">Belum ada member</h5>
                            <p class="text-muted mb-0">Gunakan panel "Add New Members" di atas untuk menambah member.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
$(document).ready(function () {

    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 3000, timerProgressBar: true,
        didOpen: (t) => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
    });

    // ── 1. Search ──────────────────────────────────────────────────────
    function setupSearch(inputId, paneId) {
        $(inputId).on('input', function () {
            const q     = $(this).val().toLowerCase();
            const $pane = $(paneId);

            $pane.find('.member-item').each(function () {
                const match = $(this).data('search-term').includes(q);
                $(this).toggle(match);
            });

            updateSelectAllStatus($pane);
        });
    }

    setupSearch('#userSearch',     '#user-pane');
    setupSearch('#crewSearch',     '#crew-pane');
    setupSearch('#contactSearch',  '#contact-pane');
    setupSearch('#contractSearch', '#contract-pane');

    // ── 2. Select All ─────────────────────────────────────────────────
    $(document).on('change', '.select-all-checkbox', function () {
        const $pane     = $($(this).data('target'));
        const isChecked = this.checked;
        $pane.find('.member-checkbox:not(:disabled):visible').prop('checked', isChecked);
        $(this).prop('indeterminate', false);
        updateSelectedCount();
    });

    $(document).on('change', '.member-checkbox', function () {
        updateSelectAllStatus($(this).closest('.tab-pane'));
        updateSelectedCount();
    });

    function updateSelectAllStatus($pane) {
        const $available = $pane.find('.member-checkbox:not(:disabled):visible');
        const total      = $available.length;
        const checked    = $available.filter(':checked').length;
        const $sa        = $pane.find('.select-all-checkbox');

        if (total === 0) {
            $sa.prop({ checked: false, indeterminate: false, disabled: true });
        } else {
            $sa.prop('disabled', false);
            if (checked === total)      $sa.prop({ checked: true,  indeterminate: false });
            else if (checked > 0)       $sa.prop({ checked: false, indeterminate: true });
            else                        $sa.prop({ checked: false, indeterminate: false });
        }
    }

    function updateSelectedCount() {
        const n = $('.member-checkbox:checked').length;
        $('#selectedCount').text(n + ' member dipilih');
    }

    // Init on page load and tab switch
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        updateSelectAllStatus($($(e.target).data('bs-target')));
    });
    updateSelectAllStatus($('#user-pane'));

    // ── 3. Batch Add ──────────────────────────────────────────────────
    $('#addMembersBatchForm').on('submit', function (e) {
        e.preventDefault();

        const selected = $(this).find('.member-checkbox:checked').map(function () { return this.value; }).get();

        if (selected.length === 0) {
            Toast.fire({ icon: 'warning', title: 'Pilih minimal satu member.' });
            return;
        }

        const $btn      = $('#addMembersBtn');
        const origHtml  = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menambahkan...');

        $.ajax({
            url: "{{ route('groups.members.batch', $group) }}",
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                selected_members: selected,
            },
            success: function (res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(() => window.location.reload(), 1000);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(origHtml);
                if (xhr.status === 419) {
                    Toast.fire({ icon: 'error', title: 'Session expired. Refresh halaman.' });
                } else {
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal menambahkan member.' });
                }
            },
        });
    });

    // ── 4. Remove Member ──────────────────────────────────────────────
    $(document).on('click', '.remove-member', function () {
        const name = $(this).data('member-name');
        const id   = $(this).data('member-id');
        const type = $(this).data('member-type');

        Swal.fire({
            title: 'Hapus Member?',
            html: `Yakin ingin menghapus <strong>${name}</strong> dari grup ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
            buttonsStyling: false,
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('groups.members.remove', $group) }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', member_type: type, member_id: id },
                success: function (res) {
                    if (res.success) {
                        Toast.fire({ icon: 'success', title: res.message });

                        $(`[data-member-id="${id}"][data-member-type="${type}"]`).fadeOut(300, function () {
                            $(this).remove();
                            if ($('#membersList .col-xl-3').length === 0) window.location.reload();
                        });

                        // Re-enable checkbox in add panel
                        $(`.member-checkbox[value="${type}|${id}"]`).prop({ disabled: false, checked: false });
                        $(`[data-member-id="${id}"][data-member-type="${type}"]`).closest('.member-item').removeClass('is-existing');
                        updateSelectedCount();
                    }
                },
                error: function (xhr) {
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal menghapus member.' });
                },
            });
        });
    });

});
</script>
@endpush
