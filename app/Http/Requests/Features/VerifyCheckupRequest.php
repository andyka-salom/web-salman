<?php

namespace App\Http\Requests\Features;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Vessel;

class VerifyCheckupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('koordinator');
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
                            $fail('Company ID is required when verifying all vessels.');
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
                        $fail('Company ID is required when verifying all vessels.');
                    }
                },
            ],
            'check_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'verification_notes' => [
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
            'check_date.required' => 'Tanggal pemeriksaan harus diisi.',
            'check_date.date' => 'Format tanggal tidak valid.',
            'check_date.before_or_equal' => 'Tanggal pemeriksaan tidak boleh lebih dari hari ini.',
            'verification_notes.max' => 'Catatan verifikasi maksimal 1000 karakter.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure vessel_id is trimmed if it's a string
        if ($this->has('vessel_id') && is_string($this->vessel_id)) {
            $this->merge([
                'vessel_id' => trim($this->vessel_id),
            ]);
        }
    }
}
