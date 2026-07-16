@extends('layouts.app')

@section('title', 'Detail Kontak - ' . $contact->name)

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .info-card {
        background: var(--card-bg);
        border-radius: 12px;
        border: 1px solid var(--card-border-color);
        overflow: hidden;
    }
    .info-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--card-border-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-card-header h5 {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        margin: 0;
    }
    .info-card-body { padding: 24px; }
    .info-item { margin-bottom: 20px; }
    .info-item:last-child { margin-bottom: 0; }
    .info-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: var(--text-color);
    }
    .contact-hero {
        background: linear-gradient(135deg, rgba(113,106,202,0.08) 0%, rgba(37,211,102,0.06) 100%);
        border-radius: 12px;
        border: 1px solid var(--card-border-color);
        padding: 28px;
        margin-bottom: 0;
    }
    .contact-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(113,106,202,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        color: #716aca;
        flex-shrink: 0;
    }
    .badge-active {
        background: rgba(0,168,107,0.12);
        color: #00a86b;
        border: 1px solid rgba(0,168,107,0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .badge-inactive {
        background: rgba(231,76,60,0.10);
        color: #e74c3c;
        border: 1px solid rgba(231,76,60,0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .badge-position {
        background: rgba(0,150,255,0.10);
        color: #0096ff;
        border: 1px solid rgba(0,150,255,0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .wa-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #25D366;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 18px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s, transform 0.15s;
    }
    .wa-btn:hover { background: #1ebe5c; color: #fff; transform: translateY(-1px); }
    .group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--card-border-color);
    }
    .group-item:last-child { border-bottom: none; }
    .group-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-color);
        text-decoration: none;
    }
    .group-name:hover { color: #716aca; }
    .group-desc {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .empty-groups {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }
    .empty-groups svg { opacity: 0.25; margin-bottom: 10px; }
    .action-btn-group .btn {
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .meta-row {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed var(--card-border-color);
    }
    .meta-item { font-size: 12px; color: var(--text-muted); }
    .meta-item strong { color: var(--text-color); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Back --}}
            <div class="col-12 mb-3">
                <a href="{{ route('contacts.index') }}" class="text-muted text-decoration-none d-inline-flex align-items-center gap-1" style="font-size:13px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Kembali ke Contacts
                </a>
            </div>

            {{-- Hero Card --}}
            <div class="col-12 mb-4 layout-spacing">
                <div class="contact-hero">
                    <div class="d-flex flex-wrap gap-3 align-items-start justify-content-between">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="contact-avatar">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="fw-bold mb-2">{{ $contact->name }}</h3>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    @if($contact->is_active)
                                        <span class="badge-active">● Active</span>
                                    @else
                                        <span class="badge-inactive">● Inactive</span>
                                    @endif
                                    @if($contact->position)
                                        <span class="badge-position">{{ $contact->position }}</span>
                                    @endif
                                </div>
                                <div class="meta-row">
                                    <div class="meta-item">
                                        Dibuat <strong>{{ $contact->created_at->format('d M Y') }}</strong>
                                    </div>
                                    <div class="meta-item">
                                        Diperbarui <strong>{{ $contact->updated_at->diffForHumans() }}</strong>
                                    </div>
                                    <div class="meta-item">
                                        {{ $contact->groups->count() }} <strong>Grup</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="action-btn-group d-flex flex-wrap gap-2 mt-2 mt-md-0">
                            <a href="https://wa.me/{{ $contact->whatsapp_number }}" target="_blank" class="wa-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326z"/></svg>
                                Chat WA
                            </a>
                            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="info-card">
                    <div class="info-card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <h5>Informasi Kontak</h5>
                    </div>
                    <div class="info-card-body">

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-label">Nama Lengkap</div>
                                    <div class="info-value">{{ $contact->name }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-label">Jabatan</div>
                                    <div class="info-value">
                                        @if($contact->position)
                                            {{ $contact->position }}
                                        @else
                                            <span class="text-muted fst-italic">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-label">Nomor WhatsApp</div>
                                    <div class="info-value">
                                        <a href="https://wa.me/{{ $contact->whatsapp_number }}"
                                           target="_blank"
                                           class="d-inline-flex align-items-center gap-2 text-decoration-none"
                                           style="color:#25D366;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                            +{{ $contact->whatsapp_number }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-label">Keterangan / Notes</div>
                                    <div class="info-value" style="white-space:pre-wrap;font-size:14px;line-height:1.6;">
                                        @if($contact->notes)
                                            {{ $contact->notes }}
                                        @else
                                            <span class="text-muted fst-italic">Tidak ada keterangan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-label">Dibuat Pada</div>
                                    <div class="info-value" style="font-size:13px;">{{ $contact->created_at->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-label">Terakhir Diperbarui</div>
                                    <div class="info-value" style="font-size:13px;">{{ $contact->updated_at->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Groups --}}
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="info-card">
                    <div class="info-card-header justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <h5>Member of Groups</h5>
                        </div>
                        @if($contact->groups->count() > 0)
                        <span style="background:rgba(113,106,202,0.12);color:#716aca;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid rgba(113,106,202,0.2);">
                            {{ $contact->groups->count() }}
                        </span>
                        @endif
                    </div>
                    <div class="info-card-body">
                        @if($contact->groups->count() > 0)
                            @foreach($contact->groups as $group)
                            <div class="group-item">
                                <div>
                                    <a href="{{ route('groups.show', $group) }}" class="group-name">
                                        {{ $group->group_name }}
                                    </a>
                                    @if($group->description)
                                        <div class="group-desc">{{ Str::limit($group->description, 80) }}</div>
                                    @endif
                                </div>
                                @if($group->is_active)
                                    <span class="badge-active">Active</span>
                                @else
                                    <span class="badge-inactive">Inactive</span>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="empty-groups">
                                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                <p class="mb-1 fw-semibold" style="font-size:14px;">Belum ada grup</p>
                                <p class="text-muted mb-0" style="font-size:12px;">Kontak ini belum tergabung di grup manapun.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
