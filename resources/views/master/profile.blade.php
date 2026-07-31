@extends('layouts.app')
@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    /*
       PENTING: Semua CSS diawali dengan #custom-profile-page
       agar tidak merusak Sidebar atau elemen layout lainnya.
    */

    /* Card Styling Khusus Halaman Ini */
    #custom-profile-page .widget-content-area {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.05);
        border: 1px solid #e0e6ed;
        padding: 25px;
        margin-bottom: 24px;
    }

    #custom-profile-page .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #3b3f5c;
        border-bottom: 1px solid #e0e6ed;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    /* Profile Image */
    #custom-profile-page .profile-img-box {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto;
    }
    #custom-profile-page .profile-img-element {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    #custom-profile-page .profile-img-overlay {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #4361ee;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(67, 97, 238, 0.3);
        z-index: 5;
    }
    #custom-profile-page .profile-img-overlay:hover {
        transform: scale(1.1);
        background: #304ffe;
    }
    #custom-profile-page .profile-img-overlay svg { color: #fff; width: 20px; height: 20px; }

    /* Form Inputs */
    #custom-profile-page .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #515365;
        margin-bottom: 6px;
    }
    #custom-profile-page .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e6ed;
        font-size: 14px;
        color: #3b3f5c;
    }
    #custom-profile-page .form-control:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.15);
    }
    #custom-profile-page .form-control[readonly] {
        background-color: #f6f8fa;
        color: #888ea8;
        border-color: #f1f2f3;
    }

    /* List Group Fix */
    #custom-profile-page .list-group-item {
        border: none;
        border-bottom: 1px solid #f1f2f3;
        padding: 12px 0;
        font-size: 14px;
        background: transparent;
    }
    #custom-profile-page .list-group-item:last-child { border-bottom: none; }

    /* Button Fix */
    #custom-profile-page .btn-save-profile {
        background-color: #4361ee;
        border-color: #4361ee;
        color: white;
        box-shadow: 0 4px 6px rgba(67, 97, 238, 0.2);
        padding: 10px 25px;
        font-weight: 600;
    }
    #custom-profile-page .btn-save-profile:hover {
        background-color: #304ffe;
        box-shadow: 0 6px 10px rgba(67, 97, 238, 0.3);
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- Wrapper ID Unik untuk mencegah konflik CSS dengan Sidebar --}}
        <div id="custom-profile-page" class="row layout-top-spacing">

            {{-- COLUMN LEFT: GENERAL INFORMATION --}}
            <div class="col-xl-8 col-lg-6 col-md-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area">
                    <h5 class="section-title">General Information</h5>

                    <form id="general-info-form" enctype="multipart/form-data">
                        @csrf

                        <!-- Profile Header -->
                        <div class="d-flex flex-column align-items-center mb-5">
                            <div class="profile-img-box">
                                <img id="profilePreview"
                                     src="{{ $user->photo_path ? Storage::disk('public')->url($user->photo_path) : asset('assets/img/profile-3.jpg') }}"
                                     class="profile-img-element" alt="avatar">
                                <label for="photoInput" class="profile-img-overlay" title="Upload Photo">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                </label>
                                <input type="file" id="photoInput" name="photo" class="d-none" accept="image/*">
                            </div>
                            <h4 class="mt-3 mb-1" style="font-weight: 700;">{{ $user->name }}</h4>
                            <p class="text-primary mb-0" style="font-weight: 500;">{{ $user->jabatan ?? 'No Job Title' }}</p>
                        </div>

                        <!-- Editable Fields -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Job Title</label>
                                <input type="text" class="form-control" name="jabatan" value="{{ $user->jabatan }}" placeholder="e.g. Manager">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="phone" value="{{ $user->phone }}" placeholder="+62...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                            </div>
                        </div>

                        <!-- Read-Only Section (System Info) -->
                        <div class="p-3 mb-4 rounded" style="background-color: #f9fbfd; border: 1px dashed #e0e6ed;">
                            <h6 class="mb-3 text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Organization Info</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label text-muted">Company</label>
                                    <input type="text" class="form-control" value="{{ $user->company->name ?? '-' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Entity Function</label>
                                    <input type="text" class="form-control" value="{{ $user->entityFunction->name ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-save-profile px-4" id="btnUpdateProfile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- COLUMN RIGHT: SECURITY & STATUS --}}
            <div class="col-xl-4 col-lg-6 col-md-12 col-sm-12 layout-spacing">

                {{-- Account Status Widget --}}
                <div class="widget-content widget-content-area mb-4">
                    <h5 class="section-title">Account Status</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Status</span>
                            <span class="badge badge-light-success text-success" style="font-size: 12px; padding: 5px 10px;">Active</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Role</span>
                            <span class="fw-bold text-primary">{{ $user->roles->pluck('name')->join(', ') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Member Since</span>
                            <span>{{ $user->created_at->format('d M Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Last Login</span>
                            <span>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Change Password Widget --}}
                <div class="widget-content widget-content-area">
                    <h5 class="section-title">Security</h5>
                    <p class="text-muted mb-4" style="font-size: 13px;">Ensure your account is using a long, random password to stay secure.</p>

                    <form id="password-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required placeholder="••••••••">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required placeholder="••••••••">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required placeholder="••••••••">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-secondary" id="btnUpdatePassword">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock me-1"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Update Password
                            </button>
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

    // 1. Photo Preview
    $('#photoInput').change(function(){
        const file = this.files[0];
        if (file){
            let reader = new FileReader();
            reader.onload = function(event){
                $('#profilePreview').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // 2. Update General Info
    $('#general-info-form').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let btn = $('#btnUpdateProfile');
        let originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        $('.is-invalid').removeClass('is-invalid');

        $.ajax({
            url: "{{ route('profile.update') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if(res.success) {
                    const toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        padding: '2em',
                    });
                    toast.fire({
                        icon: 'success',
                        title: res.message,
                        padding: '2em',
                    });

                    // Optional: Update sidebar image if class exists
                    // $('.sidebar-user-img').attr('src', res.photo_url);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);

                if(xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, val) {
                        $(`[name="${key}"]`).addClass('is-invalid');
                        Swal.fire({ title: 'Validation Error', text: val[0], icon: 'error', confirmButtonColor: '#4361ee' });
                        return false;
                    });
                } else {
                    Swal.fire({ title: 'Error', text: xhr.responseJSON.message || 'System Error', icon: 'error', confirmButtonColor: '#4361ee' });
                }
            }
        });
    });

    // 3. Update Password
    $('#password-form').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let btn = $('#btnUpdatePassword');
        let originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
        $('.is-invalid').removeClass('is-invalid');

        $.ajax({
            url: "{{ route('profile.password') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if(res.success) {
                    $('#password-form')[0].reset();
                    Swal.fire({ title: 'Success', text: res.message, icon: 'success', confirmButtonColor: '#4361ee' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);

                if(xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, val) {
                        $(`[name="${key}"]`).addClass('is-invalid');
                        Swal.fire({ title: 'Check Input', text: val[0], icon: 'warning', confirmButtonColor: '#e2a03f' });
                        return false;
                    });
                } else {
                    Swal.fire({ title: 'Error', text: xhr.responseJSON.message || 'System Error', icon: 'error', confirmButtonColor: '#4361ee' });
                }
            }
        });
    });

});
</script>
@endpush
