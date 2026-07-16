{{-- resources/views/unsafe-acts/partials/actions.blade.php --}}
<div class="d-flex align-items-center justify-content-end gap-1">

    {{-- View Details --}}
    <a href="{{ route('unsafe-acts.show', $unsafeAct) }}"
       class="btn btn-sm btn-outline-primary"
       data-bs-toggle="tooltip"
       title="View Details">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    </a>

    {{-- Edit Unsafe Act --}}
    <a href="{{ route('unsafe-acts.edit', $unsafeAct) }}"
       class="btn btn-sm btn-outline-info"
       data-bs-toggle="tooltip"
       title="Edit Unsafe Act">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        </svg>
    </a>

    {{-- Reset Usage Count --}}
    @if($unsafeAct->usage_count > 0)
    <button type="button"
            class="btn btn-sm btn-outline-warning reset-usage"
            data-url="{{ route('unsafe-acts.reset-usage', $unsafeAct) }}"
            data-bs-toggle="tooltip"
            title="Reset Usage Count ({{ $unsafeAct->usage_count }} times used)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw">
            <polyline points="23 4 23 10 17 10"></polyline>
            <polyline points="1 20 1 14 7 14"></polyline>
            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
        </svg>
    </button>
    @endif

    {{-- Delete Unsafe Act --}}
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-unsafe-act"
            data-url="{{ route('unsafe-acts.destroy', $unsafeAct) }}"
            data-bs-toggle="tooltip"
            @if($unsafeAct->usage_count > 0)
                disabled
                title="Cannot delete: Used in reports"
            @else
                title="Delete Unsafe Act"
            @endif>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            <line x1="10" y1="11" x2="10" y2="17"></line>
            <line x1="14" y1="11" x2="14" y2="17"></line>
        </svg>
    </button>

</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endonce
