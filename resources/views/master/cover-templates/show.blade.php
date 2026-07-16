@extends('layouts.app')
@section('title', 'Template Details')

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
                <h3>{{ $coverTemplate->name }}</h3>
                <div>
                    <a href="{{ route('cover-templates.edit', $coverTemplate) }}" class="btn btn-primary me-2">Edit</a>
                    <a href="{{ route('cover-templates.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>

            {{-- Previews --}}
            <div class="col-md-6 layout-spacing">
                <div class="widget-content widget-content-area br-8 text-center p-4">
                    <h5 class="mb-3">Cover Image (Front)</h5>
                    <div class="border p-2 d-inline-block bg-light rounded">
                        <img src="{{ $coverTemplate->cover_url }}" class="img-fluid shadow-sm" style="max-height: 600px; width: auto;">
                    </div>
                </div>
            </div>

            <div class="col-md-6 layout-spacing">
                <div class="widget-content widget-content-area br-8 text-center p-4">
                    <h5 class="mb-3">Page Background (Content)</h5>
                    <div class="border p-2 d-inline-block bg-light rounded">
                        <img src="{{ $coverTemplate->page_url }}" class="img-fluid shadow-sm" style="max-height: 600px; width: auto;">
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="col-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5>Details</h5>
                    <div class="row mt-3">
                        <div class="col-md-3 mb-3">
                            <span class="d-block text-muted fw-bold">Status</span>
                            <span class="badge {{ $coverTemplate->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $coverTemplate->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <span class="d-block text-muted fw-bold">Usage Count</span>
                            <span>{{ $coverTemplate->usage_count }} times</span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <span class="d-block text-muted fw-bold">Created At</span>
                            <span>{{ $coverTemplate->created_at->format('d M Y') }}</span>
                        </div>
                        {{-- Kolom Order Dihapus --}}
                        <div class="col-12">
                            <span class="d-block text-muted fw-bold">Description</span>
                            <p>{{ $coverTemplate->description ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
