@extends('layouts.app')

@section('title', 'Details - Pelanggaran No. ' . $pelanggaran->sequence_number)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row">
                        <div class="col-md-10">
                            <h3 class="mb-3">{{ $pelanggaran->name }}</h3>
                            <div class="mb-2">
                                {{-- PERUBAHAN: Menampilkan Sequence Number --}}
                                <span class="badge badge-light-primary mb-2 me-2">No. {{ $pelanggaran->sequence_number }}</span>
                                <span class="badge {{ $pelanggaran->severity_badge_class }} mb-2 me-2">{{ ucfirst($pelanggaran->severity) }}</span>
                                @if($pelanggaran->is_active)
                                    <span class="badge badge-success mb-2">Active</span>
                                @else
                                    <span class="badge badge-danger mb-2">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end text-center">
                            <a href="{{ route('pelanggaran.edit', $pelanggaran) }}" class="btn btn-primary mb-2 w-100">Edit</a>
                            <a href="{{ route('pelanggaran.index') }}" class="btn btn-secondary w-100">Back</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Information</h5>

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1 fw-bold">Description</p>
                            <p class="mb-0">{{ $pelanggaran->description ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <p class="text-muted mb-1 fw-bold">Sequence Number</p>
                            <p class="mb-0 fs-5">{{ $pelanggaran->sequence_number }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-muted mb-1 fw-bold">Severity</p>
                            <span class="badge {{ $pelanggaran->severity_badge_class }} fs-5">{{ ucfirst($pelanggaran->severity) }}</span>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $pelanggaran->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $pelanggaran->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
