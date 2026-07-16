@extends('layouts.app')

@section('title', 'No Vessel Assigned')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-ship text-muted" style="font-size: 6rem; opacity: 0.3;"></i>
                    </div>

                    <h3 class="mb-3">No Vessel Assigned</h3>
                    <p class="text-muted mb-4">
                        Anda belum ditugaskan ke vessel manapun. <br>
                        Silakan hubungi administrator untuk mendapatkan akses ke vessel.
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi:</strong> Untuk dapat mengakses Daily Checkup, Anda perlu ditugaskan sebagai crew atau coordinator vessel.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            {{-- Contact Info Card --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-question-circle me-2"></i>Butuh Bantuan?
                    </h5>
                    <p class="mb-3">Hubungi administrator sistem untuk:</p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Penugasan ke vessel
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Perubahan akses dan permissions
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Pertanyaan terkait sistem Daily Checkup
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
