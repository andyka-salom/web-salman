<?php

namespace App\Exports;

use App\Models\EntityFunction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EntityFunctionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        // Ambil data beserta parent untuk referensi
        return EntityFunction::with('parent')->orderBy('level')->orderBy('code')->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Parent Function Code', // Kita gunakan Code untuk referensi parent karena unique
            'Description',
            'Is Active (1=Yes, 0=No)'
        ];
    }

    public function map($entity): array
    {
        return [
            $entity->code,
            $entity->name,
            $entity->parent ? $entity->parent->code : '', // Tampilkan Code parent, bukan ID
            $entity->description,
            $entity->is_active ? 1 : 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Bold header
        ];
    }
}
