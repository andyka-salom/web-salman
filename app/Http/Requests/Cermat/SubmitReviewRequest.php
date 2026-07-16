<?php

namespace App\Http\Requests\Cermat;

use App\Models\CermatReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CermatReport $report */
        // $report = $this->route('report');
        return true;
        // HSSE Review/Data Entry diizinkan selama status global adalah IN_REVIEW
        // (menunggu persetujuan Supervisor dan/atau review HSSE).
        // return $report->status === CermatReport::STATUS_IN_REVIEW && auth()->user()->hasRole('hsse');
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', Rule::in(['hse', 'security'])], // Menggunakan snake_case
            // 'is_limited_circulation' => 'required|boolean', // Menggunakan snake_case
            'classification' => ['required', Rule::in(['positive_aspect', 'unsafe_condition', 'unsafe_action', 'near_miss', 'incident'])], // Menggunakan snake_case

            // Menggunakan snake_case untuk keys
            'synergi_register_no' => 'nullable|string|max:255',
            'pelanggaran_id' => 'nullable|exists:pelanggaran,id',
            'security_event_category_id' => [
                'nullable',
                Rule::requiredIf($this->event_type === 'security'),
                'exists:security_event_categories,id'
            ],
            'cost_to_business_actual' => 'nullable|numeric|min:0',
            'cost_to_business_potential' => 'nullable|numeric|min:0',
            // 'notified_line_management' => 'required|boolean',
            'short_term_mitigation' => 'nullable|string|min:10', // OPTIONAL - Tidak mandatory

            // ===============================================
            // Risk Assessment Validation
            // ===============================================

            // Risk assessment data structure
            'initial_risk' => ['required', 'array'],
            'initial_risk.*.real_severity' => ['nullable', 'integer', 'between:0,5'],
            'initial_risk.*.potential_severity' => ['nullable', 'integer', 'between:0,5'],
            'initial_risk.*.likelihood' => ['nullable', 'integer', 'between:0,5'],

            'residual_risk' => ['required', 'array'],
            'residual_risk.*.potential_severity' => ['nullable', 'integer', 'between:0,5'],
            'residual_risk.*.likelihood' => ['nullable', 'integer', 'between:0,5'],
        ];
    }

    public function messages(): array
    {
        return [
             'security_event_category_id.required' => 'Kategori insiden keamanan wajib dipilih jika tipe insiden adalah Security.',
             // Message untuk short_term_mitigation dihapus karena sudah tidak mandatory
        ];
    }
}
