@extends('layouts.app')
@section('title', $campaign->title)

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <span class="badge badge-light-primary mb-2">{{ $campaign->category ?? 'Uncategorized' }}</span>
                    <h2 class="mb-3">{{ $campaign->title }}</h2>
                    <div class="d-flex justify-content-between text-muted mb-4 small">
                        <span>By <strong>{{ $campaign->creator->name ?? 'Unknown' }}</strong></span>
                        <span>{{ $campaign->published_at ? $campaign->published_at->format('d M Y') : 'Draft' }}</span>
                    </div>

                    @if($campaign->media)
                        <div class="mb-4 text-center">
                            @if($campaign->media_type == 'video')
                                <video src="{{ $campaign->media_url }}" controls class="img-fluid rounded w-100"></video>
                            @else
                                <img src="{{ $campaign->media_url }}" class="img-fluid rounded w-100" style="max-height: 500px; object-fit: cover;">
                            @endif
                        </div>
                    @endif

                    <div class="campaign-content">
                        {!! $campaign->content !!}
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5>Details</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Status</span>
                            @if($campaign->is_published)
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-secondary">Draft</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Featured</span>
                            <span>{{ $campaign->is_featured ? 'Yes' : 'No' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Views</span>
                            <span>{{ $campaign->view_count }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Read Time</span>
                            <span>{{ $campaign->read_time }} min</span>
                        </li>
                    </ul>

                    @if($campaign->summary)
                        <div class="alert alert-light-primary">
                            <strong>Summary:</strong><br>
                            {{ $campaign->summary }}
                        </div>
                    @endif

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-primary">Edit Campaign</a>
                        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">Back to List</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
