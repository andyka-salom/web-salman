<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EntityFunctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    // SESUAIKAN: Nama di route Anda adalah {entityFunction} (camelCase)
    $entityFunction = $this->route('entityFunction');

    // Ambil ID-nya (mendukung Object Model Binding maupun String UUID)
    $id = is_object($entityFunction) ? $entityFunction->id : $entityFunction;

    return [
        'name' => ['required', 'string', 'max:255'],
        'code' => [
            'required',
            'string',
            'max:50',
            // Gunakan $id yang benar agar tidak bentrok dengan diri sendiri saat update
            Rule::unique('entity_functions', 'code')->ignore($id)->whereNull('deleted_at')
        ],
        'description' => ['nullable', 'string'],
        'parent_id' => [
            'nullable',
            'uuid',
            'exists:entity_functions,id',
            // Custom rule sekarang akan berfungsi karena $id sudah terisi benar
            function ($attribute, $value, $fail) use ($id) {
                if ($value && $value == $id) {
                    $fail('An entity function cannot be its own parent.');
                }
            }
        ],
        'is_active' => ['nullable', 'boolean'],
    ];
}

    /**
     * Optional: Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'The code field is required.',
            'code.unique' => 'This code has already been taken by another function.',
            'parent_id.exists' => 'The selected parent function does not exist.',
        ];
    }
}
