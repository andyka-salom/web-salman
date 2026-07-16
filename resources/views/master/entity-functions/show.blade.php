@extends('layouts.app')
@section('title', 'Function Details')

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Function Info --}}
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="mb-0">{{ $entityFunction->name }}</h3>
                            <span class="badge badge-light-primary mt-2">{{ $entityFunction->code }}</span>
                        </div>
                        @if($entityFunction->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>

                    <p><strong>Description:</strong> {{ $entityFunction->description ?? '-' }}</p>
                    <p><strong>Parent Function:</strong>
                        @if($entityFunction->parent)
                            <a href="{{ route('entity-functions.show', $entityFunction->parent_id) }}">{{ $entityFunction->parent->name }}</a>
                        @else
                            <span class="text-muted">Root Level</span>
                        @endif
                    </p>
                    <p><strong>Level:</strong> {{ $entityFunction->level }}</p>

                    <div class="mt-4">
                        <a href="{{ route('entity-functions.edit', $entityFunction) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('entity-functions.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>

            {{-- Stats / Children --}}
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5 class="mb-3">Sub-Functions ({{ $entityFunction->children->count() }})</h5>
                    @if($entityFunction->children->count() > 0)
                        <ul class="list-group list-group-flush mb-4">
                            @foreach($entityFunction->children as $child)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <a href="{{ route('entity-functions.show', $child) }}">{{ $child->name }}</a>
                                    <span class="badge badge-light-secondary">{{ $child->code }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No sub-functions found.</p>
                    @endif

                    <h5 class="mb-3">Assigned Users ({{ $entityFunction->users->count() }})</h5>
                    @if($entityFunction->users->count() > 0)
                        <div class="avatar-group">
                            @foreach($entityFunction->users->take(5) as $u)
                                <div class="avatar avatar-sm" data-bs-toggle="tooltip" title="{{ $u->name }}">
                                    <span class="avatar-title rounded-circle bg-primary">{{ substr($u->name, 0, 2) }}</span>
                                </div>
                            @endforeach
                            @if($entityFunction->users->count() > 5)
                                <div class="avatar avatar-sm">
                                    <span class="avatar-title rounded-circle bg-secondary">+{{ $entityFunction->users->count() - 5 }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">No users assigned directly to this function.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
