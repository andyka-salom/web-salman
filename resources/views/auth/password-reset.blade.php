@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')

{{-- Style Konsisten --}}
<style>
    .auth-bg {
        background-image: url('{{ asset('phm.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .auth-bg::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 0;
    }
    .auth-card {
        background-color: #ffffff !important;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border: none;
        position: relative;
        z-index: 1;
    }
</style>

<div class="auth-container auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 col-12">

                <div class="card mt-3 mb-3 auth-card">
                    <div class="card-body p-4 p-md-5">

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="text-center mb-4">
                                <h2 class="text-dark fw-bold">Set New Password</h2>
                                <p class="text-muted">Enter your new password</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">New Password</label>
                                <input type="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Enter new password"
                                       required
                                       autofocus>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Min 8 characters, include uppercase, lowercase, number & symbol
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-dark fw-bold">Confirm Password</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       placeholder="Confirm new password"
                                       required>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1;">
                                    RESET PASSWORD
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0">
                                    <a href="{{ route('login') }}" class="text-warning fw-bold text-decoration-none">Back to Sign In</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
