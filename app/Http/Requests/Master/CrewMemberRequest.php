<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrewMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $crewMemberId = $this->route('crew_member') ? $this->route('crew_member')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'max:50',
                Rule::unique('crew_members', 'nik')->ignore($crewMemberId)->whereNull('deleted_at')
            ],
            'position' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Crew member name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'nik.required' => 'NIK is required',
            'nik.unique' => 'This NIK is already registered',
            'nik.max' => 'NIK cannot exceed 50 characters',
            'position.max' => 'Position cannot exceed 255 characters',
            'gender.in' => 'Invalid gender selection',
            'birth_date.date' => 'Invalid birth date format',
            'birth_date.before' => 'Birth date must be before today',
            'blood_type.max' => 'Blood type cannot exceed 5 characters',
            'phone.max' => 'Phone number cannot exceed 20 characters',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trim whitespace from string fields
        $this->merge([
            'name' => trim($this->name ?? ''),
            'nik' => trim($this->nik ?? ''),
            'position' => trim($this->position ?? ''),
            'phone' => trim($this->phone ?? ''),
        ]);
    }
}
