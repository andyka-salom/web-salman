<?php

namespace App\Imports;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ContactsImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithChunkReading
{
    use SkipsFailures;

    public int $importedCount = 0;
    public int $skippedCount  = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name   = trim($row['name'] ?? $row['nama'] ?? '');
            $number = trim($row['whatsapp_number'] ?? $row['nomor_whatsapp'] ?? $row['no_wa'] ?? '');

            if (empty($name) || empty($number)) {
                $this->skippedCount++;
                continue;
            }

            $normalized = Contact::normalizeWhatsappNumber($number);

            // Skip exact duplicate (same name + number) in this import run
            $exists = Contact::withTrashed()
                ->where('name', $name)
                ->where('whatsapp_number', $normalized)
                ->exists();

            if ($exists) {
                $this->skippedCount++;
                continue;
            }

            Contact::create([
                'created_by'      => Auth::id(),
                'name'            => $name,
                'whatsapp_number' => $normalized,
                'position'        => trim($row['position'] ?? $row['jabatan'] ?? '') ?: null,
                'notes'           => trim($row['notes'] ?? $row['keterangan'] ?? '') ?: null,
                'is_active'       => true,
            ]);

            $this->importedCount++;
        }
    }

    public function rules(): array
    {
        return [
            // heading-row based, minimal rules – heavy validation in collection()
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
