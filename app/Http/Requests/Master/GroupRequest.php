<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $groupId = $this->route('group') ? $this->route('group')->id : null;

        return [
            'group_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'group_name')->ignore($groupId)->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'group_name.required' => 'Group name is required',
            'group_name.unique' => 'Group name already exists',
            'group_name.max' => 'Group name must not exceed 255 characters',
        ];
    }

    public function attributes(): array
    {
        return [
            'group_name' => 'group name',
            'description' => 'description',
        ];
    }
}
