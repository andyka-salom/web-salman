<?php

namespace App\Http\Requests\Features;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'target_type' => 'required|in:all_users,specific_users,whatsapp_group,crew_members',
            'target_data' => 'required|array',
            'target_data.*' => 'required',
            'scheduled_at' => 'nullable|date|after:now',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4,mp3,wav|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'message.required' => 'Message is required',
            'target_type.required' => 'Please select target type',
            'target_data.required' => 'Please select at least one recipient',
            'media.max' => 'File size must not exceed 10MB',
        ];
    }
}
