<?php

namespace App\Http\Requests\Cermat;

use App\Models\ActionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tetap izinkan semua user yang terotentikasi untuk mencoba update.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    ActionItem::STATUS_IN_PROGRESS,
                    ActionItem::STATUS_COMPLETED,
                    ActionItem::STATUS_CANT_DO,
                ]),
            ],
            // Catatan menjadi wajib jika statusnya 'Completed' atau 'Cannot Do'
            'completion_notes' => [
                Rule::requiredIf(fn () => in_array($this->status, [
                    ActionItem::STATUS_COMPLETED,
                    ActionItem::STATUS_CANT_DO,
                ])),
                'string',
                'nullable',
            ],
            // [BARU] Validasi untuk file bukti foto
            'proof_photos'   => 'nullable|array|max:5', // Batasi maksimal 5 foto per upload
            'proof_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // Setiap file harus gambar, maks 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'proof_photos.max' => 'Anda hanya dapat mengunggah maksimal 5 foto sekaligus.',
            'proof_photos.*.image' => 'File yang diunggah harus berupa gambar.',
            'proof_photos.*.mimes' => 'Format gambar yang didukung adalah jpeg, png, jpg, atau gif.',
            'proof_photos.*.max' => 'Ukuran maksimal per gambar adalah 5MB.',
        ];
    }
}
