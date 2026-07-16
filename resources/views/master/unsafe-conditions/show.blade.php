@extends('layouts.app')

@section('title', 'Details - ' . $unsafeCondition->code)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <div class="row">
                        <div class="col-md-10">
                            <h3 class="mb-3">{{ $unsafeCondition->code }}</h3>
                            <div class="mb-2">
                                @if($unsafeCondition->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                                <span class="badge badge-info ms-2">Used {{ $unsafeCondition->usage_count }} times</span>
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end text-center">
                            <a href="{{ route('unsafe-conditions.edit', $unsafeCondition) }}" class="btn btn-primary mb-2 w-100">
                                <span class="btn-text-inner">Edit</span>
                            </a>
                            <a href="{{ route('unsafe-conditions.index') }}" class="btn btn-secondary w-100">
                                <span class="btn-text-inner">Back</span>
                            </a>
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
                            <p class="mb-0 fs-5">{{ $unsafeCondition->description }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 fw-bold">Created At</p>
                            <p class="mb-0">{{ $unsafeCondition->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 fw-bold">Last Updated</p>
                            <p class="mb-0">{{ $unsafeCondition->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
