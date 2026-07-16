{{-- FILE: resources/views/cermat/partials/bulk_action_modal.blade.php --}}
@php
    // Ambil data untuk Tom Select di modal
    $usersJson = json_encode($users->mapWithKeys(fn($u) => [$u->id => $u->name]));
    $categoriesJson = json_encode($actionCategories->mapWithKeys(fn($c) => [$c->id => $c->name]));
@endphp

<div class="modal fade animated fadeInDown" id="bulkActionModal" tabindex="-1" role="dialog" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form action="{{ route('cermat.reports.submitBulkActionItems', $report) }}" method="POST" id="bulk-action-form">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="bulkActionModalLabel"><i class="bi bi-journal-plus me-2"></i> Tambah Tindakan Perbaikan Massal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    {{-- Alert untuk pesan error validasi bulk --}}
                    @if ($errors->has('actions') || $errors->has('actions.*'))
                        <div class="alert alert-danger mb-4">
                            Terdapat kesalahan pada input tindakan perbaikan massal. Silakan periksa setiap item.
                        </div>
                    @endif

                    <div id="action-items-container">
                        {{-- Template Action Item akan ditambahkan di sini oleh JavaScript --}}
                        {{-- Jika ada error validasi lama, render ulang item yang lama --}}
                        @if(old('actions'))
                            @foreach(old('actions') as $index => $oldAction)
                                @include('cermat.partials.bulk_action_item_template', [
                                    'index' => $index,
                                    'oldAction' => $oldAction,
                                    'users' => $users,
                                    'actionCategories' => $actionCategories,
                                    'hasError' => $errors->has("actions.$index") || $errors->has("actions.$index.*")
                                ])
                            @endforeach
                        @endif
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="add-action-btn">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Item
                        </button>
                        <button type="button" class="btn btn-outline-info rounded-pill px-4" id="copy-last-action-btn">
                            <i class="bi bi-copy me-1"></i> Duplikat Item Terakhir
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-bulk-btn">
                        <i class="bi bi-send-fill me-2"></i> Submit Semua Tindakan (<span id="action-count-footer">0</span>)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TEMPLATE HTML UNTUK SATU ACTION ITEM (Hidden) --}}
{{-- PENTING: Index harus berupa '__INDEX__' agar JS bisa menggantinya --}}
<template id="action-item-template">
    <div class="card mb-3 action-item-card @if(isset($hasError) && $hasError) border-danger border-3 @endif" data-index="__INDEX__">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0 text-primary">Tindakan #<span class="action-item-number">__NUMBER__</span></h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-action-btn" title="Hapus item ini"><i class="bi bi-trash-fill"></i></button>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-semibold">Deskripsi Tindakan <span class="text-danger">*</span></label>
                    <textarea name="actions[__INDEX__][description]" class="form-control @error('actions.__INDEX__.description') is-invalid @enderror" rows="2" required placeholder="Apa yang harus dilakukan?"></textarea>
                    @error('actions.__INDEX__.description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="actions[__INDEX__][action_category_id]" class="form-select action-category-select @error('actions.__INDEX__.action_category_id') is-invalid @enderror" required></select>
                    @error('actions.__INDEX__.action_category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Target Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="actions[__INDEX__][target_date]" class="form-control action-target-date @error('actions.__INDEX__.target_date') is-invalid @enderror" required min="{{ now()->toDateString() }}">
                    @error('actions[__INDEX__].target_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                    <select name="actions[__INDEX__][responsible_id]" class="form-select action-pic-select @error('actions.__INDEX__.responsible_id') is-invalid @enderror" required></select>
                    @error('actions[__INDEX__].responsible_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</template>

{{-- PENTING: Partial untuk me-render action item lama saat validasi gagal --}}
@if (!function_exists('render_bulk_action_item_template'))
    @include('cermat.partials.bulk_action_item_template', ['isTemplate' => true])
@endif
