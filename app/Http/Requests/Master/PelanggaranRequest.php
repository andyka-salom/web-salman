<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Pelanggaran;

class PelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Mengambil parameter 'pelanggaran' dari route
        $pelanggaran = $this->route('pelanggaran');

        // Cara paling aman mendapatkan ID/UUID baik dari Object maupun String
        $id = is_object($pelanggaran) ? $pelanggaran->id : $pelanggaran;

        return [
            'sequence_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('pelanggaran', 'sequence_number')
                    ->ignore($id) // Pastikan $id ini tidak null saat update
                    ->whereNull('deleted_at')
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
