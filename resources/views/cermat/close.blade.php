@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tutup Laporan #{{ $report->report_number }}</h2>
    <p>Berikan deskripsi penutupan akhir untuk laporan ini. Pastikan semua tindakan perbaikan telah diverifikasi.</p>

    <form action="{{ route('cermat.reports.submitClose', $report) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
                @endif
                <div class="mb-3">
                    <label for="close_out_description" class="form-label">Deskripsi Penutupan <span class="text-danger">*</span></label>
                    <textarea name="close_out_description" id="close_out_description" class="form-control" rows="5" required>{{ old('close_out_description') }}</textarea>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('cermat.reports.show', $report) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan dan Tutup Laporan</button>
            </div>
        </div>
    </form>
</div>
@endsection
