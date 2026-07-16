<?php

namespace App\Http\Requests\Features;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyCheckupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'crew_member_id' => 'required|exists:crew_members,id',
            'vessel_id' => 'required|exists:vessels,id',
            'check_date' => 'required|date|before_or_equal:today',
            'work_area' => 'nullable|string|max:255',

            // Required vital signs
            'pulse_rate' => 'required|integer|min:30|max:200',
            'blood_pressure' => 'required|string|regex:/^\d{2,3}\/\d{2,3}$/',

            // Optional vital signs
            'temperature' => 'nullable|numeric|min:34|max:43',
            'respiratory_rate' => 'nullable|integer|min:10|max:60',
            'blood_sugar_level' => 'nullable|integer|min:30|max:500',
            'oxygen_saturation' => 'nullable|integer|min:70|max:100',

            // Required health status
            'illness_complaints' => 'required|string|max:1000',
            'medications_consumed' => 'required|string|max:1000',

            // Optional fields
            'fatigue_level' => 'nullable|string|max:255',
            'napza_test_result' => 'nullable|in:not_tested,negative,non_negative',
            'romberg_test_result' => 'nullable|in:not_tested,negative,positive',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'crew_member_id.required' => 'Crew member harus dipilih',
            'vessel_id.required' => 'Vessel harus dipilih',
            'pulse_rate.required' => 'Frekuensi nadi wajib diisi',
            'pulse_rate.min' => 'Frekuensi nadi minimal 30 bpm',
            'pulse_rate.max' => 'Frekuensi nadi maksimal 200 bpm',
            'blood_pressure.required' => 'Tekanan darah wajib diisi',
            'blood_pressure.regex' => 'Format tekanan darah tidak valid (contoh: 120/80)',
            'illness_complaints.required' => 'Keluhan kesehatan wajib diisi',
            'medications_consumed.required' => 'Obat-obatan yang dikonsumsi wajib diisi',
        ];
    }
}
