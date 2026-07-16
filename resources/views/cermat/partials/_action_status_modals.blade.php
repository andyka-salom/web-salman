@push('styles')
    {{-- CSS Kustom untuk komponen upload file yang lebih baik --}}
    <style>
        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #ced4da;
            border-radius: .5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease;
        }
        .file-upload-wrapper:hover, .file-upload-wrapper.dragover {
            border-color: var(--bs-primary);
            background-color: var(--bs-primary-bg-subtle);
        }
        .file-upload-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        .file-upload-content i {
            font-size: 3rem;
            color: var(--bs-secondary-color);
        }
        .file-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        .preview-image-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
        }
        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: .5rem;
            border: 1px solid var(--bs-border-color);
        }
        .remove-preview-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: rgba(255, 0, 0, 0.8);
            color: white;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            cursor: pointer;
            font-size: 14px;
            z-index: 10;
        }
    </style>
@endpush

{{-- =================================================================== --}}
{{-- MODAL UNTUK MENANDAI SELESAI (COMPLETED) --}}
{{-- =================================================================== --}}
<div class="modal fade" id="actionStatusModal-{{ $item->id }}-completed" tabindex="-1" aria-labelledby="modalLabelCompleted{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ \App\Models\ActionItem::STATUS_COMPLETED }}">
            <input type="hidden" name="modal_id" value="actionStatusModal-{{ $item->id }}-completed">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalLabelCompleted{{ $item->id }}">Konfirmasi Penyelesaian Tindakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menandai tindakan ini sebagai <strong>"Selesai"</strong>?</p>
                    <div class="form-floating mb-3">
                        <textarea class="form-control @error('completion_notes') is-invalid @enderror" placeholder="Jelaskan hasil dari tindakan yang dilakukan..." id="completion_notes_completed_{{ $item->id }}" name="completion_notes" style="height: 100px" required>{{ old('completion_notes') }}</textarea>
                        <label for="completion_notes_completed_{{ $item->id }}">Catatan Penyelesaian (Wajib diisi)</label>
                        @error('completion_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- [DIREVISI] Bagian untuk Upload Bukti Foto --}}
                    <div>
                        <label class="form-label fw-semibold">Bukti Foto (Opsional, maks. 5 file)</label>
                        <div class="file-upload-wrapper" id="file-upload-wrapper-{{ $item->id }}">
                            <input type="file" name="proof_photos[]" class="proof-photo-input" accept="image/*" multiple data-item-id="{{ $item->id }}">
                            <div class="file-upload-content">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <p class="mb-0 fw-bold">Klik untuk memilih file atau seret ke sini</p>
                                <small class="text-muted">JPG, PNG, GIF (Maks. 5MB per file)</small>
                            </div>
                        </div>
                        @error('proof_photos.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @error('proof_photos')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        <div class="file-preview-container" id="photo-preview-container-{{ $item->id }}">
                            {{-- Preview gambar akan muncul di sini --}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill me-2"></i>Ya, Tandai Selesai</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- =================================================================== --}}
{{-- MODAL UNTUK MENANDAI TIDAK BISA DIKERJAKAN (CANNOT DO) --}}
{{-- =================================================================== --}}
<div class="modal fade" id="actionStatusModal-{{ $item->id }}-cantdo" tabindex="-1" aria-labelledby="modalLabelCantDo{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('cermat.action-items.updateStatus', $item) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ \App\Models\ActionItem::STATUS_CANT_DO }}">
            <input type="hidden" name="modal_id" value="actionStatusModal-{{ $item->id }}-cantdo">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalLabelCantDo{{ $item->id }}">Konfirmasi Tindakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin tindakan ini <strong>tidak dapat dikerjakan</strong>?</p>
                    <div class="form-floating">
                        <textarea class="form-control @error('completion_notes') is-invalid @enderror" placeholder="Alasan" id="completion_notes_cantdo_{{ $item->id }}" name="completion_notes" style="height: 100px" required>{{ old('completion_notes') }}</textarea>
                        <label for="completion_notes_cantdo_{{ $item->id }}">Alasan (Wajib diisi)</label>
                        @error('completion_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle-fill me-2"></i>Ya, Konfirmasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- =================================================================== --}}
{{-- MODAL UNTUK PERPANJANGAN WAKTU (EXTEND TIME) --}}
{{-- =================================================================== --}}
<div class="modal fade" id="extendTimeModal-{{ $item->id }}" tabindex="-1" aria-labelledby="modalLabelExtend{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('cermat.action-items.extend', $item) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="modal_id" value="extendTimeModal-{{ $item->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalLabelExtend{{ $item->id }}">Perpanjangan Target Waktu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Target saat ini adalah <strong>{{ \Carbon\Carbon::parse($item->target_date)->isoFormat('DD MMMM Y') }}</strong>. Batas maksimal perpanjangan adalah 3 kali.</p>
                    <div class="mb-3">
                        <label for="new_target_date_{{ $item->id }}" class="form-label">Target Baru <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('new_target_date') is-invalid @enderror" id="new_target_date_{{ $item->id }}" name="new_target_date" min="{{ \Carbon\Carbon::parse($item->target_date)->addDay()->toDateString() }}" required>
                        @error('new_target_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating">
                        <textarea class="form-control @error('extend_reason') is-invalid @enderror" placeholder="Alasan Perpanjangan" id="extend_reason_{{ $item->id }}" name="extend_reason" style="height: 100px" required></textarea>
                        <label for="extend_reason_{{ $item->id }}">Alasan Perpanjangan (Wajib diisi)</label>
                         @error('extend_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-plus-fill me-2"></i>Ajukan Perpanjangan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataTransferObjects = {};

    function handleFileInputChange(inputElement) {
        const itemId = inputElement.dataset.itemId;
        const previewContainer = document.getElementById(`photo-preview-container-${itemId}`);
        const files = inputElement.files;

        if (!dataTransferObjects[itemId]) {
            dataTransferObjects[itemId] = new DataTransfer();
        }

        // Tambahkan file baru ke DataTransfer object
        Array.from(files).forEach(file => {
             // Batasi total file menjadi 5
            if (dataTransferObjects[itemId].items.length < 5) {
                dataTransferObjects[itemId].items.add(file);
            } else {
                alert('Maksimal 5 foto yang dapat diunggah.');
            }
        });

        // Update file di input element
        inputElement.files = dataTransferObjects[itemId].files;
        renderPreviews(itemId, previewContainer);
    }

    function renderPreviews(itemId, previewContainer) {
        previewContainer.innerHTML = '';
        const dt = dataTransferObjects[itemId];

        if (!dt || dt.items.length === 0) return;

        Array.from(dt.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'preview-image-wrapper';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-image';

                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-preview-btn';
                removeBtn.innerHTML = '&times;';
                removeBtn.dataset.index = index;
                removeBtn.onclick = () => removeFile(itemId, index);

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                previewContainer.appendChild(wrapper);
            }
            reader.readAsDataURL(file);
        });
    }

    function removeFile(itemId, index) {
        const inputElement = document.querySelector(`.proof-photo-input[data-item-id="${itemId}"]`);
        const previewContainer = document.getElementById(`photo-preview-container-${itemId}`);

        dataTransferObjects[itemId].items.remove(index);
        inputElement.files = dataTransferObjects[itemId].files;
        renderPreviews(itemId, previewContainer);
    }

    // Event listeners
    document.querySelectorAll('.proof-photo-input').forEach(input => {
        input.addEventListener('change', () => handleFileInputChange(input));
    });

    document.querySelectorAll('.file-upload-wrapper').forEach(wrapper => {
        wrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            wrapper.classList.add('dragover');
        });
        wrapper.addEventListener('dragleave', () => {
            wrapper.classList.remove('dragover');
        });
        wrapper.addEventListener('drop', () => {
            wrapper.classList.remove('dragover');
        });
    });

    // Handle re-opening modal on validation error
    const failedModalId = @json(old('modal_id'));
    if (failedModalId) {
        const modalElement = document.getElementById(failedModalId);
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
});
</script>
@endpush
