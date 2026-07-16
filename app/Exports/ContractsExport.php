<?php

namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ContractsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection(): Collection
    {
        return Contract::select([
            'nama_kontraktor',
            'sap_no',
            'alamat_kantor',
            'alamat_email',
            'no_tlp_kantor',
            'created_at'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Nama Kontraktor',
            'Nomor SAP',
            'Alamat Kantor',
            'Email Kantor',
            'Nomor Telepon Kantor',
            'Created At'
        ];
    }
}
