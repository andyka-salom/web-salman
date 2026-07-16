<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SecurityEventCategory;

class SecurityEventCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    // Coba ambil dari snake_case (standar resource) ATAU camelCase (standar manual/JS)
    // Ini untuk memastikan ID tetap tertangkap apapun format parameternya di web.php
    $resource = $this->route('security_event_category') ?? $this->route('securityEventCategory');

    // Ambil ID-nya (Mendukung Object Model maupun String UUID)
    $categoryId = is_object($resource) ? $resource->id : $resource;

    return [
        'code' => [
            'required',
            'string',
            'max:50',
            Rule::unique('security_event_categories', 'code')
                ->ignore($categoryId) // Sekarang $categoryId tidak akan null saat update
                ->whereNull('deleted_at')
        ],
        'name' => [
            'required',
            'string',
            'max:255'
        ],
        'description' => [
            'nullable',
            'string',
            'max:1000'
        ],
        'is_active' => [
            'nullable',
            'boolean'
        ],
    ];
}

    public function attributes(): array
    {
        return [
            'code' => 'category code',
            'name' => 'category name',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The category code is required.',
            'code.unique' => 'This category code already exists.',
            'code.max' => 'The category code must not exceed 50 characters.',
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 1000 characters.',
        ];
    }
}
