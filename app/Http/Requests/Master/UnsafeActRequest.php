<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnsafeActRequest extends FormRequest
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
    // SESUAIKAN: Nama di route Anda adalah {unsafeAct}
    $unsafeAct = $this->route('unsafeAct');

    // Ambil ID-nya (mendukung Object Model maupun String UUID)
    $id = is_object($unsafeAct) ? $unsafeAct->id : $unsafeAct;

    return [
        'code' => [
            'required',
            'string',
            'max:50',
            // Gunakan $id yang benar untuk mengabaikan record saat update
            Rule::unique('unsafe_acts', 'code')
                ->ignore($id)
                ->whereNull('deleted_at')
        ],
        'description' => [
            'required',
            'string',
            'max:500'
        ],
        'is_active' => [
            'nullable',
            'boolean'
        ],
    ];
}
    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'unsafe act code',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'The unsafe act code is required.',
            'code.unique' => 'This unsafe act code already exists.',
            'code.max' => 'The unsafe act code must not exceed 50 characters.',
            'description.required' => 'The description is required.',
            'description.max' => 'The description must not exceed 500 characters.',
        ];
    }
}
