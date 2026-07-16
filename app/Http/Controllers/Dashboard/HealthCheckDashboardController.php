<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Vessel;
use App\Models\CrewMember;
use App\Models\HealthCheck;
use App\Models\VesselAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HealthCheckDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Setup Date Range (Default: Hari Ini saja)
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        // 2. Tentukan Scope Perusahaan
        if ($user->hasAnyRole(['super-admin', 'nurse'])) {
            $companyId = $request->company_id;
            $companies = Company::where('is_active', true)->orderBy('name')->get();
        } else {
            $companyId = $user->company_id;
            $companies = collect();
        }

        // 3. Base Query Builders - DIPERBAIKI: Hanya kapal aktif
        $vesselsQuery = Vessel::query()->where('is_active', true);
        $crewQuery = CrewMember::query()->where('is_active', true);
        $healthChecksQuery = HealthCheck::query()
            ->whereBetween('check_date', [$startDate, $endDate]);

        // Terapkan Filter Perusahaan - DIPERBAIKI: Tambahkan filter is_active pada vessel
        if ($companyId) {
            $vesselsQuery->where('company_id', $companyId);
            $crewQuery->whereHas('vesselAssignments', function($q) use ($companyId) {
                $q->where('is_active', true)
                  ->whereNull('unassigned_at')
                  ->whereHas('vessel', fn($vq) => $vq->where('company_id', $companyId)->where('is_active', true));
            });
            $healthChecksQuery->whereHas('vessel', fn($q) => $q->where('company_id', $companyId)->where('is_active', true));
        } elseif (!$user->hasAnyRole(['super-admin', 'nurse'])) {
            $vesselsQuery->where('company_id', $user->company_id);
            $crewQuery->whereHas('vesselAssignments', function($q) use ($user) {
                $q->where('is_active', true)
                  ->whereNull('unassigned_at')
                  ->whereHas('vessel', fn($vq) => $vq->where('company_id', $user->company_id)->where('is_active', true));
            });
            $healthChecksQuery->whereHas('vessel', fn($q) => $q->where('company_id', $user->company_id)->where('is_active', true));
        }

        // 4. Statistik Utama (Top Cards)
        $totalVessels = $vesselsQuery->count();
        $activeVessels = $vesselsQuery->where('is_active', true)->count();
        $totalCrew = $crewQuery->count();
        $activeCrew = $crewQuery->where('is_active', true)->count();

        // 5. FIXED: Hitung Kepatuhan GLOBAL dengan logika HISTORICAL - Hanya kapal aktif
        $vessels = $vesselsQuery->get();
        $totalCrewDaysInPeriod = 0;

        foreach ($vessels as $vessel) {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');

                // Selalu hitung berapa kru yang SEHARUSNYA mengisi DCU (yang assigned)
                $assignedCrewCount = VesselAssignment::where('vessel_id', $vessel->id)
                    ->where('assigned_at', '<=', $date->endOfDay())
                    ->where(function($q) use ($date) {
                        $q->whereNull('unassigned_at')
                          ->orWhere('unassigned_at', '>', $date->endOfDay());
                    })
                    ->whereHas('crewMember', function($q) {
                        $q->where('is_active', true);
                    })
                    ->distinct('crew_member_id')
                    ->count('crew_member_id');

                $totalCrewDaysInPeriod += $assignedCrewCount;
            }
        }

        $totalChecks = $healthChecksQuery->count();
        $expectedChecks = $totalCrewDaysInPeriod > 0 ? $totalCrewDaysInPeriod : 1;
        $complianceRate = $expectedChecks > 0 ? ($totalChecks / $expectedChecks) * 100 : 0;
        $complianceRate = min(round($complianceRate, 1), 100);

        // Kepatuhan MCU = valid MCU / kru aktif
        $validMcuCount = (clone $crewQuery)->whereNotNull('mcu_valid_until')
                                           ->where('mcu_valid_until', '>=', now())
                                           ->count();
        $mcuComplianceRate = $activeCrew > 0 ? ($validMcuCount / $activeCrew) * 100 : 0;
        $mcuComplianceRate = min(round($mcuComplianceRate, 1), 100);

        // Statistik Isu Kesehatan
        $healthIssues = (clone $healthChecksQuery)->where('has_health_issue', true)->count();
        $requiresFollowUp = (clone $healthChecksQuery)->where('requires_follow_up', true)->count();

        // 6. Data Detail & Grafik
        $complianceTrendData = $this->getComplianceTrend($healthChecksQuery, $startDate, $endDate);

        $checksForDistribution = (clone $healthChecksQuery)
            ->select('blood_pressure', 'pulse_rate', 'temperature', 'respiratory_rate', 'has_health_issue', 'requires_follow_up')
            ->get();

        $bloodPressureDistribution = $this->getBloodPressureDistribution($checksForDistribution);
        $pulseRateDistribution = $this->getPulseRateDistribution($checksForDistribution);
        $temperatureDistribution = $this->getTemperatureDistribution($checksForDistribution);
        $respiratoryRateDistribution = $this->getRespiratoryRateDistribution($checksForDistribution);

        $healthStatusData = $this->getHealthStatusSummary($checksForDistribution, $totalChecks);

        $vesselCompliance = $this->getVesselComplianceData($companyId, $user, $startDate, $endDate);

        return view('dashboard.health-check', array_merge(
            compact(
                'companies',
                'totalVessels',
                'activeVessels',
                'totalCrew',
                'activeCrew',
                'complianceRate',
                'mcuComplianceRate',
                'validMcuCount',
                'totalChecks',
                'healthIssues',
                'requiresFollowUp',
                'complianceTrendData',
                'bloodPressureDistribution',
                'pulseRateDistribution',
                'temperatureDistribution',
                'respiratoryRateDistribution',
                'vesselCompliance'
            ),
            [
                'normalHealthCount' => $healthStatusData['normal'],
                'attentionHealthCount' => $healthStatusData['attention'],
                'criticalHealthCount' => $healthStatusData['critical'],
                'normalHealthPercentage' => $healthStatusData['normalPercentage'],
                'attentionHealthPercentage' => $healthStatusData['attentionPercentage'],
                'criticalHealthPercentage' => $healthStatusData['criticalPercentage']
            ]
        ));
    }

    public function getVesselDetail(Request $request)
    {
        $vesselId = $request->vessel_id;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // DIPERBAIKI: Pastikan vessel aktif
        $vessel = Vessel::where('is_active', true)->findOrFail($vesselId);

        $dailyDetails = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $uniqueCrewOnThisDay = HealthCheck::where('vessel_id', $vesselId)
                ->whereDate('check_date', $dateString)
                ->distinct('crew_member_id')
                ->count('crew_member_id');

            if ($uniqueCrewOnThisDay === 0) {
                $uniqueCrewOnThisDay = VesselAssignment::where('vessel_id', $vesselId)
                    ->where('assigned_at', '<=', $date->endOfDay())
                    ->where(function($q) use ($date) {
                        $q->whereNull('unassigned_at')
                          ->orWhere('unassigned_at', '>', $date->endOfDay());
                    })
                    ->whereHas('crewMember', function($q) {
                        $q->where('is_active', true);
                    })
                    ->distinct('crew_member_id')
                    ->count('crew_member_id');
            }

            $checksCount = HealthCheck::where('vessel_id', $vesselId)
                ->whereDate('check_date', $dateString)
                ->count();

            $activeCrewCount = $uniqueCrewOnThisDay;

            $complianceRate = $activeCrewCount > 0
                ? min(round(($checksCount / $activeCrewCount) * 100, 1), 100.0)
                : ($checksCount > 0 ? 100.0 : 0.0);

            $dailyDetails[] = [
                'date' => $date->format('d M Y'),
                'date_raw' => $dateString,
                'active_crew' => $activeCrewCount,
                'checks_done' => $checksCount,
                'compliance_rate' => $complianceRate,
                'status' => $this->getComplianceStatus($complianceRate)
            ];
        }

        return response()->json([
            'vessel_name' => $vessel->name,
            'daily_details' => $dailyDetails
        ]);
    }

    /**
     * BARU: Debug endpoint untuk diagnose discrepancy
     *
     * Akses: /dashboard/health-check/debug?vessel_id=...&start_date=...&end_date=...
     * Response: JSON dengan detail perhitungan crew vs target per hari
     */
    public function debugVesselComplianceData(Request $request)
    {
        $vesselId = $request->vessel_id;
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // DIPERBAIKI: Pastikan vessel aktif
        $vessel = Vessel::where('is_active', true)->findOrFail($vesselId);

        $debugData = [
            'vessel_id' => $vesselId,
            'vessel_name' => $vessel->name,
            'vessel_is_active' => $vessel->is_active,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate) + 1
            ],
            'daily_breakdown' => [],
            'summary' => [
                'total_crew_days' => 0,
                'total_checks' => 0,
                'expected_compliance' => 0
            ]
        ];

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            // 1. Crew yang melakukan DCU
            $crewWithDcu = HealthCheck::where('vessel_id', $vesselId)
                ->whereDate('check_date', $dateString)
                ->distinct('crew_member_id')
                ->count('crew_member_id');

            // 2. Crew yang assigned
            $crewAssigned = VesselAssignment::where('vessel_id', $vesselId)
                ->where('assigned_at', '<=', $date->endOfDay())
                ->where(function($q) use ($date) {
                    $q->whereNull('unassigned_at')
                      ->orWhere('unassigned_at', '>', $date->endOfDay());
                })
                ->whereHas('crewMember', function($q) {
                    $q->where('is_active', true);
                })
                ->distinct('crew_member_id')
                ->count('crew_member_id');

            // 3. Gunakan crew with DCU, jika 0 gunakan assigned
            $effectiveCrew = $crewWithDcu > 0 ? $crewWithDcu : $crewAssigned;

            // 4. Total checks di hari itu
            $totalChecksOnDay = HealthCheck::where('vessel_id', $vesselId)
                ->whereDate('check_date', $dateString)
                ->count();

            // 5. Detail assignments di hari itu
            $assignments = VesselAssignment::where('vessel_id', $vesselId)
                ->where('assigned_at', '<=', $date->endOfDay())
                ->where(function($q) use ($date) {
                    $q->whereNull('unassigned_at')
                      ->orWhere('unassigned_at', '>', $date->endOfDay());
                })
                ->with('crewMember')
                ->get()
                ->map(function($va) {
                    return [
                        'crew_id' => $va->crew_member_id,
                        'crew_name' => $va->crewMember?->name ?? 'Unknown',
                        'crew_is_active' => $va->crewMember?->is_active ?? false,
                        'assigned_at' => $va->assigned_at->format('Y-m-d H:i'),
                        'unassigned_at' => $va->unassigned_at ? $va->unassigned_at->format('Y-m-d H:i') : 'Still Active'
                    ];
                });

            // 6. Detail DCU di hari itu
            $dcu = HealthCheck::where('vessel_id', $vesselId)
                ->whereDate('check_date', $dateString)
                ->with('crewMember')
                ->get()
                ->map(function($hc) {
                    return [
                        'crew_id' => $hc->crew_member_id,
                        'crew_name' => $hc->crew_name,
                        'checked_at' => $hc->checked_at?->format('Y-m-d H:i:s') ?? 'N/A',
                        'status' => $hc->status
                    ];
                });

            $debugData['daily_breakdown'][] = [
                'date' => $dateString,
                'crew_metrics' => [
                    'crew_with_dcu' => $crewWithDcu,
                    'crew_assigned' => $crewAssigned,
                    'effective_crew_used' => $effectiveCrew,
                    'reason' => $crewWithDcu > 0 ? 'From DCU records' : 'From assignment records (fallback)'
                ],
                'check_metrics' => [
                    'total_checks' => $totalChecksOnDay,
                    'compliance_rate' => $effectiveCrew > 0 ? round(($totalChecksOnDay / $effectiveCrew) * 100, 1) : 0
                ],
                'assignments' => $assignments,
                'dcu_records' => $dcu,
                'issues' => $this->detectIssuesForDay($crewWithDcu, $crewAssigned, $totalChecksOnDay, $effectiveCrew)
            ];

            $debugData['summary']['total_crew_days'] += $effectiveCrew;
            $debugData['summary']['total_checks'] += $totalChecksOnDay;
        }

        // Hitung expected compliance
        if ($debugData['summary']['total_crew_days'] > 0) {
            $debugData['summary']['expected_compliance'] = round(
                ($debugData['summary']['total_checks'] / $debugData['summary']['total_crew_days']) * 100,
                1
            );
        }

        return response()->json($debugData);
    }

    /**
     * HELPER: Deteksi masalah di setiap hari
     */
    private function detectIssuesForDay($crewWithDcu, $crewAssigned, $totalChecks, $effectiveCrew)
    {
        $issues = [];

        // Issue 1: Crew assigned tapi tidak ada DCU
        if ($crewAssigned > 0 && $crewWithDcu === 0) {
            $issues[] = [
                'type' => 'NO_DCU_DESPITE_ASSIGNED',
                'message' => $crewAssigned . ' crew assigned but no DCU records',
                'severity' => 'warning'
            ];
        }

        // Issue 2: DCU lebih banyak dari crew (multiple DCU per crew)
        if ($totalChecks > $effectiveCrew) {
            $issues[] = [
                'type' => 'MULTIPLE_DCU_PER_CREW',
                'message' => $totalChecks . ' checks from ' . $effectiveCrew . ' crew (avg ' . round($totalChecks / $effectiveCrew, 1) . ' per crew)',
                'severity' => 'info'
            ];
        }

        // Issue 3: Crew tidak complete DCU
        if ($totalChecks < $effectiveCrew) {
            $issues[] = [
                'type' => 'INCOMPLETE_DCU',
                'message' => $totalChecks . ' checks from ' . $effectiveCrew . ' crew (' . ($effectiveCrew - $totalChecks) . ' missing)',
                'severity' => 'error'
            ];
        }

        return $issues;
    }

    private function getComplianceTrend($baseQuery, $startDate, $endDate)
    {
        $trendData = (clone $baseQuery)
            ->selectRaw('DATE(check_date) as date, count(*) as total, sum(case when has_health_issue = 1 then 1 else 0 end) as issues')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $healthyValues = [];
        $warningValues = [];

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('d M');

            if (isset($trendData[$dateString])) {
                $total = $trendData[$dateString]->total;
                $issues = $trendData[$dateString]->issues;
                $healthy = $total - $issues;

                $healthyValues[] = $healthy;
                $warningValues[] = (int) $issues;
            } else {
                $healthyValues[] = 0;
                $warningValues[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'healthy' => $healthyValues,
            'warning' => $warningValues
        ];
    }

    private function getBloodPressureDistribution($collection)
    {
        $distribution = [
            'Hipotensi' => 0,
            'Normal' => 0,
            'Pre Hipertensi' => 0,
            'HT 1' => 0,
            'HT 2' => 0
        ];

        foreach ($collection as $check) {
            if (!$check->blood_pressure) continue;

            $bp = explode('/', $check->blood_pressure);
            if (count($bp) === 2) {
                $systolic = (int) $bp[0];
                $diastolic = (int) $bp[1];
                $category = $this->categorizeBP($systolic, $diastolic);
                $distribution[$category]++;
            }
        }

        return $distribution;
    }

    private function categorizeBP($systolic, $diastolic)
    {
        // Hipotensi: Systolic < 90 ATAU Diastolic < 60
        if ($systolic < 90 || $diastolic < 60) {
            return 'Hipotensi';
        }

        // Normal: Systolic 90-120 DAN Diastolic 60-80
        if ($systolic >= 90 && $systolic <= 120 && $diastolic >= 60 && $diastolic <= 80) {
            return 'Normal';
        }

        // Pre Hipertensi: Systolic >120-140 ATAU Diastolic >80
        if (($systolic > 120 && $systolic <= 140) || $diastolic > 80) {
            return 'Pre Hipertensi';
        }

        // HT 1: Systolic >140-159
        if ($systolic > 140 && $systolic <= 159) {
            return 'HT 1';
        }

        // HT 2: Systolic >159
        return 'HT 2';
    }

    private function getPulseRateDistribution($collection)
    {
        $distribution = ['Bradikardi' => 0, 'Normal' => 0, 'Takikardi' => 0];

        foreach ($collection as $check) {
            if (!$check->pulse_rate) continue;
            $pulse = $check->pulse_rate;

            if ($pulse < 60) {
                $distribution['Bradikardi']++;
            } elseif ($pulse >= 60 && $pulse <= 100) {
                $distribution['Normal']++;
            } else {
                $distribution['Takikardi']++;
            }
        }
        return $distribution;
    }

    private function getTemperatureDistribution($collection)
    {
        $distribution = ['Hipotermi' => 0, 'Normal' => 0, 'Hipertermi' => 0];

        foreach ($collection as $check) {
            if (!$check->temperature) continue;
            $temp = $check->temperature;

            if ($temp < 36) {
                $distribution['Hipotermi']++;
            } elseif ($temp >= 36 && $temp <= 37.5) {
                $distribution['Normal']++;
            } else {
                $distribution['Hipertermi']++;
            }
        }
        return $distribution;
    }

    private function getRespiratoryRateDistribution($collection)
    {
        $distribution = ['Bradipnea' => 0, 'Normal' => 0, 'Takipnea' => 0];

        foreach ($collection as $check) {
            if (!$check->respiratory_rate) continue;
            $rate = $check->respiratory_rate;

            if ($rate < 12) {
                $distribution['Bradipnea']++;
            } elseif ($rate >= 12 && $rate <= 20) {
                $distribution['Normal']++;
            } else {
                $distribution['Takipnea']++;
            }
        }
        return $distribution;
    }

    private function getHealthStatusSummary($collection, $total)
    {
        $normal = $collection->where('has_health_issue', false)
            ->where('requires_follow_up', false)->count();

        $attention = $collection->where('has_health_issue', true)
            ->where('requires_follow_up', false)->count();

        $critical = $collection->where('requires_follow_up', true)->count();

        return [
            'normal' => $normal,
            'attention' => $attention,
            'critical' => $critical,
            'normalPercentage' => $total > 0 ? round(($normal / $total) * 100, 1) : 0,
            'attentionPercentage' => $total > 0 ? round(($attention / $total) * 100, 1) : 0,
            'criticalPercentage' => $total > 0 ? round(($critical / $total) * 100, 1) : 0,
        ];
    }

    private function getVesselComplianceData($companyId, $user, $startDate, $endDate)
    {
        // DIPERBAIKI: Hanya ambil kapal aktif
        $vesselsQuery = Vessel::query()->where('is_active', true);

        if ($companyId) {
            $vesselsQuery->where('company_id', $companyId);
        } elseif (!$user->hasAnyRole(['super-admin', 'nurse'])) {
            $vesselsQuery->where('company_id', $user->company_id);
        }

        $vessels = $vesselsQuery->get();

        $daysCount = $startDate->diffInDays($endDate) + 1;

        // DIPERBAIKI: Filter health checks hanya dari kapal aktif
        $checksByVessel = HealthCheck::select('vessel_id', DB::raw('count(*) as total'))
            ->whereBetween('check_date', [$startDate, $endDate])
            ->whereIn('vessel_id', $vessels->pluck('id'))
            ->whereHas('vessel', function($q) {
                $q->where('is_active', true);
            })
            ->groupBy('vessel_id')
            ->pluck('total', 'vessel_id');

        $expectedChecksByVessel = [];
        $totalCrewHistoricalByVessel = [];

        foreach ($vessels as $vessel) {
            $vesselId = $vessel->id;
            $expectedChecksByVessel[$vesselId] = 0;
            $totalCrewHistoricalByVessel[$vesselId] = 0;

            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');

                // Selalu hitung berapa kru yang SEHARUSNYA mengisi DCU (yang assigned)
                $assignedCrewCount = VesselAssignment::where('vessel_id', $vesselId)
                    ->where('assigned_at', '<=', $date->endOfDay())
                    ->where(function($q) use ($date) {
                        $q->whereNull('unassigned_at')
                          ->orWhere('unassigned_at', '>', $date->endOfDay());
                    })
                    ->whereHas('crewMember', function($q) {
                        $q->where('is_active', true);
                    })
                    ->distinct('crew_member_id')
                    ->count('crew_member_id');

                $expectedChecksByVessel[$vesselId] += $assignedCrewCount;
                $totalCrewHistoricalByVessel[$vesselId] += $assignedCrewCount;
            }
        }

        return $vessels->map(function($vessel) use ($checksByVessel, $expectedChecksByVessel, $totalCrewHistoricalByVessel, $daysCount) {
            $vesselId = $vessel->id;
            $checksCount = (int) ($checksByVessel[$vesselId] ?? 0);
            $targetChecks = (int) ($expectedChecksByVessel[$vesselId] ?? 0);
            $totalCrewHistorical = (int) ($totalCrewHistoricalByVessel[$vesselId] ?? 0);

            $averageCrewPerDay = $daysCount > 0 ? round($totalCrewHistorical / $daysCount) : 0;

            if ($targetChecks > 0) {
                $complianceRate = ($checksCount / $targetChecks) * 100;
                $complianceRate = min(round($complianceRate, 1), 100.0);
            } else {
                $complianceRate = $checksCount > 0 ? 100.0 : 0.0;
            }

            return [
                'id' => $vessel->id,
                'name' => $vessel->name,
                'is_active' => $vessel->is_active,
                'total_crew' => $averageCrewPerDay,
                'checks_count' => $checksCount,
                'target_checks' => $targetChecks,
                'compliance_rate' => $complianceRate,
                'status' => $this->getComplianceStatus($complianceRate)
            ];
        })
        ->sortByDesc('compliance_rate')
        ->values();
    }

    private function getComplianceStatus($rate)
    {
        if ($rate >= 80) return 'excellent';
        if ($rate >= 60) return 'good';
        if ($rate >= 40) return 'warning';
        return 'critical';
    }
}
