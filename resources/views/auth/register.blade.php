@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')

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

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="text-center mb-4">
                                <h2 class="text-dark fw-bold">Sign Up</h2>
                                <p class="text-muted">Create your account to get started</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Full Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="Enter your full name"
                                       required
                                       autofocus>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="Enter your email"
                                       required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Phone Number</label>
                                <input type="text"
                                       name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}"
                                       placeholder="08xxxxxxxxxx"
                                       required>
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Used for WhatsApp notifications</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Password</label>
                                <input type="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Enter password"
                                       required>
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
                                       placeholder="Confirm password"
                                       required>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label text-secondary" for="terms">
                                        I agree to the <a href="#" class="text-primary text-decoration-none fw-bold">Terms & Conditions</a>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1;">
                                    SIGN UP
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0 text-secondary">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="text-warning fw-bold text-decoration-none">Sign In</a>
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
