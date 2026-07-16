@extends('layouts.app')

@section('title', 'Kelola Tindakan untuk #' . $report->report_number)

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* (Styles from previous answer remain the same) */
        .timeline-container { position: relative; padding-left: 50px; }
        .timeline-container::before { content: ''; position: absolute; left: 17px; top: 10px; bottom: 10px; width: 2px; background: #e9ecef; }
        .timeline-item { position: relative; margin-bottom: 2rem; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-marker { position: absolute; left: -50px; top: 0; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; border: 4px solid var(--bs-body-bg); }
        .status-completed .timeline-marker { background-color: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
        .status-cannot-do .timeline-marker { background-color: var(--bs-danger-bg-subtle); color: var(--bs-danger-text-emphasis); }
        .status-in-progress .timeline-marker { background-color: var(--bs-primary-bg-subtle); color: var(--bs-primary-text-emphasis); }
        .status-do .timeline-marker { background-color: var(--bs-secondary-bg-subtle); color: var(--bs-secondary-text-emphasis); }
        .timeline-content { border-radius: 0.5rem; padding: 1.25rem; border: 1px solid var(--bs-border-color); transition: all 0.2s ease-in-out; }
        .timeline-content:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,.05); border-color: var(--bs-primary); }
        .completion-notes { margin-top: 1rem; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid; font-style: italic; }
        .status-completed .completion-notes { border-color: var(--bs-success); background-color: var(--bs-success-bg-subtle); }
        .status-cannot-do .completion-notes { border-color: var(--bs-danger); background-color: var(--bs-danger-bg-subtle); }
        .stat-card { border-left: 4px solid; transition: background-color 0.2s; }
        .stat-card:hover { background-color: var(--bs-body-tertiary); }
    </style>
@endpush

@section('content')
    <!-- BREADCRUMB & HEADER -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cermat.reports.index') }}">CeRMAT</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cermat.reports.show', $report) }}">Detail Laporan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kelola Tindakan</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-0 fw-bolder">Rencana Tindakan Perbaikan</h1>
            <p class="mb-0 text-muted">Untuk Laporan <span class="fw-bold text-primary">#{{ $report->report_number }}</span></p>
        </div>
        <a href="{{ route('cermat.reports.show', $report) }}" class="btn btn-outline-secondary rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i> Kembali ke Detail</a>
    </div>

    {{-- ALERTS --}}
    @include('partials.alerts') {{-- Assuming you have a partials/alerts.blade.php --}}

    @php
        $actionItems = $report->actionItems;
        $totalActions = $actionItems->count();
        $completedActions = $actionItems->where('status', \App\Models\ActionItem::STATUS_COMPLETED)->count();
        $openActions = $actionItems->whereIn('status', [\App\Models\ActionItem::STATUS_DO, \App\Models\ActionItem::STATUS_IN_PROGRESS]);
        $overdueActions = $openActions->where('target_date', '<', now()->startOfDay())->count();
        $progressPercentage = $totalActions > 0 ? (($completedActions) / $totalActions) * 100 : 0;
    @endphp

    <!-- PROGRESS SUMMARY CARD -->
    @include('cermat.partials._action_progress_summary', compact('totalActions', 'completedActions', 'openActions', 'overdueActions', 'progressPercentage'))

    <div class="row g-4">
        {{-- Left Column: Timeline of Actions --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-3 px-4">
                    <h5 class="mb-0 card-title fw-bold"><i class="bi bi-list-check text-primary me-2"></i>Daftar Rencana Tindakan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-container">
                        @forelse($actionItems->sortBy('target_date') as $item)
                            <div class="timeline-item status-{{ Str::slug($item->status) }}">
                                <div class="timeline-marker">
                                    {{-- Icon logic --}}
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        {{-- Description and info --}}
                                        <div class="flex-grow-1">
                                            <p class="fw-bold mb-1">{{ $item->description }}</p>
                                            <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                                                <span><i class="bi bi-person-fill me-1"></i> {{ $item->responsible->name ?? 'N/A' }}</span>
                                                <span>
                                                    <i class="bi bi-calendar-event me-1"></i> Target: {{ \Carbon\Carbon::parse($item->target_date)->isoFormat('DD MMM Y') }}
                                                    @if(!in_array($item->status, ['Completed', "Cannot Do"]) && $item->target_date < now()->startOfDay())
                                                        <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill ms-1">Terlambat</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Dropdown Actions --}}
                                        @if(!in_array($item->status, ['Completed', 'Cannot Do']))
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if($item->status === 'Do')
                                                <li><form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="In Progress"><button type="submit" class="dropdown-item fw-medium"><i class="bi bi-play-circle-fill me-2"></i>Mulai Kerjakan</button></form></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @endif
                                                <li><a class="dropdown-item text-success fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#actionStatusModal-{{ $item->id }}-completed"><i class="bi bi-check-circle-fill me-2"></i>Tandai Selesai</a></li>
                                                <li><a class="dropdown-item text-danger fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#actionStatusModal-{{ $item->id }}-cantdo"><i class="bi bi-x-circle-fill me-2"></i>Tidak Bisa Dikerjakan</a></li>
                                                {{-- EXTEND BUTTON --}}
                                                @if(!$item->extend_date_3) {{-- Only show if there are extension slots left --}}
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-warning fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#extendModal-{{ $item->id }}"><i class="bi bi-calendar-plus-fill me-2"></i>Perpanjang Target</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        @else
                                            {{-- Status badge for completed items --}}
                                        @endif
                                    </div>
                                    {{-- Completion notes section --}}
                                </div>
                            </div>

                            <!-- Include Status and Extend Modals for each item -->
                            @include('cermat.partials._action_status_modals', ['item' => $item])
                            @include('cermat.partials._action_extend_modal', ['item' => $item])

                        @empty
                            <div class="text-center p-5 bg-light rounded-3">
                                <i class="bi bi-clipboard-x fs-1 text-primary"></i>
                                <h5 class="mt-3 fw-bold">Belum Ada Tindakan</h5>
                                <p class="mb-0 text-muted">Gunakan formulir di samping untuk menambahkan tindakan perbaikan pertama.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Add New Action Form --}}
        <div class="col-lg-5">
            {{-- Form from previous answer --}}
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Tom-Select script from previous answer --}}
@endpush
