@extends('layouts.app')
@section('title', 'User Profile')
@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Profile Card --}}
            <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-0 overflow-hidden">
                    {{-- Header Background --}}
                    <div class="profile-header-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 120px;"></div>

                    {{-- Profile Content --}}
                    <div class="text-center px-4 pb-4" style="margin-top: -60px;">
                        <div class="avatar-wrapper mb-3">
                            <img src="{{ $user->photo_path ? Storage::disk('public')->url($user->photo_path) : asset('assets/img/profile-3.jpg') }}"
                                 alt="avatar" class="profile-avatar" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 5px solid #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                        </div>

                        <h4 class="mb-1 fw-bold text-break" style="color: #1b2e4b;">{{ $user->name }}</h4>
                        <p class="text-muted mb-2" style="font-size: 14px;">{{ $user->jabatan ?? 'No Job Title' }}</p>

                        <div class="mb-3">
                            @if($user->is_active)
                                <span class="badge" style="background-color: #e7f7ef; color: #00ab55; padding: 6px 16px; border-radius: 20px; font-weight: 500;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Active Account
                                </span>
                            @else
                                <span class="badge" style="background-color: #ffe7e7; color: #e63757; padding: 6px 16px; border-radius: 20px; font-weight: 500;">
                                    <i class="bi bi-x-circle-fill me-1"></i> Inactive Account
                                </span>
                            @endif
                        </div>

                        <hr class="my-4">

                        {{-- Quick Stats --}}
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="stat-box p-3 rounded" style="background-color: #f1f5f9;">
                                    <h6 class="mb-1 fw-bold" style="color: #1b2e4b;">{{ $user->failed_login_attempts }}</h6>
                                    <small class="text-muted">Failed Attempts</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 rounded" style="background-color: #f1f5f9;">
                                    <h6 class="mb-1 fw-bold" style="color: #1b2e4b;">
                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                    </h6>
                                    <small class="text-muted">Last Login</small>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary" style="border-radius: 8px; padding: 10px;">
                                <i class="bi bi-pencil-square me-2"></i>Edit Profile
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px; padding: 10px;">
                                <i class="bi bi-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="col-xl-8 col-lg-7 col-md-12 col-sm-12 layout-spacing">

                {{-- Personal Details --}}
                <div class="widget-content widget-content-area br-8 p-4 mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-circle" style="color: white; font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="color: #1b2e4b;">Personal Details</h5>
                            <small class="text-muted">Basic information about the user</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #f8fafc; border-left: 3px solid #667eea;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-envelope" style="color: #667eea; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Email Address</small>
                                        <span class="fw-semibold text-break" style="color: #1b2e4b; font-size: 14px;">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #f8fafc; border-left: 3px solid #10b981;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-phone" style="color: #10b981; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Phone Number</small>
                                        <span class="fw-semibold" style="color: #1b2e4b; font-size: 14px;">{{ $user->phone ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #f8fafc; border-left: 3px solid #f59e0b;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-building" style="color: #f59e0b; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Company</small>
                                        <span class="fw-semibold" style="color: #1b2e4b; font-size: 14px;">{{ $user->company->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #f8fafc; border-left: 3px solid #8b5cf6;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #ede9fe; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-briefcase" style="color: #8b5cf6; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Entity Function</small>
                                        <span class="fw-semibold" style="color: #1b2e4b; font-size: 14px;">{{ $user->entityFunction->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Login Activity --}}
                <div class="widget-content widget-content-area br-8 p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-shield-lock" style="color: white; font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="color: #1b2e4b;">Login Activity</h5>
                            <small class="text-muted">Security and authentication logs</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #f8fafc; border-left: 3px solid #06b6d4;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #cffafe; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-clock-history" style="color: #06b6d4; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Last Login At</small>
                                        <span class="fw-semibold" style="color: #1b2e4b; font-size: 14px;">
                                            {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i:s') : 'Never' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #f8fafc; border-left: 3px solid #6366f1;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-globe" style="color: #6366f1; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Last Login IP</small>
                                        <span class="fw-semibold" style="color: #1b2e4b; font-size: 14px;">{{ $user->last_login_ip ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #fff7ed; border-left: 3px solid #f97316;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #ffedd5; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-exclamation-triangle" style="color: #f97316; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block mb-1">Failed Login Attempts</small>
                                        <span class="badge" style="background-color: #fed7aa; color: #c2410c; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                                            {{ $user->failed_login_attempts }} attempts
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($user->locked_until)
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="detail-card p-3 rounded" style="background-color: #fef2f2; border-left: 3px solid #ef4444; border: 1px solid #fecaca;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-circle me-3" style="width: 36px; height: 36px; background-color: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-lock-fill" style="color: #ef4444; font-size: 16px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-danger d-block mb-1 fw-semibold">
                                            <i class="bi bi-exclamation-circle me-1"></i>Account Locked Until
                                        </small>
                                        <span class="fw-bold" style="color: #dc2626; font-size: 14px;">
                                            {{ $user->locked_until->format('d M Y, H:i:s') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
/* Smooth transitions */
.detail-card, .stat-box {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.detail-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
}

.stat-box:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Button animations */
.btn {
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #653a8b 100%);
}

/* Profile avatar animation */
.profile-avatar {
    transition: all 0.3s ease;
}

.avatar-wrapper:hover .profile-avatar {
    transform: scale(1.05);
    box-shadow: 0 12px 32px rgba(0,0,0,0.2) !important;
}

/* Icon animations */
.icon-circle {
    transition: all 0.3s ease;
}

.detail-card:hover .icon-circle {
    transform: rotate(5deg) scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .layout-spacing {
        margin-bottom: 20px;
    }

    .profile-header-bg {
        height: 100px !important;
    }

    .text-center {
        margin-top: -50px !important;
    }

    .profile-avatar {
        width: 100px !important;
        height: 100px !important;
    }

    h4 {
        font-size: 1.25rem;
    }

    h5 {
        font-size: 1.1rem;
    }

    .icon-box {
        width: 36px !important;
        height: 36px !important;
    }

    .icon-box i {
        font-size: 18px !important;
    }
}

@media (max-width: 576px) {
    .widget-content-area {
        padding: 1rem !important;
    }

    .profile-header-bg {
        height: 80px !important;
    }

    .profile-avatar {
        width: 90px !important;
        height: 90px !important;
        border-width: 4px !important;
    }

    h4 {
        font-size: 1.1rem;
    }

    .detail-card, .stat-box {
        padding: 0.75rem !important;
    }

    .icon-circle {
        width: 32px !important;
        height: 32px !important;
    }

    .icon-circle i {
        font-size: 14px !important;
    }
}

/* Smooth page load animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.widget-content-area {
    animation: fadeInUp 0.5s ease-out;
}

/* Custom scrollbar for better aesthetics */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
@endsection
