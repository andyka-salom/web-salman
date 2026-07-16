{{-- resources/views/crew-members/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Crew Member Details - ' . $crew->name)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Crew Header --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            <div class="avatar avatar-xl">
                                <span class="avatar-title rounded-circle bg-primary fs-1">
                                    {{ $crew->initials }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3 mb-md-0">
                            <h3 class="mb-3">{{ $crew->name }}</h3>
                            <div class="mb-2">
                                @if($crew->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                                @if($crew->gender)
                                    <span class="badge badge-{{ $crew->gender == 'male' ? 'primary' : ($crew->gender == 'female' ? 'danger' : 'secondary') }} ms-1">
                                        {{ $crew->gender_label }}
                                    </span>
                                @endif
                            </div>
                            <div class="mb-2">
                                <span class="text-muted">NIK: </span>
                                <strong>{{ $crew->nik }}</strong>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end text-center">
                            <a href="{{ route('crew-members.edit', $crew) }}" class="btn btn-primary mb-2 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span class="btn-text-inner ms-2">Edit</span>
                            </a>
                            <a href="{{ route('crew-members.index') }}" class="btn btn-secondary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                <span class="btn-text-inner ms-2">Back</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Personal Information</h5>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Full Name</p>
                            <p class="mb-0">{{ $crew->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">NIK</p>
                            <p class="mb-0">{{ $crew->nik }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Position</p>
                            <p class="mb-0">{{ $crew->position ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Gender</p>
                            <p class="mb-0">{{ $crew->gender_label }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Birth Date</p>
                            <p class="mb-0">
                                @if($crew->birth_date)
                                    {{ $crew->birth_date->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Age</p>
                            <p class="mb-0">
                                @if($crew->age)
                                    {{ $crew->age }} years
                                    @if($crew->age_group)
                                        <br><small class="text-muted">({{ $crew->age_group }})</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Blood Type</p>
                            <p class="mb-0">{{ $crew->blood_type ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Phone</p>
                            <p class="mb-0">{{ $crew->phone ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <p class="text-muted mb-1 fw-bold">Address</p>
                            <p class="mb-0">{{ $crew->address ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Work Information --}}
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Work Information</h5>

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1 fw-bold">Vessel</p>
                            <p class="mb-0">
                                @if($crew->vessel)
                                    {{ $crew->vessel->name }}
                                    @if($crew->vessel->company)
                                        <br><small class="text-muted">{{ $crew->vessel->company->name }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1 fw-bold">Created By</p>
                            <p class="mb-0">{{ $crew->creator->name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $crew->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $crew->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Groups Membership --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Group Membership</h5>

                    @if($crew->groups->count() > 0)
                    <div class="row">
                        @foreach($crew->groups as $group)
                        <div class="col-md-3 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="mb-2">{{ $group->group_name }}</h6>
                                    <span class="badge badge-{{ $group->is_active ? 'success' : 'secondary' }}">
                                        {{ $group->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="badge badge-info ms-1">{{ ucfirst($group->type) }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users text-muted mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <p class="text-muted">Not a member of any group</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
