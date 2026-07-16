<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 card-title fw-bold"><i class="bi bi-card-checklist text-primary me-2"></i>Tindakan Perbaikan</h5>
    </div>
    <div class="list-group list-group-flush">
        @forelse($report->actionItems as $item)
        <div class="list-group-item p-3 d-flex align-items-start gap-3">
            @if($item->status == \App\Models\ActionItem::STATUS_COMPLETED)
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1" title="Completed"></i>
            @elseif($item->status == \App\Models\ActionItem::STATUS_CANT_DO)
                <i class="bi bi-x-circle-fill text-danger fs-4 mt-1" title="Cannot Do"></i>
            @else
                <i class="bi bi-gear-fill text-warning fs-4 mt-1" title="In Progress/Do"></i>
            @endif
            <div class="flex-grow-1">
                <p class="mb-1 fw-bold">{{ $item->description }}</p>
                <small class="text-muted d-block">
                    <i class="bi bi-person-fill"></i> PIC: {{ $item->responsible->name ?? 'N/A' }}
                </small>
                <small class="text-muted d-block">
                    <i class="bi bi-calendar-check"></i> Target: {{ \Carbon\Carbon::parse($item->target_date)->format('d M Y') }}
                </small>
                @if(in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]))
                <div class="mt-2 p-2 bg-light-subtle rounded border small">
                    <strong>{{ $item->status == \App\Models\ActionItem::STATUS_COMPLETED ? 'Catatan Penyelesaian:' : 'Alasan:' }}</strong>
                    <p class="mb-0 fst-italic">"{{ $item->completion_notes }}"</p>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="list-group-item text-center text-muted p-4">
            <i class="bi bi-info-circle fs-2"></i>
            <p class="mb-0 mt-2">Belum ada tindakan perbaikan yang ditetapkan.</p>
        </div>
        @endforelse
    </div>
</div>
