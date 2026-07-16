<!-- Modal for Image Preview -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="imageModalLabel"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body text-center"><img src="" class="img-fluid" id="modalImage" alt="Attachment Preview"></div>
        </div>
    </div>
</div>

<!-- Modal for Supervisor Approval -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{-- PERBAIKAN PENTING: Menggunakan route name yang benar --}}
            <form action="{{ route('cermat.reports.submitApproval', $report) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title" id="approvalModalLabel">Konfirmasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="decision" value="">
                    <div class="mb-3">
                        <label for="supervisor_notes" class="form-label">Catatan</label>
                        <textarea name="supervisor_notes" class="form-control" rows="4" placeholder="Berikan alasan atau catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>
