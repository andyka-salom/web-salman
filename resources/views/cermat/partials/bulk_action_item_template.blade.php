{{-- FILE: resources/views/cermat/partials/bulk_action_item_template.blade.php --}}
@php
    // Default values for template rendering
    $index = $index ?? '__INDEX__';
    $oldAction = $oldAction ?? [];
    $isTemplate = $isTemplate ?? false;
    $hasError = $hasError ?? false;

    // Use current date if no old value exists
    $targetDateValue = $oldAction['target_date'] ?? now()->toDateString();
@endphp

@if ($isTemplate)
    <div class="d-none"> {{-- Pastikan tidak dirender sebagai HTML normal --}}
@endif

<div class="card mb-3 action-item-card @if($hasError) border-danger border-3 @endif" data-index="{{ $index }}">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="fw-bold mb-0 text-primary">Tindakan #<span class="action-item-number">{{ $index + 1 }}</span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-action-btn" title="Hapus item ini"><i class="bi bi-trash-fill"></i></button>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label small fw-semibold">Deskripsi Tindakan <span class="text-danger">*</span></label>
                <textarea name="actions[{{ $index }}][description]" class="form-control @error("actions.$index.description") is-invalid @enderror" rows="2" required placeholder="Apa yang harus dilakukan?">{{ $oldAction['description'] ?? '' }}</textarea>
                @error("actions.$index.description")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                <select name="actions[{{ $index }}][action_category_id]" class="form-select action-category-select @error("actions.$index.action_category_id") is-invalid @enderror" required>
                    <option value="">Pilih Kategori</option>
                    @foreach($actionCategories as $category)
                        <option value="{{ $category->id }}"
                                data-duration="{{ $category->duration_days }}"
                                {{ ($oldAction['action_category_id'] ?? null) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }} ({{ $category->duration_days }} hari)
                        </option>
                    @endforeach
                </select>
                @error("actions.$index.action_category_id")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Target Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="actions[{{ $index }}][target_date]" class="form-control action-target-date @error("actions.$index.target_date") is-invalid @enderror" required
                       min="{{ now()->toDateString() }}" value="{{ $targetDateValue }}">
                @error("actions.$index.target_date")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                <select name="actions[{{ $index }}][responsible_id]" class="form-select action-pic-select @error("actions.$index.responsible_id") is-invalid @enderror" required>
                    <option value="">Pilih PIC</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ ($oldAction['responsible_id'] ?? null) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error("actions.$index.responsible_id")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

@if ($isTemplate)
    </div>
@endif
