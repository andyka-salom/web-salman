<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnsafeConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // SESUAIKAN: Nama di route Anda adalah {unsafeCondition}
        $unsafeCondition = $this->route('unsafeCondition');

        // Ambil ID-nya (mendukung Object Model maupun String UUID)
        $id = is_object($unsafeCondition) ? $unsafeCondition->id : $unsafeCondition;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                // Gunakan $id yang benar agar record ini diabaikan saat validasi unik (Update)
                Rule::unique('unsafe_conditions', 'code')
                    ->ignore($id)
                    ->whereNull('deleted_at')
            ],
            'description' => ['required', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
