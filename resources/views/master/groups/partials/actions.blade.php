{{-- resources/views/groups/partials/actions.blade.php --}}
<div class="d-flex align-items-center justify-content-end gap-1">

    {{-- View Details --}}
    <a href="{{ route('groups.show', $group) }}"
       class="btn btn-sm btn-outline-primary"
       data-bs-toggle="tooltip"
       title="View Details">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    </a>

    {{-- Manage Members --}}
    <a href="{{ route('groups.members', $group) }}"
       class="btn btn-sm btn-outline-dark"
       data-bs-toggle="tooltip"
       title="Manage Members">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
    </a>

    {{-- Edit Group --}}
    <a href="{{ route('groups.edit', $group) }}"
       class="btn btn-sm btn-outline-info"
       data-bs-toggle="tooltip"
       title="Edit Group">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        </svg>
    </a>

    {{-- Toggle Status --}}
    <button type="button"
            class="btn btn-sm {{ $group->is_active ? 'btn-outline-success' : 'btn-outline-secondary' }} toggle-status"
            data-url="{{ route('groups.toggle-status', $group) }}"
            data-bs-toggle="tooltip"
            title="{{ $group->is_active ? 'Deactivate' : 'Activate' }}">
        @if($group->is_active)
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-toggle-right">
                <rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect>
                <circle cx="16" cy="12" r="3"></circle>
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-toggle-left">
                <rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect>
                <circle cx="8" cy="12" r="3"></circle>
            </svg>
        @endif
    </button>

    {{-- Delete Group --}}
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-group"
            data-url="{{ route('groups.destroy', $group) }}"
            data-bs-toggle="tooltip"
            title="Delete Group">
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
