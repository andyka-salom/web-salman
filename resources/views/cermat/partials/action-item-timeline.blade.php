{{-- resources/views/cermat/partials/action-item-timeline.blade.php --}}
<div class="timeline-modern-item status-{{ Str::slug($item->status) }}">
    <div class="timeline-modern-marker">
        @if($item->status == \App\Models\ActionItem::STATUS_COMPLETED)
            <i class="bi bi-check-lg fs-4"></i>
        @elseif($item->status == \App\Models\ActionItem::STATUS_CANT_DO)
            <i class="bi bi-x fs-4"></i>
        @elseif($item->status == \App\Models\ActionItem::STATUS_IN_PROGRESS)
            <i class="bi bi-arrow-repeat fs-4"></i>
        @else
            <i class="bi bi-clipboard fs-4"></i>
        @endif
    </div>
    <div class="timeline-modern-content card-hover">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <p class="fw-semibold mb-0 flex-grow-1 pe-2 fs-6">{{ $item->description }}</p>
            @if(!in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]))
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if($item->status === \App\Models\ActionItem::STATUS_DO)
                            <li>
                                <form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Models\ActionItem::STATUS_IN_PROGRESS }}">
                                    <button type="submit" class="dropdown-item fw-medium">
                                        <i class="bi bi-play-circle-fill me-2 text-info"></i>Mulai Kerjakan
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <a class="dropdown-item text-success fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#actionStatusModal-{{ $item->id }}-completed">
                                <i class="bi bi-check-circle-fill me-2"></i>Tandai Selesai
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#actionStatusModal-{{ $item->id }}-cantdo">
                                <i class="bi bi-x-circle-fill me-2"></i>Tidak Dikerjakan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @role('hsse')
                            <li>
                                <a class="dropdown-item text-primary fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#extendTimeModal-{{ $item->id }}">
                                    <i class="bi bi-calendar-plus-fill me-2"></i>Perpanjang Waktu
                                </a>
                            </li>
                        @endrole
                    </ul>
                </div>
            @else
                @php
                    $badgeClass = ($item->status == \App\Models\ActionItem::STATUS_COMPLETED) ? 'success' : 'danger';
                @endphp
                <span class="badge fs-6 bg-{{$badgeClass}}-subtle text-{{$badgeClass}}-emphasis border border-{{$badgeClass}}-subtle rounded-pill">
                    {{ $item->status }}
                </span>
            @endif
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3 text-body-secondary small">
            <span><i class="bi bi-person-fill me-1"></i> {{ $item->responsible->name ?? 'N/A' }}</span>
            <span><i class="bi bi-calendar-event me-1"></i> Target: <span class="fw-semibold">{{ $item->target_date->isoFormat('DD MMM Y') }}</span></span>
            @if($item->category)
                <span>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill">
                        <i class="bi bi-tag-fill me-1"></i>{{ $item->category->name }}
                    </span>
                </span>
            @endif
            @if(!in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]) && $item->target_date->isPast())
                <span>
                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle rounded-pill">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Terlambat
                    </span>
                </span>
            @endif
        </div>

        @if($item->extend_date_1 || in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]) || $item->photos->isNotEmpty())
            <hr class="my-3">
            <div class="d-flex flex-column gap-3">
                @if(in_array($item->status, [\App\Models\ActionItem::STATUS_COMPLETED, \App\Models\ActionItem::STATUS_CANT_DO]) && $item->completion_notes)
                    <div class="completion-notes">
                        <p class="mb-1 fw-bold small text-body-secondary text-uppercase">CATATAN PENYELESAIAN</p>
                        <p class="mb-0 fst-italic">"{{ $item->completion_notes }}"</p>
                    </div>
                @endif
                @if($item->extend_date_1)
                    <div>
                        <p class="mb-1 fw-bold small text-body-secondary text-uppercase">RIWAYAT PERPANJANGAN</p>
                        <ul class="list-unstyled mb-0 small">
                            @foreach([1, 2, 3] as $i)
                                @php
                                    $dateCol = "extend_date_$i";
                                    $reasonCol = "extend_reason_$i";
                                @endphp
                                @if($item->$dateCol)
                                    <li class="mb-1">
                                        <i class="bi bi-arrow-right-short text-primary"></i> Diperpanjang ke <strong class="text-dark">{{ \Carbon\Carbon::parse($item->$dateCol)->isoFormat('DD MMM Y') }}</strong>.
                                        <em class="text-muted d-block ps-3">Alasan: {{ $item->$reasonCol }}</em>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($item->photos->isNotEmpty())
                    <div>
                        <p class="mb-2 fw-bold small text-body-secondary text-uppercase">Bukti Foto</p>
                        <div class="action-proof-gallery">
                            @foreach($item->photos as $photo)
                                <a href="#" class="action-proof-item" data-bs-toggle="modal" data-bs-target="#imageViewerModal" data-img-src="{{ asset('storage/' . $photo->file_path) }}" data-img-title="Bukti Tindakan: {{ Str::limit($item->description, 50) }}">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" class="action-proof-thumbnail" alt="Bukti Foto">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- MODALS FOR THIS ACTION ITEM --}}
@include('cermat.partials.action-item-modals', ['item' => $item])
