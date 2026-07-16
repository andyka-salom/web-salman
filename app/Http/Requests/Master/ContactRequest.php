<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via policy
    }

    public function rules(): array
    {
        $contactId = $this->route('contact')?->id;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'whatsapp_number' => [
                'required',
                'string',
                'min:9',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'position' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Nama kontak wajib diisi.',
            'name.min'                   => 'Nama minimal 2 karakter.',
            'name.max'                   => 'Nama maksimal 255 karakter.',
            'whatsapp_number.required'   => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.min'        => 'Nomor WhatsApp terlalu pendek (minimal 9 digit).',
            'whatsapp_number.max'        => 'Nomor WhatsApp terlalu panjang (maksimal 20 digit).',
            'whatsapp_number.regex'      => 'Format nomor tidak valid. Gunakan angka saja.',
            'position.max'               => 'Jabatan maksimal 255 karakter.',
            'notes.max'                  => 'Keterangan maksimal 2000 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean whatsapp number before validation
        if ($this->has('whatsapp_number')) {
            $this->merge([
                'whatsapp_number' => preg_replace('/[\s\-()]/', '', $this->whatsapp_number),
            ]);
        }
    }
}
