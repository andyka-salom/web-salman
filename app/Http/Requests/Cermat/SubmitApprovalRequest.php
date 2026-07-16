<?php

namespace App\Http\Requests\Cermat;

use App\Models\CermatReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CermatReport $report */
        $report = $this->route('report');

        // Hanya supervisor yang ditugaskan dan statusnya masih AWAITING_APPROVAL (Supervisor)
        return $report->line_supervisor_id === auth()->id()
            && $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_AWAITING;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject', 'no_action'])],
            'supervisor_notes' => [
                'nullable',
                // Catatan wajib jika keputusannya 'reject' atau 'no_action'
                Rule::requiredIf(fn () => in_array($this->decision, ['reject', 'no_action'])),
                'string',
                'max:5000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'supervisor_notes.required' => 'Catatan/alasan wajib diisi jika laporan ditolak atau ditutup (No Action Required).',
            'supervisor_notes.max' => 'Catatan supervisor maksimal 5000 karakter.',
        ];
    }
}
