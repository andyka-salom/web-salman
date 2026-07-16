@push('styles')
    {{-- Existing CSS links remain unchanged --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/css/dark/table/datatable/dt_global_style.css') }}">

    <link href="{{ asset('src/plugins/src/animate/animate.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <link href="{{ asset('src/plugins/css/light/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/plugins/css/dark/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet" type="text/css" />

    <style>
        /* ======================================= */
        /* 1. Modal Container & Responsiveness (FIXED SCROLLING) */
        /* ======================================= */

        #updateActionModal .modal-dialog {
            max-width: 1100px;
            margin: 1.75rem auto;
        }

        #updateActionModal .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        #updateActionModal .modal-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
        }

        #updateActionModal .modal-body {
            padding: 2rem;
            background: #f8fafc;
        }

        /* --------------------------------------- */
        /* Mobile/Tablet Adjustments (Max 991px) */
        /* --------------------------------------- */
        @media (max-width: 991px) {
            /*
               Jika modal-fullscreen-sm-down tidak bekerja,
               kita paksa modal content mengambil 100% tinggi layar
            */
            #updateActionModal .modal-dialog:not(.modal-fullscreen-sm-down) {
                max-width: 95%;
                width: 95%;
                margin: 0.5rem auto;
            }

            #updateActionModal .modal-content:not(.modal-fullscreen-sm-down) {
                /* Hitung tinggi konten agar muat di viewport dan dapat di-scroll */
                height: calc(100vh - 1rem);
            }

            #updateActionModal .modal-body {
                padding: 1rem;
            }

            /* Fix border when columns stack */
            .modal-body .border-end {
                border-right: none !important;
                border-bottom: 1px solid #e2e8f0;
                padding-bottom: 1.5rem !important;
                margin-bottom: 1.5rem;
            }

            .col-lg-5.ps-lg-4 {
                padding-left: 1rem !important;
            }
        }

        /* ======================================= */
        /* 2. Content Styling (Retained for professionalism) */
        /* ======================================= */

        #action_description {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.6;
            padding: 1rem 1.25rem;
            background: white;
            border-radius: 6px;
            border-left: 4px solid #2563eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        #updateActionModal .form-select,
        #updateActionModal .form-control {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            background: white;
        }

        #updateActionModal textarea.form-control {
            min-height: 120px;
        }

        .photo-proof-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .info-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .meta-info-list li i {
            color: #2563eb;
            flex-shrink: 0;
            width: 20px;
        }

        #activity-log p {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border-radius: 6px;
            border-left: 3px solid #cbd5e1;
        }

    </style>
@endpush

<div class="modal fade" id="updateActionModal" tabindex="-1" aria-labelledby="updateActionModalLabel" aria-hidden="true">
    {{-- PERBAIKAN DI SINI: Tambahkan modal-fullscreen-sm-down --}}
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateActionModalLabel">
                    <i class="bi bi-clipboard-check me-2"></i>
                    <span>Task Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateActionForm" name="updateActionForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="action_item_id">
                <div class="modal-body">
                    <div class="row">
                        {{-- Left Column: Update Form --}}
                        <div class="col-lg-7 border-end pe-lg-4">

                            <p id="action_description"></p>

                            <div class="mb-4">
                                <label for="status" class="form-label">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Update Status
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status" class="form-select" required>
                                    @foreach($statuses as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="completion_notes" class="form-label">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    Completion Notes
                                </label>
                                <textarea name="completion_notes" id="completion_notes" class="form-control"
                                          rows="4" placeholder="Describe the actions you've taken..."></textarea>
                                <small class="form-text">Provide detailed notes about task resolution or status change.</small>
                            </div>

                            <div class="mb-4">
                                <label for="photos" class="form-label">
                                    <i class="bi bi-image me-1"></i>
                                    Upload Proof Photos (Optional)
                                </label>
                                <input type="file" name="photos[]" id="photos" class="form-control"
                                       multiple accept="image/*">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">
                                    <i class="bi bi-images me-1"></i>
                                    Current Photo Proofs
                                </label>
                                <div id="photo-proof-gallery" class="photo-proof-gallery"></div>
                            </div>
                        </div>

                        {{-- Right Column: Task Metadata --}}
                        <div class="col-lg-5 ps-lg-4 mt-lg-0 mt-4">

                            <div class="info-section mb-4">
                                <h6>
                                    <i class="bi bi-info-circle me-1"></i>
                                    Task Information
                                </h6>
                                <ul class="meta-info-list">
                                    <li>
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                        <span id="modal_report_link"></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span id="modal_area"></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-tags-fill"></i>
                                        <span id="modal_category"></span>
                                    </li>
                                    <li class="fw-bold">
                                        <i class="bi bi-flag-fill"></i>
                                        <span id="modal_priority"></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-calendar-event-fill"></i>
                                        <span id="modal_target_date"></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-person-fill"></i>
                                        <span id="modal_reporter"></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="info-section">
                                <h6>
                                    <i class="bi bi-clock-history me-1"></i>
                                    Activity Log
                                </h6>
                                <div id="activity-log">
                                    <p><strong>Created:</strong> <span id="modal_created_at"></span></p>
                                    <p id="log_completed_at" style="display: none;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
