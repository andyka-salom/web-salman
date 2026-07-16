<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Mengambil parameter 'area' dari route
        $area = $this->route('area');

        // Cek apakah $area adalah object (Model Binding) atau hanya string UUID
        $id = is_object($area) ? $area->id : $area;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                // Gunakan $id untuk mengabaikan record yang sedang di-update
                Rule::unique('areas', 'code')->ignore($id)->whereNull('deleted_at')
            ],
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'uuid', 'exists:companies,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
