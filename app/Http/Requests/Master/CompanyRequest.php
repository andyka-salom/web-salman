<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // Mengambil parameter 'company' dari route
        $company = $this->route('company');

        // Jika $company adalah object model, ambil id-nya. Jika string (UUID), gunakan langsung.
        $companyId = is_object($company) ? $company->id : $company;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('companies', 'name')
                    ->ignore($companyId)
                    ->whereNull('deleted_at')
            ],
            'address' => 'required|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'company name',
            'logo' => 'company logo',
            'is_active' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The company name is required.',
            'name.unique' => 'A company with this name already exists.',
            'address.required' => 'The company address is required.',
            'logo.image' => 'The logo must be an image file.',
            'logo.max' => 'The logo must not exceed 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure is_active is boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
