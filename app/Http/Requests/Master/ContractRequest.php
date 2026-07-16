<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class ContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sesuaikan dengan logic otorisasi Anda (misalnya menggunakan Gate atau Policy)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('contract')->id ?? null;

        return [
            'nama_kontraktor' => [
                'required',
                'string',
                'max:255',
                // Unique validation, ignoring current contract ID if editing
                'unique:contracts,nama_kontraktor,' . $id
            ],
            'sap_no' => [
                'required',
                'string',
                'max:50',
                'unique:contracts,sap_no,' . $id
            ],
            'alamat_kantor' => ['nullable', 'string', 'max:1000'],
            'alamat_email' => [
                'nullable',
                'email',
                'max:100',
                'unique:contracts,alamat_email,' . $id
            ],
            'no_tlp_kantor' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_kontraktor.unique' => 'Nama Kontraktor sudah terdaftar.',
            'sap_no.unique' => 'Nomor SAP sudah terdaftar.',
            'alamat_email.unique' => 'Email Kontraktor sudah terdaftar.'
        ];
    }
}
