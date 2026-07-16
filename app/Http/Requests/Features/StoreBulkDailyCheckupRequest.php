<?php

namespace App\Http\Requests\Features;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkDailyCheckupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['crew-kapal', 'koordinator', 'nurse', 'super-admin']);
    }

    public function rules(): array
    {
        return [
            'vessel_id' => 'required|uuid|exists:vessels,id',
            'check_date' => 'required|date|before_or_equal:today',
            'work_area' => 'nullable|string|max:255',
            'crew_members' => 'required|array|min:1',
            'crew_members.*' => 'required|uuid|exists:crew_members,id',
            'temperature' => 'nullable|array',
            'temperature.*' => 'nullable|numeric|between:34,43',
            'pulse_rate' => 'required|array',
            'pulse_rate.*' => 'required|integer|between:30,200',
            'blood_pressure' => 'required|array',
            'blood_pressure.*' => 'required|string|max:10|regex:/^\d{2,3}\/\d{2,3}$/',
            'respiratory_rate' => 'nullable|array',
            'respiratory_rate.*' => 'nullable|integer|between:10,60',
            'blood_sugar_level' => 'nullable|array',
            'blood_sugar_level.*' => 'nullable|integer|between:30,500',
            'oxygen_saturation' => 'nullable|array',
            'oxygen_saturation.*' => 'nullable|integer|between:70,100',
            'illness_complaints' => 'required|array',
            'illness_complaints.*' => 'required|string|max:65535',
            'medications_consumed' => 'required|array',
            'medications_consumed.*' => 'required|string|max:65535',
            'fatigue_level' => 'nullable|array',
            'fatigue_level.*' => 'nullable|in:ringan,sedang,berat',
            'napza_test_result' => 'nullable|array',
            'napza_test_result.*' => 'nullable|in:non-negative,negative,not_tested',
            'romberg_test_result' => 'nullable|array',
            'romberg_test_result.*' => 'nullable|in:positive,negative,not_tested',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'crew_members.required' => 'Minimal satu crew harus dipilih.',
            'crew_members.min' => 'Minimal satu crew harus dipilih.',
            'pulse_rate.*.required' => 'Frekuensi nadi untuk semua crew harus diisi.',
            'pulse_rate.*.between' => 'Frekuensi nadi tidak valid.',
            'blood_pressure.*.required' => 'Tekanan darah untuk semua crew harus diisi.',
            'blood_pressure.*.regex' => 'Format tekanan darah harus xxx/xxx.',
            'illness_complaints.*.required' => 'Keluhan kesehatan untuk semua crew harus diisi.',
            'medications_consumed.*.required' => 'Obat yang dikonsumsi untuk semua crew harus diisi.',
        ];
    }

    /**
     * Get validated data with transformation to crew_checkups format
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Transform data to crew_checkups format
        if (isset($validated['crew_members']) && is_array($validated['crew_members'])) {
            $crewCheckups = [];

            foreach ($validated['crew_members'] as $index => $crewMemberId) {
                $crewCheckups[] = [
                    'crew_member_id' => $crewMemberId,
                    'work_area' => $validated['work_area'] ?? null,
                    'temperature' => $validated['temperature'][$index] ?? null,
                    'pulse_rate' => $validated['pulse_rate'][$index] ?? null,
                    'blood_pressure' => $validated['blood_pressure'][$index] ?? null,
                    'respiratory_rate' => $validated['respiratory_rate'][$index] ?? null,
                    'blood_sugar_level' => $validated['blood_sugar_level'][$index] ?? null,
                    'oxygen_saturation' => $validated['oxygen_saturation'][$index] ?? null,
                    'illness_complaints' => $validated['illness_complaints'][$index] ?? 'Tidak ada keluhan',
                    'medications_consumed' => $validated['medications_consumed'][$index] ?? 'Tidak ada',
                    'fatigue_level' => $validated['fatigue_level'][$index] ?? null,
                    'napza_test_result' => $validated['napza_test_result'][$index] ?? 'not_tested',
                    'romberg_test_result' => $validated['romberg_test_result'][$index] ?? 'not_tested',
                    'remarks' => $validated['remarks'][$index] ?? null,
                ];
            }

            $validated['crew_checkups'] = $crewCheckups;
        }

        return $validated;
    }
}
