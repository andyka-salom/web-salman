<?php

namespace App\Http\Requests\Cermat;

use App\Models\CermatReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreActionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CermatReport $report */
        $report = $this->route('report');

        // Action item hanya boleh dibuat jika HSSE sudah REVIEWED atau sudah RECOMMENDED
        return auth()->check() && in_array($report->hsse_status, [
            CermatReport::HSSE_STATUS_REVIEWED,
            CermatReport::HSSE_STATUS_RECOMMENDED
        ]);
    }

    public function rules(): array
    {
        // Memastikan ID diperlakukan sebagai UUID
        return [
            'description' => 'required|string',
            // action_category_id wajib UUID dan ada di tabel action_categories
            'action_category_id' => 'required|uuid|exists:action_categories,id',
            // responsible_id wajib ada di tabel users (asumsi users menggunakan UUID)
            'responsible_id' => 'required|uuid|exists:users,id',
            // Target date harus tanggal dan tidak boleh masa lalu
            'target_date' => 'required|date|after_or_equal:today',
        ];
    }
}
