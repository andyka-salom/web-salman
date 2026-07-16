{{-- resources/views/security-event-categories/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Category Details - ' . $category->name)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Category Header --}}
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row">
                        <div class="col-md-10 mb-3 mb-md-0">
                            <h3 class="mb-3">{{ $category->name }}</h3>
                            <div class="mb-2">
                                <span class="badge badge-secondary">{{ $category->code }}</span>
                                @if($category->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </div>
                            @if($category->description)
                            <div class="mt-3">
                                <p class="text-muted mb-0">{{ $category->description }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-2 text-md-end text-center">
                            @can('security_event_category.edit')
                            <a href="{{ route('security-event-categories.edit', $category) }}" class="btn btn-primary mb-2 w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span class="btn-text-inner ms-2">Edit</span>
                            </a>
                            @endcan
                            <a href="{{ route('security-event-categories.index') }}" class="btn btn-secondary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                <span class="btn-text-inner ms-2">Back</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category Information --}}
            <div class="col-xl-8 col-lg-8 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Category Information</h5>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Category Code</p>
                            <p class="mb-0">{{ $category->code }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Category Name</p>
                            <p class="mb-0">{{ $category->name }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted mb-1 fw-bold">Description</p>
                            <p class="mb-0">{{ $category->description ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Status</p>
                            <p class="mb-0">
                                @if($category->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $category->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $category->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Info / Statistics --}}
            <div class="col-xl-4 col-lg-4 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <h5 class="mb-4 pb-3 border-bottom">Category ID</h5>

                    <div class="mb-3">
                        <p class="text-muted mb-1 fw-bold">UUID</p>
                        <p class="mb-0 text-break small">{{ $category->id }}</p>
                    </div>

                    @if($category->deleted_at)
                    <div class="mb-0">
                        <p class="text-muted mb-1 fw-bold">Deleted At</p>
                        <p class="mb-0">
                            <span class="badge badge-danger">{{ $category->deleted_at->format('d M Y, H:i') }}</span>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
