<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class VesselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|uuid|exists:companies,id',
            'coordinator_id' => 'required|uuid|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',  // Now required
            'valid_until' => 'nullable|date|after:today',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'company_id.exists' => 'Selected company does not exist',
            'coordinator_id.required' => 'Coordinator is required',
            'coordinator_id.exists' => 'Selected coordinator does not exist',
            'name.required' => 'Vessel name is required',
            'type.required' => 'Vessel type is required',
            'type.max' => 'Vessel type cannot exceed 100 characters',
            'valid_until.after' => 'Valid until date must be a future date',
        ];
    }
}
