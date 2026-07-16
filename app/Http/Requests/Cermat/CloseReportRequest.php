<?php

namespace App\Http\Requests\Cermat;

use App\Models\CermatReport;
use Illuminate\Foundation\Http\FormRequest;

class CloseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CermatReport $report */
        $report = $this->route('report');

        // Hanya izinkan penutupan jika laporan dalam status 'Awaiting Close-out'.
        return $report->status === CermatReport::STATUS_AWAITING_CLOSEOUT;
    }

    public function rules(): array
    {
        return [
            'close_out_description' => 'required|string|max:225',
        ];
    }
}
