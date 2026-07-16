<?php

namespace App\Imports;

use App\Models\CrewAssessment;
use App\Models\CrewMember;
use App\Models\Company;
use App\Models\Vessel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CrewAssessmentImport implements ToCollection, WithMultipleSheets
{
    public int $importedCount = 0;
    public int $skippedCount  = 0;
    private ?Carbon $uniqueTime = null;

    public function sheets(): array
    {
        return [
            'Perwira' => $this,
        ];
    }

    public function collection(Collection $rows): void
    {
        // Skip first 10 rows (headers are rows 1 to 10)
        $rows = $rows->slice(10);

        foreach ($rows as $row) {
            $name = trim($row[3] ?? '');
            if (empty($name)) {
                $this->skippedCount++;
                continue;
            }

            // 1. Find Company (Column T, index 19)
            $companyName = trim($row[19] ?? '');
            $company = null;
            $companyNameText = null;
            if (!empty($companyName)) {
                $company = Company::where('name', $companyName)->first();
                if (!$company) {
                    $company = Company::where('name', 'like', '%' . $companyName . '%')->first();
                }
                if (!$company) {
                    $companyNameText = $companyName;
                }
            }

            // 2. Find Vessel (Column I, index 8)
            $vesselName = trim($row[8] ?? '');
            $vessel = null;
            $vesselNameText = null;
            if (!empty($vesselName)) {
                if ($company) {
                    $vessel = Vessel::where('name', $vesselName)
                        ->where('company_id', $company->id)
                        ->first();
                } else {
                    $vessel = Vessel::where('name', $vesselName)->first();
                }
                if (!$vessel) {
                    $vesselNameText = $vesselName;
                }
            }

            // 3. Find CrewMember (Nama in Column D / index 3, Tanggal Lahir in Column E / index 4)
            $coc = trim($row[5] ?? '') ?: null;
            $positionProposed = trim($row[6] ?? '') ?: null;

            $crewMember = CrewMember::where('name', $name)->first();
            $crewNameText = null;

            if (!$crewMember) {
                $crewNameText = $name;
            } else {
                // Update existing crew member details
                $crewMember->update([
                    'company_id' => $company?->id ?? $crewMember->company_id,
                    'position'   => $coc ?: ($positionProposed ?: $crewMember->position),
                ]);
            }

            // Assign crew member to vessel (only if both are verified master records)
            if ($vessel && $crewMember) {
                if (!$crewMember->isAssignedToVessel($vessel->id)) {
                    // Unassign current vessel assignment manually with unique timestamps
                    $activeAssignments = \App\Models\VesselAssignment::where('crew_member_id', $crewMember->id)
                        ->where('is_active', true)
                        ->whereNull('unassigned_at')
                        ->get();

                    foreach ($activeAssignments as $assignment) {
                        $assignment->unassigned_at = $this->getUniqueTimestamp();
                        $assignment->is_active = false;
                        $assignment->notes = $assignment->notes 
                            ? $assignment->notes . "\n" . 'Imported via Crew Assessment' 
                            : 'Imported via Crew Assessment';
                        $assignment->save();
                    }

                    // Create new assignment with unique timestamp
                    \App\Models\VesselAssignment::create([
                        'vessel_id'      => $vessel->id,
                        'crew_member_id' => $crewMember->id,
                        'assigned_by'    => Auth::id() ?? 1,
                        'assigned_at'    => $this->getUniqueTimestamp(),
                        'is_active'      => true,
                        'unassigned_at'  => null,
                        'notes'          => 'Imported via Crew Assessment',
                    ]);
                }
            }

            // 4. Parse Assessment Date (Column N, index 13)
            $assessmentDateRaw = $row[13] ?? '';
            $assessmentDate = $this->parseExcelDate($assessmentDateRaw);

            if (empty($assessmentDate)) {
                Log::warning("Skipped row due to invalid assessment date: Name: {$name}");
                $this->skippedCount++;
                continue;
            }

            $certificateNumber = trim($row[2] ?? '') ?: null;
            $positionType = trim($row[7] ?? '') ?: null;
            $expPertamina = trim($row[9] ?? '') ?: null;
            $expMaster = trim($row[10] ?? '') ?: null;
            $expOutside = trim($row[11] ?? '') ?: null;
            $mevType = trim($row[12] ?? '') ?: null;
            $assessorMar = trim($row[14] ?? '') ?: null;
            $assessorHse = trim($row[15] ?? '') ?: null;
            $assessorFmc = trim($row[16] ?? '') ?: null;
            $result = trim($row[17] ?? '') ?: null;
            $notes = trim($row[18] ?? '') ?: null;

            // 5. Create or Update CrewAssessment
            if ($crewMember) {
                $existingAssessment = CrewAssessment::where('crew_member_id', $crewMember->id)
                    ->where('assessment_date', $assessmentDate)
                    ->first();
            } else {
                $existingAssessment = CrewAssessment::where('crew_name_text', $name)
                    ->where('assessment_date', $assessmentDate)
                    ->first();
            }

            if ($existingAssessment) {
                $existingAssessment->update([
                    'company_id'           => $company?->id,
                    'company_name_text'    => $companyNameText,
                    'vessel_id'            => $vessel?->id,
                    'vessel_name_text'     => $vesselNameText,
                    'certificate_number'   => $certificateNumber,
                    'coc'                  => $coc,
                    'position_proposed'    => $positionProposed,
                    'position_type'        => $positionType,
                    'experience_pertamina' => $expPertamina,
                    'experience_master'    => $expMaster,
                    'experience_outside'   => $expOutside,
                    'mev_type'             => $mevType,
                    'assessor_mar'         => $assessorMar,
                    'assessor_hse'         => $assessorHse,
                    'assessor_fmc'         => $assessorFmc,
                    'result'               => $result,
                    'notes'                => $notes,
                    'crew_member_id'       => $crewMember?->id,
                    'crew_name_text'       => $crewNameText,
                ]);
            } else {
                CrewAssessment::create([
                    'company_id'           => $company?->id,
                    'company_name_text'    => $companyNameText,
                    'vessel_id'            => $vessel?->id,
                    'vessel_name_text'     => $vesselNameText,
                    'crew_member_id'       => $crewMember?->id,
                    'crew_name_text'       => $crewNameText,
                    'created_by'           => Auth::id() ?? 1,
                    'certificate_number'   => $certificateNumber,
                    'coc'                  => $coc,
                    'position_proposed'    => $positionProposed,
                    'position_type'        => $positionType,
                    'experience_pertamina' => $expPertamina,
                    'experience_master'    => $expMaster,
                    'experience_outside'   => $expOutside,
                    'mev_type'             => $mevType,
                    'assessment_date'      => $assessmentDate,
                    'assessor_mar'         => $assessorMar,
                    'assessor_hse'         => $assessorHse,
                    'assessor_fmc'         => $assessorFmc,
                    'result'               => $result,
                    'notes'                => $notes,
                    'status'               => 'active',
                ]);
            }

            $this->importedCount++;
        }
    }

    private function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Exception $e) {
                // fallback
            }
        }

        $valueStr = trim($value);
        $formats = ['d.m.Y', 'd/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $valueStr)->format('Y-m-d');
            } catch (\Exception $e) {
                // continue
            }
        }

        try {
            return Carbon::parse($valueStr)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Could not parse date: " . $valueStr);
            return null;
        }
    }

    private function getUniqueTimestamp(): Carbon
    {
        if ($this->uniqueTime === null) {
            $this->uniqueTime = now();
        } else {
            $this->uniqueTime = $this->uniqueTime->copy()->addSecond();
        }
        return $this->uniqueTime;
    }
}
