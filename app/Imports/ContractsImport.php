<?php

namespace App\Imports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class ContractsImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pastikan nama header di file Excel sesuai dengan yang digunakan di sini (case-insensitive default)
        return new Contract([
            'nama_kontraktor' => $row['nama_kontraktor'],
            // Menggunakan key yang disarankan untuk import guide
            'sap_no' => $row['nomor_sap'],
            'alamat_kantor' => $row['alamat_kantor'] ?? null,
            'alamat_email' => $row['email_kantor'] ?? null,
            'no_tlp_kantor' => $row['nomor_telepon_kantor'] ?? null,
        ]);
    }

    /**
     * Define validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'nama_kontraktor' => 'required|string|max:255|unique:contracts,nama_kontraktor',
            'nomor_sap' => 'required|string|max:50|unique:contracts,sap_no',
            'email_kantor' => 'nullable|email|max:100|unique:contracts,alamat_email',
        ];
    }

    /**
     * Custom validation messages (optional)
     */
    public function customValidationMessages()
    {
        return [
            'nama_kontraktor.unique' => 'Nama Kontraktor pada baris ini sudah terdaftar.',
            'nomor_sap.unique' => 'Nomor SAP pada baris ini sudah terdaftar.',
            'email_kantor.unique' => 'Email Kantor pada baris ini sudah terdaftar.'
        ];
    }
}
