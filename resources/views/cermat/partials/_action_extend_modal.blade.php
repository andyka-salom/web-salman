{{-- resources/views/cermat/partials/_action_extend_modal.blade.php --}}
<div class="modal fade" id="extendTimeModal-{{ $item->id }}" tabindex="-1" aria-labelledby="extendTimeModalLabel-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('cermat.action-items.extend', $item) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="extendTimeModalLabel-{{ $item->id }}">Perpanjang Waktu Tindakan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Anda akan memperpanjang target waktu untuk tindakan: <br><strong>"{{ $item->description }}"</strong>.</p>

                    @php
                        // Tentukan tanggal minimum untuk perpanjangan (H+1 dari target saat ini)
                        $minDate = \Carbon\Carbon::parse($item->target_date)->addDay()->toDateString();
                    @endphp

                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" name="new_target_date" id="new_target_date_{{ $item->id }}" placeholder="Target Selesai Baru" required min="{{ $minDate }}">
                        <label for="new_target_date_{{ $item->id }}">Target Selesai Baru</label>
                    </div>

                    <div class="form-floating">
                        <textarea class="form-control" name="extend_reason" placeholder="Alasan Perpanjangan" id="extend_reason_{{ $item->id }}" style="height: 100px" required></textarea>
                        <label for="extend_reason_{{ $item->id }}">Alasan Perpanjangan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-plus-fill me-1"></i> Simpan Perpanjangan</button>
                </div>
            </form>
        </div>
    </div>
</div>
