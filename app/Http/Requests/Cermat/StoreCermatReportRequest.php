<?php

namespace App\Http\Requests\Cermat;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreCermatReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'details' => 'required|string|max:2000',
            'report_datetime' => [
                'required',
                'date',
                'before_or_equal:now',
                function ($attribute, $value, $fail) {
                    $reportTime = Carbon::parse($value);
                    $now = Carbon::now();

                    // Hitung selisih waktu dalam menit
                    $diffMinutes = $now->diffInMinutes($reportTime, false);

                    // Cek apakah waktu kejadian kurang dari 1 menit dari sekarang
                    // diffInMinutes dengan false akan return nilai negatif jika reportTime di masa lalu
                    // Jika diffMinutes >= 0 atau > -1, berarti waktu terlalu dekat dengan sekarang
                    if ($diffMinutes > -1) {
                        $fail('Waktu kejadian harus minimal 1 menit sebelum waktu sekarang.');
                    }

                    // Validasi tambahan: tidak boleh lebih dari 30 hari yang lalu
                    if ($reportTime->diffInDays($now) > 30) {
                        $fail('Waktu kejadian tidak boleh lebih dari 30 hari yang lalu.');
                    }
                },
            ],
            'area_id' => 'required|exists:areas,id',
            'location_details' => 'required|string|max:500',
            'immediate_action_taken' => 'required|string|max:1000',

            // Supervisor tidak wajib diisi karena auto-generated
            'line_supervisor_id' => 'nullable|exists:users,id',

            'stop_card_issued' => 'required|boolean',
            'requires_feedback' => 'nullable|boolean',
            'statement' => 'required|accepted',

            // Klasifikasi (opsional saat create, wajib saat HSSE review)
            'selectedUnsafeActs' => 'nullable|array',
            'selectedUnsafeActs.*' => 'exists:unsafe_acts,id',
            'selectedUnsafeConditions' => 'nullable|array',
            'selectedUnsafeConditions.*' => 'exists:unsafe_conditions,id',

            // Attachments
            'attachments' => 'required|array|min:1|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'details.required' => 'Uraian kejadian wajib diisi.',
            'details.max' => 'Uraian kejadian maksimal 2000 karakter.',

            'report_datetime.required' => 'Waktu kejadian wajib diisi.',
            'report_datetime.date' => 'Format waktu kejadian tidak valid.',
            'report_datetime.before_or_equal' => 'Waktu kejadian tidak boleh di masa depan.',

            'area_id.required' => 'Area lokasi wajib dipilih.',
            'area_id.exists' => 'Area yang dipilih tidak valid.',

            'location_details.required' => 'Lokasi spesifik wajib diisi.',
            'location_details.max' => 'Lokasi spesifik maksimal 500 karakter.',

            'immediate_action_taken.required' => 'Tindakan langsung wajib diisi.',
            'immediate_action_taken.max' => 'Tindakan langsung maksimal 1000 karakter.',

            'stop_card_issued.required' => 'Status STOP Job wajib dipilih.',
            'statement.accepted' => 'Anda harus menyetujui pernyataan.',

            'attachments.required' => 'Minimal 1 foto bukti wajib dilampirkan.',
            'attachments.min' => 'Minimal 1 foto bukti wajib dilampirkan.',
            'attachments.max' => 'Maksimal 5 foto yang dapat dilampirkan.',
            'attachments.*.mimes' => 'File harus berformat: jpg, jpeg, png, gif, atau webp.',
            'attachments.*.max' => 'Ukuran file maksimal 5MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'details' => 'uraian kejadian',
            'report_datetime' => 'waktu kejadian',
            'area_id' => 'area lokasi',
            'location_details' => 'lokasi spesifik',
            'immediate_action_taken' => 'tindakan langsung',
            'line_supervisor_id' => 'supervisor',
            'stop_card_issued' => 'status STOP job',
            'attachments' => 'foto bukti',
        ];
    }
}
