<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Pemantauan Kesehatan Pekerja</title>
    <style>
        @page { margin: 15mm; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .header-table td {
            border: none;
            padding: 2px;
        }
        .header-title {
            font-size: 14px;
            font-weight: bold;
        }
        .footer-notes {
            font-size: 7px;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .text-left {
            text-align: left !important;
        }
        .text-right {
            text-align: right !important;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @if(isset($groupedByDate) && $groupedByDate->count() > 0)
        @foreach($groupedByDate as $date => $checks)
        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            {{-- HEADER --}}
            <table class="header-table">
                <tr>
                    <td class="text-left" style="width: 70%;">
                        Lampiran 1 – TKI No. C8-005/PHE04000/2021-S9-F001<br>
                        Revisi Ke-1
                    </td>
                    <td class="text-right" style="width: 30%; font-weight: bold; font-size: 10px;">
                        PERTAMINA HULU MAHAKAM
                    </td>
                </tr>
            </table>

            <div style="text-align: center; margin: 15px 0;">
                <span class="header-title">FORMULIR PEMANTAUAN KESEHATAN PEKERJA</span>
            </div>

            <table class="header-table" style="margin-bottom: 10px;">
                <tr>
                    <td class="text-left"><strong>Area:</strong> {{ $vesselName ?? '-' }}</td>
                    <td class="text-right"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
                </tr>
            </table>

            {{-- TABEL UTAMA --}}
            <table>
                <thead>
                    <tr>
                        <th style="width: 2%;">No</th>
                        <th style="width: 11%;">NIK</th>
                        <th style="width: 11%;">Nama</th>
                        <th style="width: 9%;">Perusahaan</th>
                        <th style="width: 8%;">Posisi/<br>Pekerjaan</th>
                        <th style="width: 8%;">Area<br>Kerja</th>
                        <th style="width: 8%;">Keluhan<br>Kesehatan</th>
                        <th style="width: 8%;">Obat-obatan<br>yang dikonsumsi</th>
                        <th style="width: 3%;">Suhu</th>
                        <th style="width: 4%;">Frekuensi<br>Nadi</th>
                        <th style="width: 5%;">Tekanan<br>Darah</th>
                        <th style="width: 4%;">Frekuensi<br>Nafas</th>
                        <th style="width: 3%;">*<br>Gula<br>Darah</th>
                        <th style="width: 4%;">**<br>NAPZA</th>
                        <th style="width: 4%;">***<br>Romberg<br>Test</th>
                        <th style="width: 4%;">****<br>Saturasi<br>Oksigen</th>
                        <th style="width: 4%;">Kelelahan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checks as $check)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $check->crewMember->nik ?? '-' }}</td>
                            <td class="text-left">{{ $check->crewMember->name ?? 'N/A' }}</td>
                            <td>{{ $companyName ?? '-' }}</td>
                            <td>{{ $check->crewMember->position ?? '-' }}</td>
                            <td>{{ $check->work_area ?? ($vesselName ?? '-') }}</td>
                            <td class="text-left">{{ $check->illness_complaints ?? '-' }}</td>
                            <td class="text-left">{{ $check->medications_consumed ?? '-' }}</td>
                            <td>{{ $check->temperature ?? '-' }}</td>
                            <td>{{ $check->pulse_rate ?? '-' }}</td>
                            <td>{{ $check->blood_pressure ?? '-' }}</td>
                            <td>{{ $check->respiratory_rate ?? '-' }}</td>
                            <td>{{ $check->blood_sugar_level ?? '-' }}</td>
                            <td>{{ $check->napza_test_result ? str_replace('_', ' ', Str::title($check->napza_test_result)) : '-' }}</td>
                            <td>{{ $check->romberg_test_result ? str_replace('_', ' ', Str::title($check->romberg_test_result)) : '-' }}</td>
                            <td>{{ $check->oxygen_saturation ?? '-' }}</td>
                            <td>{{ $check->fatigue_level ? ucfirst($check->fatigue_level) : '-' }}</td>
                            <td class="text-left">{{ $check->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                    {{-- Tambahkan baris kosong agar tabel penuh jika data sedikit --}}
                    @for ($i = $checks->count(); $i < 15; $i++)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- FOOTER --}}
            <div class="footer-notes">
                <p>
                    *) Jika ada riwayat diabetes mellitus <br>
                    **) Jika dicurigai penggunaan <br>
                    ***) Jika bekerja di ketinggian <br>
                    ****) Jika bekerja di ruang terbatas dan TBA
                </p>
            </div>
        </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 50px;">
            <h2>Tidak ada data untuk ditampilkan</h2>
            <p>Periode: {{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }}</p>
        </div>
    @endif
</body>
</html>
