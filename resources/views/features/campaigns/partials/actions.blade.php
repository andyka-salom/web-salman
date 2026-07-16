<div class="d-flex justify-content-center">
    <div class="dropdown">
        <button class="btn btn-sm btn-light-dark text-dark dropdown-toggle dropdown-toggle-split"
                type="button"
                id="dropdownMenuButton{{ $campaign->id }}"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                data-bs-boundary="viewport"> {{-- KUNCI PERBAIKAN: Agar dropdown tidak terpotong --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownMenuButton{{ $campaign->id }}">
            {{-- View --}}
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('campaigns.show', $campaign) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye me-2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    View
                </a>
            </li>

            {{-- Edit --}}
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('campaigns.edit', $campaign) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 me-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    Edit
                </a>
            </li>

            <li><hr class="dropdown-divider my-1"></li>

            {{-- Toggle Publish --}}
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center py-2 toggle-action" data-url="{{ route('campaigns.toggle-publish', $campaign) }}">
                    @if($campaign->is_published)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle me-2 text-warning"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        Unpublish
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2 text-success"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Publish
                    @endif
                </button>
            </li>

            {{-- Toggle Feature --}}
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center py-2 toggle-action" data-url="{{ route('campaigns.toggle-featured', $campaign) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star me-2 {{ $campaign->is_featured ? 'text-warning fill-warning' : '' }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    {{ $campaign->is_featured ? 'Unfeature' : 'Feature' }}
                </button>
            </li>

            <li><hr class="dropdown-divider my-1"></li>

            {{-- Delete --}}
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center py-2 text-danger delete-record" data-url="{{ route('campaigns.destroy', $campaign) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 me-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Delete
                </button>
            </li>
        </ul>
    </div>
</div>
