<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <strong>Informasi Utama</strong>
        <span class="badge bg-info">{{ $report->status }}</span>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-4">Pelapor</dt>
            <dd class="col-sm-8">{{ $report->reporter->name }}</dd>

            <dt class="col-sm-4">Tanggal & Waktu Kejadian</dt>
            <dd class="col-sm-8">{{ $report->report_datetime->format('d M Y, H:i') }}</dd>

            <dt class="col-sm-4">Area</dt>
            <dd class="col-sm-8">{{ $report->area->name }} ({{ $report->location_details }})</dd>

            <dt class="col-sm-4">Supervisor</dt>
            <dd class="col-sm-8">{{ $report->lineSupervisor->name }}</dd>
        </dl>
        <hr>
        <h6>Detail Kejadian</h6>
        <p>{{ nl2br(e($report->details)) }}</p>

        <h6>Tindakan Langsung</h6>
        <p>{{ nl2br(e($report->immediate_action_taken)) }}</p>

        @if($report->supervisor_notes)
        <div class="alert alert-warning">
            <strong>Catatan Supervisor:</strong> {{ $report->supervisor_notes }}
        </div>
        @endif
    </div>
</div>
