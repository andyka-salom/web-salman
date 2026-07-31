@extends('layouts.app')

@section('title', 'Company Details - ' . $company->name)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Company Header --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            @if($company->logo)
                                <img src="{{ Storage::disk('public')->url($company->logo) }}"
                                     class="img-fluid rounded"
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     alt="{{ $company->name }}">
                            @else
                                <div class="avatar avatar-xl">
                                    <span class="avatar-title rounded bg-primary fs-1">
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8 mb-3 mb-md-0">
                            <h3 class="mb-3">{{ $company->name }}</h3>
                            <div class="mb-2">
                                @if($company->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </div>
                            <div class="mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin me-1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span class="text-muted">{{ $company->address }}</span>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end text-center">
                            @can('company.edit')
                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary mb-2 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span class="btn-text-inner ms-2">Edit</span>
                            </a>
                            @endcan
                            <a href="{{ route('companies.index') }}" class="btn btn-secondary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                <span class="btn-text-inner ms-2">Back</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Company Information --}}
            <div class="col-xl-8 col-lg-8 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Company Information</h5>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Company Name</p>
                            <p class="mb-0">{{ $company->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Slug</p>
                            <p class="mb-0">{{ $company->slug }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1 fw-bold">Address</p>
                            <p class="mb-0">{{ $company->address }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $company->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $company->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="col-xl-4 col-lg-4 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Statistics</h5>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-bold">Total Users</span>
                            <span class="badge badge-primary">{{ $company->users->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-bold">Active Users</span>
                            <span class="badge badge-success">{{ $company->users->where('is_active', true)->count() }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-bold">Total Areas</span>
                            <span class="badge badge-info">{{ $company->areas->count() }}</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Total Vessels</span>
                            <span class="badge badge-warning">{{ $company->vessels->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Users --}}
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Recent Users</h5>

                    @if($company->users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company->users->take(5) as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users text-muted mb-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <p class="text-muted">No users found</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Recent Areas --}}
            <div class="col-xl-6 col-lg-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Recent Areas</h5>

                    @if($company->areas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company->areas->take(5) as $area)
                                <tr>
                                    <td>{{ $area->name }}</td>
                                    <td>{{ $area->location ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map text-muted mb-3"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                        <p class="text-muted">No areas found</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
