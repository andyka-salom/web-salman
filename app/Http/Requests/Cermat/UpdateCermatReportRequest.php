<?php

namespace App\Http\Requests\Cermat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCermatReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');
        // Authorization mengandalkan logic canBeEdited() di model, yang sudah diupdate.
        return auth()->check() && ($report->canBeEdited() || auth()->user()->hasRole('super-admin'));
    }

    protected function prepareForValidation(): void
    {
        // Hapus string kosong dari array opsional
        $this->merge([
            'selectedUnsafeActs' => array_filter(
                $this->input('selectedUnsafeActs', []),
                fn($value) => !empty($value)
            ),
            'selectedUnsafeConditions' => array_filter(
                $this->input('selectedUnsafeConditions', []),
                fn($value) => !empty($value)
            ),
            'delete_attachments' => array_filter(
                $this->input('delete_attachments', []),
                fn($value) => !empty($value)
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'details' => 'required|string',
            'report_datetime' => 'required|date|before_or_equal:now',

            'area_id' => [
                'required',
                'uuid',
                Rule::exists('areas', 'id')->where(function ($query) {
                    $query->where('is_active', true)->whereNull('deleted_at');
                }),
            ],

            'location_details' => 'required|string|max:255',
            'immediate_action_taken' => 'required|string',

            'line_supervisor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],

            'suggestion_for_improvement' => 'nullable|string',

            'stop_card_issued' => 'required|boolean',
            'requires_feedback' => 'required|boolean',

            'selectedUnsafeActs' => 'nullable|array',
            'selectedUnsafeActs.*' => [
                'uuid',
                Rule::exists('unsafe_acts', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNull('deleted_at')),
            ],

            'selectedUnsafeConditions' => 'nullable|array',
            'selectedUnsafeConditions.*' => [
                'uuid',
                Rule::exists('unsafe_conditions', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNull('deleted_at')),
            ],

            // Attachment untuk Update: tidak required, max 5
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Validasi untuk file yang akan dihapus
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'uuid|exists:report_attachments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'area_id.required' => 'Area lokasi wajib dipilih.',
            'area_id.uuid' => 'Format ID Area tidak valid.',
            'area_id.exists' => 'Area yang dipilih tidak ditemukan atau tidak aktif.',

            'line_supervisor_id.required' => 'Atasan langsung wajib dipilih.',
            'line_supervisor_id.uuid' => 'Format ID Supervisor tidak valid.',
            'line_supervisor_id.exists' => 'Supervisor yang dipilih tidak ditemukan atau tidak aktif.',

            'selectedUnsafeActs.array' => 'Format data Unsafe Act tidak valid.',
            'selectedUnsafeActs.*.uuid' => 'Format ID Unsafe Act tidak valid.',
            'selectedUnsafeActs.*.exists' => 'Salah satu Unsafe Act yang dipilih tidak ditemukan atau tidak aktif.',

            'selectedUnsafeConditions.array' => 'Format data Unsafe Condition tidak valid.',
            'selectedUnsafeConditions.*.uuid' => 'Format ID Unsafe Condition tidak valid.',
            'selectedUnsafeConditions.*.exists' => 'Salah satu Unsafe Condition yang dipilih tidak ditemukan atau tidak aktif.',

            'location_details.required' => 'Detail lokasi spesifik wajib diisi.',
            'location_details.max' => 'Detail lokasi tidak boleh lebih dari 255 karakter.',

            'report_datetime.required' => 'Tanggal & Waktu Kejadian wajib diisi.',
            'report_datetime.date' => 'Format Tanggal & Waktu tidak valid.',
            'report_datetime.before_or_equal' => 'Tanggal & Waktu Kejadian tidak boleh melebihi waktu saat ini.',

            'details.required' => 'Uraian detail temuan wajib diisi.',

            'immediate_action_taken.required' => 'Tindakan langsung yang diambil wajib diisi.',

            'stop_card_issued.required' => 'Status STOP Card wajib dipilih.',
            'stop_card_issued.boolean' => 'Format status STOP Card tidak valid.',

            'requires_feedback.required' => 'Status feedback wajib dipilih.',
            'requires_feedback.boolean' => 'Format status feedback tidak valid.',

            'attachments.array' => 'Format lampiran tidak valid.',
            'attachments.max' => 'Anda hanya dapat mengunggah maksimal 5 file.',
            'attachments.*.required' => 'File lampiran wajib dipilih.',
            'attachments.*.image' => 'File harus berupa gambar.',
            'attachments.*.mimes' => 'Format gambar harus: JPEG, PNG, JPG, atau GIF.',
            'attachments.*.max' => 'Ukuran setiap gambar tidak boleh lebih dari 2MB.',

            'delete_attachments.array' => 'Format data penghapusan tidak valid.',
            'delete_attachments.*.uuid' => 'Format ID attachment tidak valid.',
            'delete_attachments.*.exists' => 'File yang akan dihapus tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'area_id' => 'area lokasi',
            'line_supervisor_id' => 'atasan langsung',
            'location_details' => 'detail lokasi',
            'report_datetime' => 'tanggal & waktu kejadian',
            'details' => 'uraian detail',
            'immediate_action_taken' => 'tindakan langsung',
            'stop_card_issued' => 'status STOP Card',
            'requires_feedback' => 'status feedback',
            'attachments' => 'lampiran foto',
            'selectedUnsafeActs' => 'Unsafe Act',
            'selectedUnsafeConditions' => 'Unsafe Condition',
            'delete_attachments' => 'file yang akan dihapus',
        ];
    }
}
