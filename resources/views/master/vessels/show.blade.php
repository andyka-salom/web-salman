@extends('layouts.app')

@section('title', 'Vessel Detail - ' . $vessel->name)

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/tomselect/tom-select.default.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .vessel-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    .vessel-header h3 {
        color: white;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .info-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid var(--card-border-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }
    .info-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .info-card h5 {
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--primary);
    }
    .info-item {
        margin-bottom: 16px;
        padding: 12px;
        background: var(--bs-gray-100);
        border-radius: 8px;
    }
    .info-item:last-child {
        margin-bottom: 0;
    }
    .info-label {
        font-weight: 600;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-value {
        font-size: 15px;
        color: var(--text-color);
        font-weight: 500;
    }
    .stats-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid var(--card-border-color);
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .stats-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 12px;
    }
    .crew-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        border: 1px solid var(--card-border-color);
        height: 100%;
    }
    .crew-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(113, 106, 202, 0.2);
    }
    .crew-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 22px;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .crew-info-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--bs-gray-200);
    }
    .crew-info-item:last-child {
        border-bottom: none;
    }
    .crew-info-item svg {
        margin-right: 8px;
        opacity: 0.6;
    }
    .position-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    .empty-state svg {
        opacity: 0.3;
        margin-bottom: 20px;
    }
    .empty-state h5 {
        color: var(--text-muted);
        font-weight: 600;
    }
    .position-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .position-chip {
        padding: 6px 12px;
        background: var(--bs-gray-100);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- Vessel Header --}}
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="vessel-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3>{{ $vessel->name }}</h3>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge badge-light-{{ $vessel->status_color }} fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $vessel->status)) }}
                                </span>
                                <span class="badge badge-light-info fs-6">{{ $vessel->type }}</span>
                                @if($vessel->valid_until)
                                    @php
                                        $days = $vessel->getDaysUntilExpiry();
                                        $badgeColor = $days < 0 ? 'danger' : ($days <= 30 ? 'warning' : 'success');
                                    @endphp
                                    <span class="badge badge-light-{{ $badgeColor }} fs-6">
                                        {{ $days < 0 ? 'Expired' : ($days <= 30 ? 'Expiring in '.$days.' days' : 'Valid until '.date('d M Y', strtotime($vessel->valid_until))) }}
                                    </span>
                                @else
                                    <span class="badge badge-light-success fs-6">Permanent License</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <button type="button" class="btn btn-light btn-action" data-bs-toggle="modal" data-bs-target="#assignCrewModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                    Assign
                                </button>
                                <a href="{{ route('vessels.edit', $vessel) }}" class="btn btn-light btn-action">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    Edit
                                </a>
                                <a href="{{ route('vessels.index') }}" class="btn btn-light btn-action">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                    Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Row --}}
        <div class="row layout-spacing">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <h6 class="text-muted mb-1">Total Crew</h6>
                    <h3 class="mb-0 fw-bold text-primary">{{ $currentCrewMembers->count() }}</h3>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <h6 class="text-muted mb-1">Active Crew</h6>
                    <h3 class="mb-0 fw-bold text-success">{{ $currentCrewMembers->where('is_active', true)->count() }}</h3>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <h6 class="text-muted mb-1">Inactive Crew</h6>
                    <h3 class="mb-0 fw-bold text-warning">{{ $currentCrewMembers->where('is_active', false)->count() }}</h3>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="stats-icon bg-info bg-opacity-10 text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    </div>
                    <h6 class="text-muted mb-1">Positions</h6>
                    <h3 class="mb-0 fw-bold text-info">{{ $crewByPosition->count() }}</h3>
                </div>
            </div>
        </div>

        {{-- Vessel Information & Position Summary --}}
        <div class="row layout-spacing">
            {{-- Vessel Information --}}
            <div class="col-xl-6 col-lg-6 col-sm-12">
                <div class="info-card">
                    <h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="5" r="3"></circle><line x1="12" y1="22" x2="12" y2="8"></line><path d="M5 12H2a10 10 0 0 0 20 0h-3"></path></svg>
                        Vessel Information
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Vessel Name</div>
                                <div class="info-value">{{ $vessel->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Type</div>
                                <div class="info-value">{{ $vessel->type }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Company</div>
                                <div class="info-value">{{ $vessel->company->name ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Coordinator</div>
                                <div class="info-value">{{ $vessel->coordinator->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Valid Until</div>
                                <div class="info-value">
                                    @if($vessel->valid_until)
                                        {{ $vessel->valid_until->format('d M Y') }}
                                        @if($vessel->getDaysUntilExpiry() !== null)
                                            <br><small class="text-muted">({{ $vessel->getDaysUntilExpiry() }} days remaining)</small>
                                        @endif
                                    @else
                                        <span class="badge badge-success">No Expiry</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="badge badge-{{ $vessel->is_active ? 'success' : 'danger' }}">
                                        {{ $vessel->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Crew by Position Summary --}}
            <div class="col-xl-6 col-lg-6 col-sm-12">
                <div class="info-card">
                    <h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        Crew by Position
                    </h5>

                    @if($crewByPosition->count() > 0)
                        <div class="position-summary">
                            @foreach($crewByPosition as $position => $count)
                                <div class="position-chip">
                                    <strong>{{ $position ?? 'Unassigned' }}:</strong> {{ $count }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No crew members assigned yet</p>
                    @endif

                    <div class="mt-4">
                        <h6 class="mb-3">Quick Stats</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Different Positions</span>
                            <strong>{{ $crewByPosition->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Available Crew</span>
                            <strong class="text-success">{{ $availableCrew->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Assignment Rate</span>
                            <strong class="text-primary">
                                {{ $currentCrewMembers->count() > 0 ? number_format(($currentCrewMembers->where('is_active', true)->count() / $currentCrewMembers->count()) * 100, 1) : 0 }}%
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned Crew Members --}}
        <div class="row layout-spacing">
            <div class="col-12">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Assigned Crew Members
                            <span class="badge badge-primary ms-2">{{ $currentCrewMembers->count() }}</span>
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignCrewModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Add Crew
                        </button>
                    </div>

                    @if($currentCrewMembers->count() > 0)
                        <div class="row" id="crewList">
                            @foreach($currentCrewMembers as $crew)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4" data-crew-id="{{ $crew->id }}">
                                <div class="crew-card card">
                                    <div class="card-body">
                                        {{-- Avatar & Name --}}
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="crew-avatar bg-gradient-primary">
                                                {{ strtoupper(substr($crew->name, 0, 2)) }}
                                            </div>
                                            <div class="ms-3 flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $crew->name }}</h6>
                                                <small class="text-muted">{{ $crew->nik }}</small>
                                                @if($crew->position)
                                                    <div class="mt-2">
                                                        <span class="position-badge">{{ $crew->position }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Contact Info --}}
                                        @if($crew->phone)
                                        <div class="crew-info-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <small class="text-muted">{{ $crew->phone }}</small>
                                        </div>
                                        @endif

                                        {{-- Assignment Date --}}
                                        @if($crew->currentVesselAssignment)
                                        <div class="crew-info-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            <small class="text-muted">{{ $crew->currentVesselAssignment->assigned_at->format('d M Y') }}</small>
                                        </div>

                                        {{-- Assigned By --}}
                                        @if($crew->assigned_by_user)
                                        <div class="crew-info-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            <small class="text-muted">By: {{ $crew->assigned_by_user->name }}</small>
                                        </div>
                                        @endif

                                        {{-- Notes --}}
                                        @if($crew->assignment_notes)
                                        <div class="crew-info-item">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            <small class="text-muted">{{ Str::limit($crew->assignment_notes, 40) }}</small>
                                        </div>
                                        @endif
                                        @endif

                                        {{-- Actions --}}
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <span class="badge badge-{{ $crew->is_active ? 'success' : 'danger' }}">
                                                {{ $crew->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger unassign-crew"
                                                    data-crew-id="{{ $crew->id }}"
                                                    data-crew-name="{{ $crew->name }}"
                                                    title="Unassign crew">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state" id="emptyState">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <h5 class="mb-3">No Crew Members Assigned</h5>
                            <p class="text-muted mb-4">This vessel doesn't have any crew members assigned yet.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignCrewModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Assign Your First Crew Member
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Assign Crew Modal --}}
<div class="modal fade" id="assignCrewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Assign Crew to {{ $vessel->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignCrewForm">
                @csrf
                <div class="modal-body">
                    @if($availableCrew->count() > 0)
                        <div class="mb-4">
                            <label for="crew_member_id" class="form-label">
                                Select Crew Member <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="crew_member_id" name="crew_member_id" required>
                                <option value="">-- Choose Crew Member --</option>
                                @foreach($availableCrew as $crew)
                                    <option value="{{ $crew->id }}"
                                            data-position="{{ $crew->position }}"
                                            data-phone="{{ $crew->phone }}">
                                        {{ $crew->name }} - {{ $crew->nik }}
                                        @if($crew->position) ({{ $crew->position }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Only unassigned crew members are shown</small>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Assignment Notes (Optional)</label>
                            <textarea class="form-control"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="Add any relevant notes for this assignment..."></textarea>
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>

                        {{-- Selected Crew Preview --}}
                        <div id="crewPreview" class="alert alert-info d-none">
                            <h6 class="mb-2">Selected Crew:</h6>
                            <div id="previewContent"></div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <strong>No Available Crew</strong>
                            <p class="mb-0 mt-2">All crew members are currently assigned to vessels. Please unassign crew from other vessels first or create new crew members.</p>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if($availableCrew->count() > 0)
                        <button type="submit" class="btn btn-primary" id="assignBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            <span class="btn-text-inner">Assign Crew Member</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/tomselect/tom-select.base.js') }}"></script>
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
$(document).ready(function() {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    const vesselId = '{{ $vessel->id }}';
    let crewSelect = null;

    // Initialize TomSelect
    @if($availableCrew->count() > 0)
    crewSelect = new TomSelect('#crew_member_id', {
        placeholder: 'Search and select crew member...',
        allowEmptyOption: true,
        create: false,
        render: {
            option: function(data, escape) {
                let position = data.dataset ? data.dataset.position : '';
                let phone = data.dataset ? data.dataset.phone : '';

                return '<div class="py-2">' +
                    '<div class="fw-bold">' + escape(data.text) + '</div>' +
                    (position ? '<small class="text-muted">Position: ' + escape(position) + '</small>' : '') +
                    (phone ? '<br><small class="text-muted">Phone: ' + escape(phone) + '</small>' : '') +
                    '</div>';
            }
        }
    });

    // Show crew preview on selection
    crewSelect.on('change', function(value) {
        if (value) {
            const option = crewSelect.options[value];
            const position = option.dataset ? option.dataset.position : '';
            const phone = option.dataset ? option.dataset.phone : '';

            let html = '<strong>' + option.text + '</strong><br>';
            if (position) html += '<small>Position: ' + position + '</small><br>';
            if (phone) html += '<small>Phone: ' + phone + '</small>';

            $('#previewContent').html(html);
            $('#crewPreview').removeClass('d-none');
        } else {
            $('#crewPreview').addClass('d-none');
        }
    });
    @endif

    // Reset modal on close
    $('#assignCrewModal').on('hidden.bs.modal', function() {
        $('#assignCrewForm')[0].reset();
        if (crewSelect) crewSelect.clear();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#crewPreview').addClass('d-none');
    });

    // Assign crew form submission
    $('#assignCrewForm').on('submit', function(e) {
        e.preventDefault();

        @if($availableCrew->count() === 0)
            return false;
        @endif

        const $btn = $('#assignBtn');
        const $spinner = $btn.find('.spinner-border');
        const $btnText = $btn.find('.btn-text-inner');
        const formData = new FormData(this);

        // Show loading state
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $btnText.text('Assigning...');

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: `/vessels/${vesselId}/crew/assign`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    $('#assignCrewModal').modal('hide');

                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const $field = $(`[name="${field}"]`);
                        $field.addClass('is-invalid');
                        $field.next('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON.message || 'Failed to assign crew member'
                    });
                }
            },
            complete: function() {
                // Reset button state
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $btnText.text('Assign Crew Member');
            }
        });
    });

    // Unassign crew
    $(document).on('click', '.unassign-crew', function() {
        const crewId = $(this).data('crew-id');
        const crewName = $(this).data('crew-name');

        Swal.fire({
            title: 'Unassign Crew Member?',
            html: `Are you sure you want to remove <strong>${crewName}</strong> from this vessel?<br><br>This action will make them available for assignment to other vessels.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, unassign!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-outline-secondary ms-2'
            },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: `/vessels/${vesselId}/crew/${crewId}/unassign`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        notes: 'Unassigned via vessel management interface'
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(
                        error.responseJSON?.message || 'Failed to unassign crew member'
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Toast.fire({
                    icon: 'success',
                    title: result.value.message
                });

                // Remove crew card with animation
                const $crewCard = $(`[data-crew-id="${crewId}"]`);
                $crewCard.fadeOut(400, function() {
                    $(this).remove();

                    // Check if no more crew members
                    if ($('#crewList .col-xl-3').length === 0) {
                        // Reload to show empty state
                        window.location.reload();
                    }
                });
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);

});
</script>
@endpush
