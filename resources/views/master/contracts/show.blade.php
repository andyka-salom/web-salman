@extends('layouts.app')

@section('title', 'Contract Details - ' . $contract->nama_kontraktor)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        {{-- Assume layout handles breadcrumbs inclusion --}}

        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">{{ $contract->nama_kontraktor }}</h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span class="btn-text-inner ms-2">Edit</span>
                            </a>
                            <a href="{{ route('contracts.index') }}" class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                <span class="btn-text-inner ms-2">Back</span>
                            </a>
                        </div>
                    </div>

                    <h5 class="mb-4 pb-3 border-bottom">Kontraktor Information</h5>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <p class="text-muted mb-1 fw-bold">Nama Kontraktor</p>
                            <p class="mb-0 fs-5">{{ $contract->nama_kontraktor }}</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <p class="text-muted mb-1 fw-bold">Nomor SAP</p>
                            <p class="mb-0 fs-5">{{ $contract->sap_no }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <p class="text-muted mb-1 fw-bold">Email Kantor</p>
                            <p class="mb-0">{{ $contract->alamat_email ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <p class="text-muted mb-1 fw-bold">Nomor Telepon Kantor</p>
                            <p class="mb-0">{{ $contract->no_tlp_kantor ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <p class="text-muted mb-1 fw-bold">Alamat Kantor</p>
                            <p class="mb-0">{{ $contract->alamat_kantor ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row border-top pt-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $contract->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $contract->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
