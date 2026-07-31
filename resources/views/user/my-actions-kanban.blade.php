@extends('layouts.app')

@section('title', 'My Tasks & Action Items')

@push('styles')
    {{-- === CORE PLUGINS FROM TEMPLATE === --}}
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            /* Palette Professional */
            --primary: #4361ee; /* Equation Blue */
            --primary-hover: #2e49d6;
            --bg-app: #e0e6ed;
            --bg-card: #ffffff;
            --text-main: #3b3f5c;
            --text-muted: #888ea8;
            --border-color: #e0e6ed;

            /* Status Colors */
            --color-do: #4361ee;
            --color-progress: #e2a03f;
            --color-completed: #1abc9c;
            --color-cannot: #e7515a;
        }

        body {
            background-color: #fafafa;
            font-family: 'Nunito', sans-serif;
            color: var(--text-main);
        }

        /* --- Header & Stats --- */
        .page-header { margin-bottom: 2rem; margin-top: 1rem; }
        .page-title { font-weight: 700; color: var(--text-main); font-size: 1.5rem; }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px 0 rgba(85, 85, 85, 0.08); /* Equation Shadow */
            border: 1px solid #e0e6ed;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 0.75rem; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: #3b3f5c; }
        .stat-label { font-size: 0.9rem; color: var(--text-muted); font-weight: 600; }

        /* Stat Colors */
        .stat-do .stat-icon { background: rgba(67, 97, 238, 0.15); color: var(--color-do); }
        .stat-progress .stat-icon { background: rgba(226, 160, 63, 0.15); color: var(--color-progress); }
        .stat-completed .stat-icon { background: rgba(26, 188, 156, 0.15); color: var(--color-completed); }
        .stat-cannot .stat-icon { background: rgba(231, 81, 90, 0.15); color: var(--color-cannot); }

        /* --- Filters --- */
        .controls-bar {
            background: var(--bg-card);
            padding: 1.25rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px 0 rgba(85, 85, 85, 0.08);
            margin-bottom: 2rem;
        }
        .form-control, .form-select {
            border: 1px solid #bfc9d4;
            color: #3b3f5c;
            font-size: 15px;
            padding: 0.75rem 1rem;
            border-radius: 6px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        /* --- Task List Groups --- */
        .task-group {
            background: var(--bg-card);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px 0 rgba(85, 85, 85, 0.08);
            border: 1px solid #e0e6ed;
            overflow: hidden;
        }

        .task-group-header {
            padding: 1rem 1.5rem;
            background: #fff;
            border-bottom: 1px solid #e0e6ed;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .task-group-header:hover { background: #fbfbfb; }
        .header-title { font-weight: 700; font-size: 1.1rem; color: #3b3f5c; }
        .header-badge { font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 4px; color: white; }

        .group-do .header-badge { background: var(--color-do); box-shadow: 0 3px 9px 0 rgba(67, 97, 238, 0.35); }
        .group-in-progress .header-badge { background: var(--color-progress); box-shadow: 0 3px 9px 0 rgba(226, 160, 63, 0.35); }
        .group-completed .header-badge { background: var(--color-completed); box-shadow: 0 3px 9px 0 rgba(26, 188, 156, 0.35); }
        .group-cannot-do .header-badge { background: var(--color-cannot); box-shadow: 0 3px 9px 0 rgba(231, 81, 90, 0.35); }

        /* --- Task Item --- */
        .task-item {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e0e6ed;
            background: white;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .task-item:last-child { border-bottom: none; }
        .task-item:hover { background: #fafafa; transform: scale(1.002); }

        .task-item::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
        }
        .task-item.priority-Tinggi::before { background: #e7515a; }
        .task-item.priority-Sedang::before { background: #e2a03f; }
        .task-item.priority-Rendah::before { background: #1abc9c; }

        .task-title { font-weight: 600; font-size: 15px; color: #3b3f5c; margin-bottom: 0.3rem; }
        .meta-tag { display: inline-flex; align-items: center; gap: 4px; background: #ebedf2; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #515365; margin-right: 8px; }
        .meta-tag i { color: #888ea8; }
        .meta-tag a { color: var(--primary); text-decoration: none; font-weight: 700; }

        .due-date { font-weight: 700; font-size: 14px; color: #3b3f5c; display: flex; align-items: center; justify-content: flex-end; gap: 5px; }
        .due-date.overdue { color: #e7515a; }
        .due-relative { font-size: 12px; color: #888ea8; text-align: right; }

        /* --- EQUATION MODAL STYLES REPLICATION --- */
        .modal-content {
            border: none;
            border-radius: 6px;
            box-shadow: 0 0 20px 0 rgba(0,0,0,0.1);
            background: #fff;
        }

        .modal-header {
            border-bottom: 1px solid #e0e6ed;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }

        .modal-title {
            font-weight: 600;
            font-size: 18px;
            color: #3b3f5c;
            margin: 0;
        }

        /* Tombol Close SVG dari Template */
        .btn-close-custom {
            background: transparent;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #888ea8;
            transition: color 0.2s;
        }
        .btn-close-custom:hover { color: #e7515a; }
        .btn-close-custom svg { width: 20px; height: 20px; stroke-width: 2; }

        .modal-body {
            padding: 1.5rem 1.5rem;
            background: #fdfdfd;
        }

        .modal-footer {
            border-top: 1px solid #e0e6ed;
            padding: 1rem 1.25rem;
            background: #fff;
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Custom Buttons from Template */
        .btn-light-dark {
            background: #e0e6ed;
            color: #3b3f5c;
            border: 1px solid transparent;
            box-shadow: none;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 6px;
            transition: .3s;
        }
        .btn-light-dark:hover { background: #bfc9d4; color: #000; }

        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
            box-shadow: 0 4px 6px 0 rgba(67, 97, 238, .3);
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 6px;
        }
        .btn-primary:hover {
            background-color: #2e49d6;
            border-color: #2e49d6;
            box-shadow: 0 4px 12px 0 rgba(67, 97, 238, .5);
        }

        /* Internal Modal Grid (Split Layout) */
        .modal-split-layout { display: flex; gap: 2rem; }
        .modal-left { flex: 3; }
        .modal-right { flex: 2; background: #fff; border: 1px solid #e0e6ed; border-radius: 6px; padding: 1.25rem; height: fit-content; }

        .info-label { font-size: 11px; font-weight: 700; color: #888ea8; text-transform: uppercase; margin-bottom: 5px; display: block; letter-spacing: 0.5px; }
        .info-value { font-size: 14px; color: #3b3f5c; font-weight: 600; margin-bottom: 15px; display: block; }
        .info-value i { color: var(--primary); margin-right: 5px; }

        /* Photos */
        .photo-gallery { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px; }
        .photo-thumb { width: 70px; height: 70px; border-radius: 6px; overflow: hidden; position: relative; border: 1px solid #e0e6ed; }
        .photo-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .btn-del-photo { position: absolute; top: 2px; right: 2px; background: rgba(231, 81, 90, 0.9); color: white; border: none; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .btn-del-photo svg { width: 12px; height: 12px; }

        /* --- MOBILE RESPONSIVE FIX (Keeping the scroll functionality) --- */
        @media (max-width: 991px) {
            .modal-split-layout { flex-direction: column; gap: 1rem; }
            .modal-right { width: 100%; border: none; padding: 0; background: transparent; }

            /* Override Centered Dialog behavior on Mobile to allow full scroll */
            .modal-dialog-centered {
                align-items: flex-start !important; /* Start from top */
                min-height: calc(100% - 1rem);
                margin: 0.5rem;
            }

            .modal-content {
                max-height: calc(100vh - 1rem); /* Full height */
                display: flex;
                flex-direction: column;
            }

            .modal-body {
                overflow-y: auto; /* Scrollable body */
                padding: 1rem;
            }

            .task-item { flex-direction: column; align-items: flex-start; }
            .task-side { width: 100%; display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e0e6ed; }
        }

        /* Animation overrides if needed */
        .animated { animation-duration: 0.3s; }
    </style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;

    if (!function_exists('renderTaskRow')) {
        function renderTaskRow($item) {
            if (!$item || !isset($item->target_date)) return '';

            $isOverdue = $item->target_date->isPast() && !in_array($item->status, ['Completed', 'Cannot Do']);

            $catName = optional($item->actionCategory)->name ?? 'Uncategorized';
            $priority = 'Rendah';
            if (Str::contains($catName, ['Immediate', '1', 'Tinggi', 'High'])) $priority = 'Tinggi';
            elseif (Str::contains($catName, ['Short', 'Medium', '2', '3', 'Sedang'])) $priority = 'Sedang';

            $searchTerm = Str::lower($item->description . ' ' . optional($item->cermatReport)->report_number . ' ' . optional(optional($item->cermatReport)->area)->name);

            $daysDiff = now()->diffInDays($item->target_date, false);
            if ($isOverdue) {
                $daysPast = abs($daysDiff);
                $relText = $daysPast == 0 ? 'Due Today' : "{$daysPast} days overdue";
                $dateClass = 'overdue';
            } else {
                $relText = $daysDiff == 0 ? 'Due Today' : ($daysDiff == 1 ? 'Due Tomorrow' : "Due in {$daysDiff} days");
                $dateClass = '';
            }

            $reportUrl = route('cermat.reports.show', $item->cermat_report_id);
            $areaName = optional(optional($item->cermatReport)->area)->name ?? '-';
            $reportNum = optional($item->cermatReport)->report_number ?? '-';

            return "
            <div class='task-item priority-{$priority}'
                 onclick='openTaskModal(this)'
                 data-id='{$item->id}'
                 data-status='".Str::slug($item->status)."'
                 data-search='{$searchTerm}'
                 data-prio='{$priority}'
                 data-date='{$item->target_date->toDateString()}'>

                <div class='task-main'>
                    <div class='task-title'>{$item->description}</div>
                    <div class='task-meta'>
                        <span class='meta-tag'>
                            <i class='bi bi-hash'></i> <a href='{$reportUrl}' onclick='event.stopPropagation()'>{$reportNum}</a>
                        </span>
                        <span class='meta-tag'>
                            <i class='bi bi-geo-alt'></i> {$areaName}
                        </span>
                        <span class='meta-tag d-none d-sm-inline-flex'>
                            <i class='bi bi-tag'></i> ".Str::limit($catName, 20)."
                        </span>
                    </div>
                </div>

                <div class='task-side'>
                    <div class='due-date {$dateClass}'>
                        <span>{$item->target_date->format('d M Y')}</span>
                        <i class='bi bi-calendar-event'></i>
                    </div>
                    <div class='due-relative'>{$relText}</div>
                </div>
            </div>";
        }
    }
@endphp

<div class="container-fluid">

    {{-- Header --}}
    <div class="page-header">
        <h3 class="page-title">My Action Items</h3>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-container">
        <div class="stat-card stat-do">
            <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
            <div class="stat-value" id="stat-do">0</div>
            <div class="stat-label">To Do</div>
        </div>
        <div class="stat-card stat-progress">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value" id="stat-progress">0</div>
            <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-value" id="stat-completed">0</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card stat-cannot">
            <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="stat-value" id="stat-cannot">0</div>
            <div class="stat-label">Cannot Do</div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="controls-bar">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <input type="text" id="search-input" class="form-control" placeholder="Search report #, description, area...">
            </div>
            <div class="col-6 col-lg-3">
                <select id="filter-priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="Tinggi">High Priority</option>
                    <option value="Sedang">Medium Priority</option>
                    <option value="Rendah">Low Priority</option>
                </select>
            </div>
            <div class="col-6 col-lg-3">
                <select id="filter-date" class="form-select">
                    <option value="">All Dates</option>
                    <option value="overdue">Overdue</option>
                    <option value="today">Due Today</option>
                    <option value="week">This Week</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Task List --}}
    <div class="task-list-wrapper">
        @foreach($statuses as $statusKey => $statusName)
            @php
                $slug = Str::slug($statusKey);
                $groupClass = "group-{$slug}";
                $items = $groupedItems->get($statusKey, collect());
            @endphp

            <div class="task-group {{ $groupClass }}" data-status-key="{{ $statusKey }}">
                <div class="task-group-header" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $slug }}">
                    <div class="header-title">
                        {{ $statusName }}
                    </div>
                    <span class="header-badge" id="count-{{ $slug }}">{{ $items->count() }}</span>
                </div>

                <div id="collapse-{{ $slug }}" class="collapse show">
                    <div class="list-container" id="container-{{ $slug }}">
                        @forelse($items as $item)
                            {!! renderTaskRow($item) !!}
                        @empty
                            {{-- Placeholder via JS --}}
                        @endforelse
                    </div>
                    <div class="p-4 text-center text-muted empty-placeholder" id="empty-{{ $slug }}" style="{{ $items->count() > 0 ? 'display:none;' : '' }}">
                        <p class="mb-0">No tasks in this group.</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- === MODAL UPDATE TASK (EQUATION STYLE) === --}}
<!-- Perhatikan class: animated zoomInUp custo-zoomInUp -->
<div class="modal fade animated zoomInUp custo-zoomInUp" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">

            {{-- HEADER: Menggunakan Ikon SVG Feather-X --}}
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Task Details</h5>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form id="updateForm" enctype="multipart/form-data">
                @csrf @method('PUT')
                <input type="hidden" id="action_id">

                {{-- BODY --}}
                <div class="modal-body">
                    <div class="modal-split-layout">

                        {{-- Left Column: Form --}}
                        <div class="modal-left">
                            <div class="alert alert-light-primary border-0 mb-4" style="background: #eef2ff; color: #4361ee; padding: 15px; border-radius: 6px;">
                                <span id="m_desc" style="font-weight: 600; font-size: 15px; display: block;"></span>
                            </div>

                            <div class="form-group mb-4">
                                <label class="info-label">Update Status <span class="text-danger">*</span></label>
                                <select id="m_status" name="status" class="form-select">
                                    @foreach($statuses as $key => $val)
                                        <option value="{{ $key }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="info-label">Completion Notes</label>
                                <textarea id="m_notes" name="completion_notes" class="form-control" rows="4" placeholder="Describe actions taken..."></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="info-label">Proof Photos</label>
                                <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                            </div>

                            <div id="m_gallery_container" style="display:none">
                                <label class="info-label">Existing Photos</label>
                                <div id="m_gallery" class="photo-gallery"></div>
                            </div>
                        </div>

                        {{-- Right Column: Meta Info --}}
                        <div class="modal-right">
                            <h6 style="font-weight: 700; color: #3b3f5c; margin-bottom: 20px; font-size: 16px;">Information</h6>

                            <span class="info-label">Report Number</span>
                            <span class="info-value" id="m_report_num"></span>

                            <span class="info-label">Area / Location</span>
                            <span class="info-value"><i class="bi bi-geo-alt"></i> <span id="m_area"></span></span>

                            <span class="info-label">Category</span>
                            <span class="info-value"><i class="bi bi-tag"></i> <span id="m_cat"></span></span>

                            <span class="info-label">Priority</span>
                            <span class="info-value"><i class="bi bi-flag"></i> <span id="m_prio"></span></span>

                            <hr style="border-top: 1px dashed #e0e6ed;">

                            <span class="info-label">Target Date</span>
                            <span class="info-value"><i class="bi bi-calendar-event"></i> <span id="m_target"></span></span>

                            <span class="info-label">Reporter</span>
                            <span class="info-value"><i class="bi bi-person"></i> <span id="m_reporter"></span></span>

                            <div id="m_completed_row" style="display:none; margin-top: 15px;">
                                <span class="info-label text-success">Completed Date</span>
                                <span class="info-value text-success"><i class="bi bi-check-circle"></i> <span id="m_completed_at"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-dark" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/isSameOrAfter.js"></script>
<script>
    dayjs.extend(window.dayjs_plugin_isSameOrAfter);

    const STATUS_MAP = {
        'Do': 'do',
        'In Progress': 'in-progress',
        'Completed': 'completed',
        'Cannot Do': 'cannot-do'
    };

    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // --- Filter Logic ---
        function filterTasks() {
            const term = $('#search-input').val().toLowerCase();
            const prio = $('#filter-priority').val();
            const dateFilter = $('#filter-date').val();
            const today = dayjs().startOf('day');

            $('.task-item').each(function() {
                const el = $(this);
                const txt = el.data('search');
                const p = el.data('prio');
                const d = dayjs(el.data('date'));
                const status = el.data('status');

                let show = true;

                if (term && !txt.includes(term)) show = false;
                if (show && prio && p !== prio) show = false;
                if (show && dateFilter) {
                    if (dateFilter === 'overdue') {
                        if (d.isSameOrAfter(today) || status === 'completed' || status === 'cannot-do') show = false;
                    }
                    else if (dateFilter === 'today' && !d.isSame(today, 'day')) show = false;
                    else if (dateFilter === 'week') {
                        const nextWeek = dayjs().add(7, 'day');
                        if (!d.isAfter(today) || !d.isBefore(nextWeek)) show = false;
                    }
                }
                show ? el.fadeIn(200) : el.hide();
            });
            setTimeout(updateCounters, 250);
        }

        function updateCounters() {
            // Stats
            $('#stat-do').text($('.group-do .task-item:visible').length);
            $('#stat-progress').text($('.group-in-progress .task-item:visible').length);
            $('#stat-completed').text($('.group-completed .task-item:visible').length);
            $('#stat-cannot').text($('.group-cannot-do .task-item:visible').length);

            // Groups
            $('.task-group').each(function() {
                const visibleCount = $(this).find('.task-item:visible').length;
                $(this).find('.header-badge').text(visibleCount);
                const placeholder = $(this).find('.empty-placeholder');
                visibleCount === 0 ? placeholder.show() : placeholder.hide();
            });
        }

        $('#search-input, #filter-priority, #filter-date').on('keyup change', filterTasks);

        // --- Open Modal ---
        window.openTaskModal = function(el) {
            const id = $(el).data('id');
            const url = `{{ url('user/my-actions') }}/${id}/edit`;

            $('#updateForm')[0].reset();
            $('#m_gallery').empty();
            $('#m_gallery_container').hide();
            $('#action_id').val(id);

            Swal.fire({
                title: 'Loading...', allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.get(url, function(data) {
                Swal.close();

                $('#m_desc').text(data.description);
                $('#m_status').val(data.status);
                $('#m_notes').val(data.completion_notes);

                const r = data.cermat_report;
                const rUrl = `{{ url('cermat/reports') }}/${r.id}`;
                $('#m_report_num').html(`<a href="${rUrl}" target="_blank" style="color:#4361ee">#${r.report_number}</a>`);
                $('#m_area').text(r.area?.name || '-');
                $('#m_cat').text(data.action_category?.name || '-');
                $('#m_prio').text(data.action_category?.name.includes('1') ? 'High' : 'Normal');
                $('#m_target').text(dayjs(data.target_date).format('DD MMM YYYY'));
                $('#m_reporter').text(r.reporter?.name || '-');

                if(data.completed_at) {
                    $('#m_completed_row').show();
                    $('#m_completed_at').text(dayjs(data.completed_at).format('DD MMM YYYY'));
                } else {
                    $('#m_completed_row').hide();
                }

                if(data.photos && data.photos.length > 0) {
                    $('#m_gallery_container').show();
                    data.photos.forEach(p => {
                        const src = `{{ rtrim(Storage::disk('public')->url('/'), '/') }}/${p.file_path}`;
                        $('#m_gallery').append(`
                            <div class="photo-thumb">
                                <a href="${src}" target="_blank"><img src="${src}"></a>
                                <button type="button" class="btn-del-photo" onclick="deletePhoto(${p.id}, this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        `);
                    });
                }

                $('#updateModal').modal('show');
            }).fail(() => {
                Swal.close();
                Swal.fire('Error', 'Failed to load task data', 'error');
            });
        };

        // --- Save Form ---
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();

            if($('#m_status').val() === 'Completed' && !$('#m_notes').val().trim()) {
                Swal.fire('Validation', 'Notes required for completion.', 'warning');
                return;
            }

            const formData = new FormData(this);
            const id = $('#action_id').val();
            const btn = $('#btnSave');

            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: `{{ url('user/my-actions') }}/${id}`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#updateModal').modal('hide');
                    Swal.fire({
                        icon: 'success', title: 'Saved', toast: true,
                        position: 'top-end', showConfirmButton: false, timer: 2000
                    });

                    const oldCard = $(`.task-item[data-id="${id}"]`);
                    oldCard.fadeOut(200, function() { $(this).remove(); });

                    const newGroupSlug = STATUS_MAP[res.item_status || $('#m_status').val()];
                    if(res.html) {
                         $(`#container-${newGroupSlug}`).prepend(res.html);
                         filterTasks();
                    } else {
                        setTimeout(() => location.reload(), 500);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Update failed.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save Changes');
                }
            });
        });

        // --- Delete Photo ---
        window.deletePhoto = function(photoId, btn) {
            Swal.fire({
                title: 'Delete?', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#e7515a',
                confirmButtonText: 'Yes'
            }).then((res) => {
                if(res.isConfirmed) {
                    $.ajax({
                        url: `{{ url('user/my-actions/photos') }}/${photoId}`,
                        type: 'DELETE',
                        success: function() {
                            $(btn).parent().fadeOut(300, function(){ $(this).remove(); });
                        }
                    });
                }
            });
        };

        filterTasks();
    });
</script>
@endpush
