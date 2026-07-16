<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoverTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // SESUAIKAN: Nama di route Anda adalah {coverTemplate}
        $resource = $this->route('coverTemplate');

        // Dapatkan ID (Mendukung Object Model maupun String UUID)
        $id = is_object($resource) ? $resource->id : $resource;

        // Jika ada ID (Update), maka nullable. Jika tidak ada (Create), maka required.
        $fileRule = $id ? 'nullable' : 'required';

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Sebaiknya tambahkan validasi unik untuk nama agar tidak bentrok
                Rule::unique('cover_templates', 'name')->ignore($id)->whereNull('deleted_at')
            ],
            'description' => ['nullable', 'string', 'max:1000'],

            // Menggunakan $fileRule yang sudah dinamis
            'cover_image' => [$fileRule, 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            'page_image'  => [$fileRule, 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
