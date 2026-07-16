{{-- Modal: Add Single Action --}}
<div class="modal fade" id="addActionModal" tabindex="-1" aria-labelledby="addActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('cermat.reports.submitActionItem', $report) }}" method="POST" id="addActionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addActionModalLabel">
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>
                        Tambah Tindakan Perbaikan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="action_description" class="form-label fw-semibold">
                            Deskripsi Tindakan <span class="text-danger">*</span>
                        </label>
                        <textarea name="description"
                                  id="action_description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4"
                                  required
                                  placeholder="Jelaskan tindakan perbaikan yang akan dilakukan...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="action_category" class="form-label fw-semibold">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="action_category_id"
                                    id="action_category"
                                    class="form-select"
                                    required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($actionCategories as $category)
                                    <option value="{{ $category->id }}"
                                            data-category-option
                                            data-duration="{{ $category->duration_days }}"
                                            data-name="{{ $category->name }}"
                                            data-code="{{ $category->code }}"
                                            {{ old('action_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->code }}) - {{ $category->duration_days }} hari
                                    </option>
                                @endforeach
                            </select>
                            @error('action_category_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="category_info_single" class="mt-2 small text-muted" style="display: none;"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="action_target_date" class="form-label fw-semibold">
                                Target Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="target_date"
                                   id="action_target_date"
                                   class="form-control @error('target_date') is-invalid @enderror"
                                   required
                                   value="{{ old('target_date', now()->toDateString()) }}"
                                   min="{{ now()->toDateString() }}">
                            @error('target_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="action_pic" class="form-label fw-semibold">
                            Penanggung Jawab (PIC) <span class="text-danger">*</span>
                        </label>
                        <select name="responsible_id"
                                id="action_pic"
                                class="form-select"
                                required>
                            <option value="">-- Pilih PIC --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('responsible_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('responsible_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Tindakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Bulk Add Actions --}}
<div class="modal fade" id="bulkAddActionModal" tabindex="-1" aria-labelledby="bulkAddActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('cermat.reports.bulkAddActionItems', $report) }}" method="POST" id="bulkAddActionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="bulkAddActionModalLabel">
                        <i class="bi bi-journal-plus text-primary me-2"></i>
                        Tambah Beberapa Tindakan Sekaligus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-start">
                        <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                        <div>
                            <strong>Petunjuk:</strong> Anda dapat menambahkan hingga 10 tindakan sekaligus.
                            Klik tombol "Tambah Baris" untuk menambah form tindakan baru.
                        </div>
                    </div>

                    <div id="bulkActionsContainer">
                        <!-- Action row template will be added here -->
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="addBulkActionRow">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Baris
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill me-1"></i>Simpan Semua Tindakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TomSelect for single action modal
    window.categorySelect = new TomSelect('#action_category', {
        create: false,
        onChange: function(value) {
            updateCategoryInfo(value, 'category_info_single', 'action_target_date');
        }
    });

    window.picSelect = new TomSelect('#action_pic', {
        create: false
    });

    // Category info and date calculation
    function updateCategoryInfo(categoryId, infoElementId, targetDateInputId) {
        const infoElement = document.getElementById(infoElementId);
        const targetDateInput = document.getElementById(targetDateInputId);

        if (!categoryId || !categoryData[categoryId]) {
            infoElement.style.display = 'none';
            return;
        }

        const category = categoryData[categoryId];
        const duration = category.duration;

        if (duration > 0) {
            const today = new Date();
            const targetDate = new Date(today);
            targetDate.setDate(targetDate.getDate() + duration);

            targetDateInput.value = formatDate(targetDate);

            infoElement.innerHTML = `
                <i class="bi bi-calendar-check me-1"></i>
                Target otomatis disetel ke <strong>${formatDateReadable(targetDate)}</strong>
                (+ ${duration} hari kerja)
            `;
            infoElement.style.display = 'block';
        } else {
            infoElement.style.display = 'none';
        }
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDateReadable(date) {
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

    // Bulk Add Actions Logic
    let bulkActionCount = 0;
    const maxBulkActions = 10;
    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
    const addBulkActionRowBtn = document.getElementById('addBulkActionRow');

    function createBulkActionRow(index) {
        const row = document.createElement('div');
        row.className = 'bulk-action-row card mb-3';
        row.dataset.index = index;
        row.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-grip-vertical me-2"></i>Tindakan #${index + 1}
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-bulk-row" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi Tindakan <span class="text-danger">*</span></label>
                    <textarea name="actions[${index}][description]"
                              class="form-control"
                              rows="3"
                              required
                              placeholder="Deskripsi tindakan perbaikan..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select name="actions[${index}][action_category_id]"
                                class="form-select bulk-category-select"
                                data-index="${index}"
                                required>
                            <option value="">-- Pilih --</option>
                            @foreach($actionCategories as $category)
                                <option value="{{ $category->id }}"
                                        data-duration="{{ $category->duration_days }}">
                                    {{ $category->name }} ({{ $category->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Target Selesai <span class="text-danger">*</span></label>
                        <input type="date"
                               name="actions[${index}][target_date]"
                               class="form-control bulk-target-date"
                               data-index="${index}"
                               required
                               min="{{ now()->toDateString() }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">PIC <span class="text-danger">*</span></label>
                        <select name="actions[${index}][responsible_id]"
                                class="form-select bulk-pic-select"
                                required>
                            <option value="">-- Pilih --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        `;

        return row;
    }

    function addBulkActionRow() {
        if (bulkActionCount >= maxBulkActions) {
            Swal.fire('Perhatian', `Maksimal ${maxBulkActions} tindakan dapat ditambahkan sekaligus`, 'warning');
            return;
        }

        const row = createBulkActionRow(bulkActionCount);
        bulkActionsContainer.appendChild(row);

        // Initialize TomSelect for the new row
        const picSelect = row.querySelector('.bulk-pic-select');
        new TomSelect(picSelect, { create: false });

        // Add event listener for category change
        const categorySelect = row.querySelector('.bulk-category-select');
        const targetDateInput = row.querySelector('.bulk-target-date');

        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const duration = parseInt(selectedOption.dataset.duration, 10);

            if (duration > 0) {
                const today = new Date();
                const targetDate = new Date(today);
                targetDate.setDate(targetDate.getDate() + duration);
                targetDateInput.value = formatDate(targetDate);
            }
        });

        bulkActionCount++;

        // Update remove buttons
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const removeButtons = bulkActionsContainer.querySelectorAll('.remove-bulk-row');
        removeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('.bulk-action-row');
                row.remove();
                bulkActionCount--;

                // Renumber remaining rows
                const rows = bulkActionsContainer.querySelectorAll('.bulk-action-row');
                rows.forEach((r, idx) => {
                    r.querySelector('h6').innerHTML = `
                        <i class="bi bi-grip-vertical me-2"></i>Tindakan #${idx + 1}
                    `;
                });
            });
        });
    }

    addBulkActionRowBtn.addEventListener('click', addBulkActionRow);

    // Add initial rows when modal opens
    document.getElementById('bulkAddActionModal').addEventListener('shown.bs.modal', function() {
        if (bulkActionCount === 0) {
            addBulkActionRow();
            addBulkActionRow();
            addBulkActionRow();
        }
    });

    // Make rows sortable
    if (bulkActionsContainer) {
        new Sortable(bulkActionsContainer, {
            animation: 150,
            handle: '.bi-grip-vertical',
            ghostClass: 'opacity-50'
        });
    }
});
</script>
@endpush
