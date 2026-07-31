@extends('layouts.app')

@section('title', isset($campaign) ? 'Edit Campaign' : 'Create Campaign')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .card { border: 1px solid #e0e6ed; box-shadow: none; }
    .img-preview-container {
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 6px;
        padding: 10px;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="campaignForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($campaign)) @method('PUT') @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($campaign) ? 'Edit Campaign' : 'Create New Campaign' }}</h4>
                        </div>

                        <div class="row">
                            {{-- Left Column: Content Details --}}
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $campaign->title ?? '') }}" placeholder="Enter campaign title...">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Summary</label>
                                    <textarea class="form-control" name="summary" rows="3" maxlength="1000" placeholder="Brief summary for list view...">{{ old('summary', $campaign->summary ?? '') }}</textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Maximum 1000 characters.</small>
                                        <small class="text-muted fw-bold" id="summaryCounter">0/1000</small>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Content <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="content" rows="15" placeholder="Write your full campaign content here...">{{ old('content', $campaign->content ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            {{-- Right Column: Settings & Media --}}
                            <div class="col-lg-4">
                                {{-- Settings Card --}}
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-3 text-primary">Settings</h5>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Category</label>
                                            <select class="form-select" name="category">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat }}" {{ (old('category', $campaign->category ?? '') == $cat) ? 'selected' : '' }}>{{ $cat }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Publish Date</label>
                                            <input type="datetime-local" class="form-control" name="published_at"
                                                value="{{ isset($campaign) && $campaign->published_at ? $campaign->published_at->format('Y-m-d\TH:i') : '' }}">
                                            <small class="text-muted">Leave empty to publish immediately.</small>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="mt-4">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $campaign->is_published ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="is_published">Publish to Public</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $campaign->is_featured ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="is_featured">Featured Campaign</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Media Card --}}
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-3 text-primary">Campaign Image</h5>

                                        <div class="img-preview-container mb-3 text-center" id="imagePreviewBox">
                                            {{-- Preview New Upload --}}
                                            <img id="mediaPreview" src="#" class="img-fluid rounded d-none" style="max-height: 200px;">

                                            {{-- Existing Image --}}
                                            @if(isset($campaign) && $campaign->media)
                                                <div id="existingMedia">
                                                    <img src="{{ Storage::disk('public')->url($campaign->media) }}" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                                    <p class="small text-muted mt-2 mb-0">Current Image</p>
                                                </div>
                                            @else
                                                <div id="placeholderMedia">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                    <p class="small text-muted mt-2 mb-0">No image uploaded</p>
                                                </div>
                                            @endif
                                        </div>

                                        <input type="file" class="form-control" id="media" name="media" accept="image/jpeg,image/png,image/jpg,image/webp">
                                        <small class="text-muted d-block mt-1">Max 5MB (JPG, PNG, WEBP).</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($campaign) ? 'Update Campaign' : 'Save Campaign' }}
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // 1. Inisialisasi Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 2. Summary Counter
    $('[name="summary"]').on('input', function() {
        const len = $(this).val().length;
        $('#summaryCounter').text(`${len}/1000`);
        if(len >= 1000) $('#summaryCounter').addClass('text-danger');
        else $('#summaryCounter').removeClass('text-danger');
    });

    // 3. Image Preview Logic - FIXED: 5MB limit
    $('#media').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validasi ukuran file: 5MB = 5 * 1024 * 1024 bytes
            const maxSize = 5 * 1024 * 1024; // 5MB dalam bytes

            if (file.size > maxSize) {
                Toast.fire({
                    icon: 'error',
                    title: 'File size must not exceed 5MB'
                });
                $(this).val(''); // Reset input file
                $('#mediaPreview').addClass('d-none');
                $('#existingMedia, #placeholderMedia').removeClass('d-none');
                return;
            }

            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                Toast.fire({
                    icon: 'error',
                    title: 'Only JPG, PNG, and WEBP images are allowed'
                });
                $(this).val('');
                $('#mediaPreview').addClass('d-none');
                $('#existingMedia, #placeholderMedia').removeClass('d-none');
                return;
            }

            // Preview image jika valid
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#existingMedia, #placeholderMedia').addClass('d-none');
                $('#mediaPreview').attr('src', e.target.result).removeClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // 4. Form Submission AJAX
    $('#campaignForm').on('submit', function(e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($campaign));
        const url = @json(isset($campaign) ? route('campaigns.update', $campaign->id ?? 0) : route('campaigns.store'));

        // Spoofing PUT untuk Edit Mode via AJAX
        if(isEdit) { formData.append('_method', 'PUT'); }

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui campaign...' : 'Sedang menyimpan campaign baru...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url,
            type: 'POST', // Selalu POST karena membawa FormData (file)
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(() => { window.location.href = res.redirect; }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Toast.fire({ icon: 'error', title: 'Validation Error' });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: xhr.responseJSON?.message || 'Server Error',
                    });
                }
            }
        });
    });
});
</script>
@endpush
