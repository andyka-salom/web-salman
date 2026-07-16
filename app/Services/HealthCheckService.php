<?php

namespace App\Services;

use App\Models\HealthCheck;
use App\Models\Vessel;
use App\Models\CrewMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthCheckService
{
    /**
     * Create single health check
     */
    public function createHealthCheck(array $data, User $reporter): HealthCheck
    {
        // Get crew and vessel for snapshot
        $crew = CrewMember::find($data['crew_member_id']);
        $vessel = Vessel::find($data['vessel_id']);

        $healthCheckData = [
            'crew_member_id' => $data['crew_member_id'],
            'vessel_id' => $data['vessel_id'],
            'crew_name_snapshot' => $crew?->name,
            'crew_position_snapshot' => $crew?->position,
            'reported_by' => $reporter->id,
            'checked_at' => $data['checked_at'] ?? now(),
            'check_date' => $data['check_date'],
            'work_area' => $data['work_area'] ?? null,
            'illness_complaints' => $data['illness_complaints'] ?? 'Tidak ada keluhan',
            'medications_consumed' => $data['medications_consumed'] ?? 'Tidak ada',
            'temperature' => $data['temperature'] ?? null,
            'pulse_rate' => $data['pulse_rate'] ?? null,
            'blood_pressure' => $data['blood_pressure'] ?? null,
            'respiratory_rate' => $data['respiratory_rate'] ?? null,
            'blood_sugar_level' => $data['blood_sugar_level'] ?? null,
            'oxygen_saturation' => $data['oxygen_saturation'] ?? null,
            'fatigue_level' => $data['fatigue_level'] ?? null,
            'napza_test_result' => $data['napza_test_result'] ?? 'not_tested',
            'romberg_test_result' => $data['romberg_test_result'] ?? 'not_tested',
            'remarks' => $data['remarks'] ?? null,
            'status' => 'pending',
        ];

        $healthCheck = HealthCheck::create($healthCheckData);

        // Auto-detect health issues
        $hasIssue = $healthCheck->hasAbnormalVitals();
        if ($hasIssue) {
            $healthCheck->update(['has_health_issue' => true]);
        }

        return $healthCheck->fresh();
    }

    /**
     * Create bulk health checks
     */
    public function createBulkHealthChecks(array $data, User $reporter): array
    {
        $vessel = Vessel::findOrFail($data['vessel_id']);
        $checkDate = $data['check_date'];

        $success = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        foreach ($data['crew_checkups'] as $crewCheckup) {
            try {
                // Check if already exists
                $exists = HealthCheck::where('crew_member_id', $crewCheckup['crew_member_id'])
                    ->whereDate('check_date', $checkDate)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Verify crew is assigned to vessel
                if (!$vessel->hasCrewMember($crewCheckup['crew_member_id'])) {
                    $failed++;
                    $errors[] = "Crew {$crewCheckup['crew_member_id']} not assigned to vessel";
                    continue;
                }

                // Merge common data with individual crew data
                $checkupData = array_merge([
                    'vessel_id' => $data['vessel_id'],
                    'check_date' => $checkDate,
                    'checked_at' => now(),
                ], $crewCheckup);

                $this->createHealthCheck($checkupData, $reporter);
                $success++;

            } catch (\Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
                Log::error('Bulk checkup failed for crew', [
                    'crew_id' => $crewCheckup['crew_member_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'success' => $success,
            'skipped' => $skipped,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Verify health checks (Coordinator)
     * UPDATED: Support verifying all vessels in a company
     */
    public function verifyHealthChecks(string $vesselId, string $checkDate, ?string $notes, User $verifier, ?string $companyId = null): array
    {
        // Support "all" vessels
        if ($vesselId === 'all' && $companyId) {
            $vessels = Vessel::where('company_id', $companyId)
                ->where('is_active', true)
                ->pluck('id');

            $totalCount = 0;
            foreach ($vessels as $vesselIdItem) {
                $updated = HealthCheck::where('vessel_id', $vesselIdItem)
                    ->whereDate('check_date', $checkDate)
                    ->where('status', 'pending')
                    ->whereNull('verified_by')
                    ->update([
                        'status' => 'reviewed',
                        'verified_by' => $verifier->id,
                        'verified_at' => now(),
                        'verification_notes' => $notes,
                    ]);
                $totalCount += $updated;
            }

            return ['count' => $totalCount];
        }

        // Single vessel
        $updated = HealthCheck::where('vessel_id', $vesselId)
            ->whereDate('check_date', $checkDate)
            ->where('status', 'pending')
            ->whereNull('verified_by')
            ->update([
                'status' => 'reviewed',
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'verification_notes' => $notes,
            ]);

        return ['count' => $updated];
    }

    /**
     * Validate health checks (Nurse/Admin)
     * UPDATED: Bisa validasi status pending atau reviewed, support all vessels
     */
    public function validateHealthChecks(string $vesselId, string $checkDate, ?string $notes, User $validator, ?string $companyId = null): array
    {
        // Support "all" vessels
        if ($vesselId === 'all' && $companyId) {
            $vessels = Vessel::where('company_id', $companyId)
                ->where('is_active', true)
                ->pluck('id');

            $totalCount = 0;
            foreach ($vessels as $vesselIdItem) {
                // UPDATED: Bisa validasi pending atau reviewed
                $updated = HealthCheck::where('vessel_id', $vesselIdItem)
                    ->whereDate('check_date', $checkDate)
                    ->whereIn('status', ['pending', 'reviewed'])
                    ->whereNull('validated_by')
                    ->update([
                        'status' => 'validated',
                        'validated_by' => $validator->id,
                        'validated_at' => now(),
                        'validation_notes' => $notes,
                    ]);
                $totalCount += $updated;
            }

            return ['count' => $totalCount];
        }

        // Single vessel - UPDATED: Bisa validasi pending atau reviewed
        $updated = HealthCheck::where('vessel_id', $vesselId)
            ->whereDate('check_date', $checkDate)
            ->whereIn('status', ['pending', 'reviewed'])
            ->whereNull('validated_by')
            ->update([
                'status' => 'validated',
                'validated_by' => $validator->id,
                'validated_at' => now(),
                'validation_notes' => $notes,
            ]);

        return ['count' => $updated];
    }

    /**
     * Get vessel checkup statistics for a specific date
     */
    public function getVesselCheckupStats(string $vesselId, string $date): array
    {
        $vessel = Vessel::findOrFail($vesselId);

        // Get current crew count
        $totalCrew = $vessel->currentCrewMembers()
            ->where('crew_members.is_active', true)
            ->count();

        // Get checkups for the date
        $checkups = HealthCheck::where('vessel_id', $vesselId)
            ->whereDate('check_date', $date)
            ->get();

        $completed = $checkups->count();
        $pending = max(0, $totalCrew - $completed);

        $statusCounts = $checkups->countBy('status');
        $warnings = $checkups->where('has_health_issue', true)->count();
        $followUps = $checkups->where('requires_follow_up', true)->count();

        // BARU: Hitung kondisi kritis
        $critical = $checkups->filter(function($check) {
            return $check->hasCriticalCondition();
        })->count();

        return [
            'crew_total' => $totalCrew,
            'completed' => $completed,
            'pending' => $pending,
            'not_checked' => $pending,
            'completion_rate' => $totalCrew > 0 ? round(($completed / $totalCrew) * 100, 1) : 0,
            'pending' => $statusCounts->get('pending', 0),
            'reviewed' => $statusCounts->get('reviewed', 0),
            'validated' => $statusCounts->get('validated', 0),
            'warnings' => $warnings,
            'critical' => $critical, // BARU
            'follow_ups' => $followUps,
        ];
    }

    /**
     * Get daily statistics across all vessels or specific filters
     */
    public function getDailyStats(string $date, ?string $companyId = null, ?string $vesselId = null): array
    {
        $query = HealthCheck::whereDate('check_date', $date);

        if ($companyId) {
            $query->whereHas('vessel', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        if ($vesselId) {
            $query->where('vessel_id', $vesselId);
        }

        $checkups = $query->get();

        // BARU: Hitung kondisi kritis
        $critical = $checkups->filter(function($check) {
            return $check->hasCriticalCondition();
        })->count();

        return [
            'total' => $checkups->count(),
            'pending' => $checkups->where('status', 'pending')->count(),
            'reviewed' => $checkups->where('status', 'reviewed')->count(),
            'validated' => $checkups->where('status', 'validated')->count(),
            'warnings' => $checkups->where('has_health_issue', true)->count(),
            'critical' => $critical, // BARU
            'follow_ups' => $checkups->where('requires_follow_up', true)->count(),
        ];
    }

    /**
     * Get crew health history
     */
    public function getCrewHealthHistory(string $crewMemberId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $checkups = HealthCheck::where('crew_member_id', $crewMemberId)
            ->where('check_date', '>=', $startDate)
            ->orderBy('check_date', 'desc')
            ->with(['vessel', 'reporter', 'verifier', 'validator'])
            ->get();

        $totalCheckups = $checkups->count();
        $healthIssues = $checkups->where('has_health_issue', true)->count();

        return [
            'total_checkups' => $totalCheckups,
            'health_issues' => $healthIssues,
            'issue_rate' => $totalCheckups > 0 ? round(($healthIssues / $totalCheckups) * 100, 1) : 0,
            'checkups' => $checkups,
            'average_temperature' => round($checkups->avg('temperature'), 1),
            'average_pulse' => round($checkups->avg('pulse_rate'), 0),
        ];
    }

    /**
     * Get abnormal checkups that need attention
     */
    public function getAbnormalCheckups(string $date, ?string $companyId = null): array
    {
        $query = HealthCheck::whereDate('check_date', $date)
            ->where('has_health_issue', true)
            ->with(['crewMember', 'vessel.company', 'reporter']);

        if ($companyId) {
            $query->whereHas('vessel', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return $query->orderBy('checked_at', 'desc')->get()->toArray();
    }
}
