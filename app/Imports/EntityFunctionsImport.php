<?php

namespace App\Imports;

use App\Models\EntityFunction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class EntityFunctionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Cari Parent ID berdasarkan Parent Code yang diinput di Excel
            $parentId = null;
            $level = 0;

            if (!empty($row['parent_function_code'])) {
                $parent = EntityFunction::where('code', $row['parent_function_code'])->first();
                if ($parent) {
                    $parentId = $parent->id;
                    $level = $parent->level + 1;
                }
            }

            // 2. Update jika ada (berdasarkan code), Create jika belum ada
            $entity = EntityFunction::updateOrCreate(
                ['code' => $row['code']], // Kunci pencarian
                [
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'parent_id' => $parentId,
                    'level' => $level,
                    'is_active' => isset($row['is_active_1yes_0no']) ? (bool)$row['is_active_1yes_0no'] : true,
                ]
            );

            // 3. Update Materialized Path (Penting untuk hirarki)
            $entity->updatePath();
        }
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            // parent_function_code boleh null
        ];
    }
}
