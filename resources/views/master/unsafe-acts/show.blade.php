{{-- resources/views/unsafe-acts/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Unsafe Act Details - ' . $unsafeAct->code)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Unsafe Act Header --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row">
                        <div class="col-md-10 mb-3 mb-md-0">
                            <h3 class="mb-3">{{ $unsafeAct->code }}</h3>
                            <div class="mb-3">
                                @if($unsafeAct->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                                <span class="badge badge-info ms-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2 me-1">
                                        <line x1="18" y1="20" x2="18" y2="10"></line>
                                        <line x1="12" y1="20" x2="12" y2="4"></line>
                                        <line x1="6" y1="20" x2="6" y2="14"></line>
                                    </svg>
                                    Used {{ $unsafeAct->usage_count }} time(s)
                                </span>
                            </div>
                            <div class="mt-3">
                                <p class="mb-0">{{ $unsafeAct->description }}</p>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end text-center">
                            @can('unsafe_act.edit')
                            <a href="{{ route('unsafe-acts.edit', $unsafeAct) }}" class="btn btn-primary mb-2 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span class="btn-text-inner ms-2">Edit</span>
                            </a>
                            @endcan
                            <a href="{{ route('unsafe-acts.index') }}" class="btn btn-secondary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                <span class="btn-text-inner ms-2">Back</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Unsafe Act Information --}}
            <div class="col-xl-8 col-lg-8 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Unsafe Act Information</h5>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Code</p>
                            <p class="mb-0">{{ $unsafeAct->code }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Status</p>
                            <p class="mb-0">
                                @if($unsafeAct->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1 fw-bold">Description</p>
                            <p class="mb-0">{{ $unsafeAct->description }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Usage Count</p>
                            <p class="mb-0">
                                <span class="badge badge-info">{{ $unsafeAct->usage_count }} time(s)</span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $unsafeAct->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $unsafeAct->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics & Additional Info --}}
            <div class="col-xl-4 col-lg-4 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Statistics</h5>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-bold">Total Reports</span>
                            <span class="badge badge-primary">{{ $unsafeAct->cermatReports->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-bold">Usage Count</span>
                            <span class="badge badge-info">{{ $unsafeAct->usage_count }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="mt-4">
                        <p class="text-muted mb-1 fw-bold">UUID</p>
                        <p class="mb-0 text-break small">{{ $unsafeAct->id }}</p>
                    </div>

                    @if($unsafeAct->deleted_at)
                    <div class="mt-3">
                        <p class="text-muted mb-1 fw-bold">Deleted At</p>
                        <p class="mb-0">
                            <span class="badge badge-danger">{{ $unsafeAct->deleted_at->format('d M Y, H:i') }}</span>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Recent Reports Using This Unsafe Act --}}
            @if($unsafeAct->cermatReports->count() > 0)
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Recent Reports Using This Unsafe Act</h5>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report ID</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unsafeAct->cermatReports->take(10) as $report)
                                <tr>
                                    <td>{{ $report->report_number ?? 'N/A' }}</td>
                                    <td>{{ $report->created_at->format('d M Y') }}</td>
                                    <td>{{ $report->location ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $report->status ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>

    </div>
</div>
@endsection
