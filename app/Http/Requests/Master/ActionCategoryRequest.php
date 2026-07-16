<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // SESUAIKAN: Nama di route Anda adalah {actionCategory}
        $actionCategory = $this->route('actionCategory');

        // Ambil ID-nya (baik jika berupa object model maupun string UUID)
        $id = is_object($actionCategory) ? $actionCategory->id : $actionCategory;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                // Gunakan $id yang sudah didapat untuk di-ignore
                Rule::unique('action_categories', 'code')->ignore($id)->whereNull('deleted_at')
            ],
            'name' => ['required', 'string', 'max:255'],
            'duration_range' => [
                'required',
                'string',
                'max:15',
                Rule::in(['<7', '8-15', '16-30', '>30'])
            ],
            'priority' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            // 'color.regex' dihapus
            'duration_range.in' => 'Rentang durasi harus salah satu dari: <7, 8-15, 16-30, atau >30.',
        ];
    }
}
