<?php

namespace App\Http\Requests\Features;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required', 'string', 'max:500',
            ],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'published_at' => ['nullable', 'date'],

            // PERUBAHAN DISINI:
            // 1. image: memastikan file adalah gambar
            // 2. mimes: membatasi format (jpg, png, dll)
            // 3. max:2048: membatasi ukuran maksimal 2MB (2048 KB)
            'media' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],

            // Kita tidak perlu validasi media_type dari input user lagi karena sudah pasti image
            // 'media_type' => ['nullable', 'in:image'],

            // Flags
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
