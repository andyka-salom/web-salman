{{-- resources/views/vessels/partials/actions.blade.php --}}
<div class="d-flex align-items-center justify-content-end gap-1">

    {{-- View Details & Manage Crew --}}
    <a href="{{ route('vessels.show', $vessel) }}"
       class="btn btn-sm btn-outline-primary"
       data-bs-toggle="tooltip"
       title="View & Manage Crew">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-anchor">
            <circle cx="12" cy="5" r="3"></circle>
            <line x1="12" y1="22" x2="12" y2="8"></line>
            <path d="M5 12H2a10 10 0 0 0 20 0h-3"></path>
        </svg>
    </a>

    {{-- Edit Vessel --}}
    <a href="{{ route('vessels.edit', $vessel) }}"
       class="btn btn-sm btn-outline-info"
       data-bs-toggle="tooltip"
       title="Edit Vessel">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        </svg>
    </a>
    {{-- Delete Vessel --}}
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-vessel"
            data-url="{{ route('vessels.destroy', $vessel) }}"
            data-name="{{ $vessel->name }}"
            data-bs-toggle="tooltip"
            title="Delete Vessel">
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
    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endonce
