{{-- resources/views/features/campaign-salman/partials/actions.blade.php --}}
<div class="d-flex align-items-center justify-content-end gap-2">

    {{-- Dropdown Menu --}}
    <div class="dropdown">
        <button class="btn btn-sm btn-light-dark text-dark dropdown-toggle dropdown-toggle-split"
                type="button"
                id="dropdownMenuButton{{ $row->id }}"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                data-bs-auto-close="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical">
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="12" cy="5" r="1"></circle>
                <circle cx="12" cy="19" r="1"></circle>
            </svg>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
            aria-labelledby="dropdownMenuButton{{ $row->id }}"
            style="min-width: 200px;">

            {{-- View Details --}}
            <li>
                <a class="dropdown-item d-flex align-items-center py-2"
                   href="{{ route('campaign-salman.show', $row->id) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye me-2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>View Details</span>
                </a>
            </li>

            {{-- Edit Report --}}
            <li>
                <a class="dropdown-item d-flex align-items-center py-2"
                   href="{{ route('campaign-salman.edit', $row->id) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 me-2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    <span>Edit Report</span>
                </a>
            </li>

            {{-- Divider --}}
            <li><hr class="dropdown-divider my-1"></li>

            {{-- Preview PDF --}}
            <li>
                <a class="dropdown-item d-flex align-items-center py-2"
                   href="{{ route('campaign-salman.preview', $row->id) }}"
                   target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text me-2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Preview PDF</span>
                </a>
            </li>

            {{-- Download PDF --}}
            <li>
                <a class="dropdown-item d-flex align-items-center py-2"
                   href="{{ route('campaign-salman.download', $row->id) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download me-2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Download PDF</span>
                </a>
            </li>

            {{-- Divider --}}
            <li><hr class="dropdown-divider my-1"></li>

            {{-- Delete Report --}}
            <li>
                <button type="button"
                        class="dropdown-item d-flex align-items-center py-2 text-danger delete-record"
                        data-url="{{ route('campaign-salman.destroy', $row->id) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 me-2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                    <span>Delete Report</span>
                </button>
            </li>

        </ul>
    </div>

</div>

{{-- Initialize tooltips --}}
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Clean up tooltips when dropdown is shown
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        dropdown.addEventListener('show.bs.dropdown', function () {
            tooltipList.forEach(function(tooltip) {
                tooltip.hide();
            });
        });
    });
});
</script>
@endpush
@endonce
