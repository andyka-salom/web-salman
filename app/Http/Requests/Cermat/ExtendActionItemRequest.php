<?php

namespace App\Http\Requests\Cermat;

use App\Models\ActionItem;
use Illuminate\Foundation\Http\FormRequest;

class ExtendActionItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var ActionItem $actionItem */
        $actionItem = $this->route('actionItem');

        // Cek apakah action item dapat diperpanjang (max 3 kali dan status belum selesai/tidak dapat dilakukan)
        if (!$actionItem->canExtend()) {
            return false;
        }

        // Sebaiknya hanya HSSE atau PIC yang berwenang
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ActionItem $actionItem */
        $actionItem = $this->route('actionItem');

        // Ambil tanggal target efektif saat ini
        $afterDate = $actionItem->current_target_date
            ? $actionItem->current_target_date->format('Y-m-d')
            : $actionItem->target_date->format('Y-m-d');

        return [
            // new_target_date harus setelah tanggal target efektif saat ini
            'new_target_date' => ['required', 'date', 'after:' . $afterDate],
            'extend_reason' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_target_date.after' => 'Tanggal target baru harus setelah tanggal target saat ini.',
            'extend_reason.min' => 'Alasan perpanjangan harus memiliki minimal 10 karakter.',
        ];
    }
}
