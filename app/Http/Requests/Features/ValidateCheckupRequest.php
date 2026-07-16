<?php

namespace App\Http\Requests\Features;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Vessel;
use App\Models\Company;

class ValidateCheckupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['nurse', 'super-admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vessel_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // If value is "all", require company_id
                    if ($value === 'all') {
                        if (!$this->has('company_id') || !$this->input('company_id')) {
                            $fail('Company ID is required when validating all vessels.');
                        }
                        return;
                    }

                    // Otherwise, validate as normal vessel ID
                    if (!Vessel::where('id', $value)->exists()) {
                        $fail('The selected vessel does not exist.');
                    }
                },
            ],
            'company_id' => [
                'nullable',
                'exists:companies,id',
                function ($attribute, $value, $fail) {
                    // If vessel_id is "all", company_id is required
                    if ($this->input('vessel_id') === 'all' && !$value) {
                        $fail('Company ID is required when validating all vessels.');
                    }
                },
            ],
            'check_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'validation_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
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
            'vessel_id.required' => 'Vessel harus dipilih.',
            'company_id.required' => 'Company diperlukan saat memvalidasi semua kapal.',
            'company_id.exists' => 'Company yang dipilih tidak valid.',
            'check_date.required' => 'Tanggal pemeriksaan harus diisi.',
            'check_date.date' => 'Format tanggal tidak valid.',
            'check_date.before_or_equal' => 'Tanggal pemeriksaan tidak boleh lebih dari hari ini.',
            'validation_notes.max' => 'Catatan validasi maksimal 1000 karakter.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If vessel_id is empty string or null, convert to 'all'
        if ($this->has('vessel_id')) {
            $vesselId = $this->vessel_id;

            if (is_string($vesselId)) {
                $vesselId = trim($vesselId);

                // Convert empty string to 'all' if company_id is present
                if (empty($vesselId) && $this->has('company_id') && $this->company_id) {
                    $vesselId = 'all';
                }
            }

            $this->merge([
                'vessel_id' => $vesselId ?: 'all',
            ]);
        }
    }
}
