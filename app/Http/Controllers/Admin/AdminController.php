<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        // System Statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_cases' => CaseModel::count(),
            'pending_cases' => CaseModel::where('status', 'pending')->count(),
            'verified_cases' => CaseModel::where('status', 'verified')->count(),
            'approved_cases' => CaseModel::where('status', 'approved')->count(),
            'rejected_cases' => CaseModel::where('status', 'rejected')->count(),
            'cases_this_month' => CaseModel::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'cases_last_month' => CaseModel::whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->whereYear('created_at', Carbon::now()->subMonth()->year)
                ->count(),
        ];

        // User Role Distribution
        $usersByRole = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        // Cases by Status Over Time (Last 6 months) - Database agnostic approach
        $casesOverTime = $this->getCasesOverTime();

        // Top Counties by Case Count
        $topCounties = CaseModel::select('county', DB::raw('count(*) as count'))
            ->groupBy('county')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        // Recent Activity
        $recentCases = CaseModel::with('user')
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::latest()
            ->take(10)
            ->get();

        // Performance Metrics - Database agnostic
        $avgProcessingTime = $this->getAverageProcessingTime();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'usersByRole' => $usersByRole,
            'casesOverTime' => $casesOverTime,
            'topCounties' => $topCounties,
            'recentCases' => $recentCases,
            'recentUsers' => $recentUsers,
            'avgProcessingTime' => round($avgProcessingTime, 1),
        ]);
    }

    public function analytics(Request $request)
    {
        $range = $request->get('range', 30);
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($range);

        // Calculate main analytics metrics
        $totalCases = CaseModel::count();
        $totalCasesInRange = CaseModel::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalCasesPreviousPeriod = CaseModel::whereBetween('created_at', [
            $startDate->copy()->subDays($range),
            $startDate
        ])->count();

        $casesGrowth = $totalCasesPreviousPeriod > 0
            ? round((($totalCasesInRange - $totalCasesPreviousPeriod) / $totalCasesPreviousPeriod) * 100, 1)
            : 100;

        $pendingCases = CaseModel::where('status', 'pending')->count();
        $approvedCases = CaseModel::where('status', 'approved')->count();
        $verifiedCases = CaseModel::where('status', 'verified')->count();

        // Calculate approval rate
        $totalProcessed = CaseModel::whereIn('status', ['approved', 'rejected'])->count();
        $approvalRate = $totalProcessed > 0
            ? round(($approvedCases / $totalProcessed) * 100, 1)
            : 0;

        // Get processing times
        $processingStats = $this->getProcessingStats();

        // Get user activity counts
        $activeSubmitters = CaseModel::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $activeVerifiers = CaseModel::whereBetween('verified_at', [$startDate, $endDate])
            ->whereNotNull('verified_by')
            ->distinct('verified_by')
            ->count('verified_by');

        $activeApprovers = CaseModel::whereBetween('approved_at', [$startDate, $endDate])
            ->whereNotNull('approved_by')
            ->distinct('approved_by')
            ->count('approved_by');

        $analytics = [
            'totalCases' => $totalCases,
            'pendingCases' => $pendingCases,
            'approvedCases' => $approvedCases,
            'approvalRate' => $approvalRate,
            'casesGrowth' => $casesGrowth,
            'avgVerificationTime' => $processingStats['avg_verification_time'],
            'avgApprovalTime' => $processingStats['avg_approval_time'],
            'avgTotalTime' => round($processingStats['avg_verification_time'] + $processingStats['avg_approval_time'], 1),
            'activeSubmitters' => $activeSubmitters,
            'activeVerifiers' => $activeVerifiers,
            'activeApprovers' => $activeApprovers,
        ];

        // Prepare chart data

        // Status distribution for doughnut chart
        $statusCounts = CaseModel::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusData = [
            $statusCounts['pending'] ?? 0,
            $statusCounts['verified'] ?? 0,
            $statusCounts['approved'] ?? 0,
            $statusCounts['rejected'] ?? 0
        ];

        // Case type distribution for bar chart
        $typeCounts = CaseModel::select('case_type', DB::raw('count(*) as count'))
            ->groupBy('case_type')
            ->pluck('count', 'case_type');

        $typeData = [
            $typeCounts['GBV'] ?? 0,
            $typeCounts['SRH'] ?? 0,
            $typeCounts['Economic Empowerment'] ?? 0
        ];

        // Cases over time for line chart
        $driver = DB::getDriverName();
        $dateFormat = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM-DD')",
            'mysql' => "DATE(created_at)",
            'sqlsrv' => "CONVERT(date, created_at)",
            default => "DATE(created_at)",
        };

        $casesOverTime = CaseModel::select(
            DB::raw("$dateFormat as date"),
            DB::raw('count(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw($dateFormat))
            ->orderBy(DB::raw($dateFormat))
            ->get();

        // Fill in missing dates with zero counts
        $timeLabels = [];
        $timeData = [];
        $dateMap = $casesOverTime->pluck('count', 'date')->toArray();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $timeLabels[] = $date->format('M d');
            $timeData[] = $dateMap[$dateStr] ?? 0;
        }

        // Top counties for horizontal bar chart
        $topCounties = CaseModel::select('county', DB::raw('count(*) as count'))
            ->groupBy('county')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $countyLabels = $topCounties->pluck('county')->toArray();
        $countyData = $topCounties->pluck('count')->toArray();

        // Gender distribution for pie chart
        $genderCounts = CaseModel::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender');

        $genderData = [
            $genderCounts['Male'] ?? 0,
            $genderCounts['Female'] ?? 0,
            $genderCounts['Others'] ?? 0
        ];

        $chartData = [
            'statusData' => $statusData,
            'typeData' => $typeData,
            'timeLabels' => $timeLabels,
            'timeData' => $timeData,
            'countyLabels' => $countyLabels,
            'countyData' => $countyData,
            'genderData' => $genderData,
        ];

        // Get recent activities
        $recentActivities = collect();

        // Recent case submissions
        $recentSubmissions = CaseModel::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id . '_created',
                    'type' => 'case_created',
                    'description' => "New {$case->case_type} case submitted by {$case->user->name}",
                    'created_at' => $case->created_at,
                ];
            });

        // Recent verifications
        $recentVerifications = CaseModel::with('verifier')
            ->whereNotNull('verified_at')
            ->orderBy('verified_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id . '_verified',
                    'type' => 'case_verified',
                    'description' => "Case #{$case->id} verified by {$case->verifier->name}",
                    'created_at' => $case->verified_at,
                ];
            });

        // Recent approvals
        $recentApprovals = CaseModel::with('approver')
            ->whereNotNull('approved_at')
            ->orderBy('approved_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($case) {
                $status = $case->status === 'approved' ? 'approved' : 'rejected';
                return [
                    'id' => $case->id . '_' . $status,
                    'type' => 'case_' . $status,
                    'description' => "Case #{$case->id} {$status} by {$case->approver->name}",
                    'created_at' => $case->approved_at,
                ];
            });

        // Merge and sort all activities
        $recentActivities = $recentSubmissions
            ->concat($recentVerifications)
            ->concat($recentApprovals)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return Inertia::render('Admin/Analytics', [
            'analytics' => $analytics,
            'chartData' => $chartData,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Export analytics data
     */
    public function exportAnalytics(Request $request)
    {
        $range = $request->get('range', 30);
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($range);

        $cases = CaseModel::with(['user', 'verifier', 'approver'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $filename = 'analytics_export_' . Carbon::now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($cases, $range) {
            $handle = fopen('php://output', 'w');

            // Add summary header
            fputcsv($handle, ['Analytics Report - Last ' . $range . ' days']);
            fputcsv($handle, ['Generated on: ' . Carbon::now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []); // Empty row

            // Add summary statistics
            fputcsv($handle, ['Summary Statistics']);
            fputcsv($handle, ['Total Cases', $cases->count()]);
            fputcsv($handle, ['Pending', $cases->where('status', 'pending')->count()]);
            fputcsv($handle, ['Verified', $cases->where('status', 'verified')->count()]);
            fputcsv($handle, ['Approved', $cases->where('status', 'approved')->count()]);
            fputcsv($handle, ['Rejected', $cases->where('status', 'rejected')->count()]);
            fputcsv($handle, []); // Empty row

            // Detailed case data headers
            fputcsv($handle, [
                'ID',
                'County',
                'Case Type',
                'Gender',
                'Status',
                'Reporter',
                'Incident Date',
                'Submitted Date',
                'Verified By',
                'Verified Date',
                'Approved By',
                'Approved Date',
                'Processing Days'
            ]);

            // Case data
            foreach ($cases as $case) {
                $processingDays = $case->approved_at
                    ? Carbon::parse($case->created_at)->diffInDays(Carbon::parse($case->approved_at))
                    : '-';

                fputcsv($handle, [
                    $case->id,
                    $case->county,
                    $case->case_type,
                    $case->gender,
                    $case->status,
                    $case->user->name,
                    $case->incident_date,
                    $case->created_at->format('Y-m-d H:i:s'),
                    $case->verifier?->name ?? '-',
                    $case->verified_at?->format('Y-m-d H:i:s') ?? '-',
                    $case->approver?->name ?? '-',
                    $case->approved_at?->format('Y-m-d H:i:s') ?? '-',
                    $processingDays
                ]);
            }

            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }
    public function exportData(Request $request)
    {
        $query = CaseModel::with(['user', 'verifier', 'approver']);

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->case_type) {
            $query->where('case_type', $request->case_type);
        }
        if ($request->county) {
            $query->where('county', $request->county);
        }
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('incident_date', [$request->date_from, $request->date_to]);
        }

        $cases = $query->get();

        $filename = 'cases_export_' . Carbon::now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($cases) {
            $handle = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($handle, [
                'ID',
                'Reporter Name',
                'Reporter Email',
                'County',
                'Case Type',
                'Gender',
                'Incident Date',
                'Status',
                'Description',
                'Contact Info',
                'Verified By',
                'Verified At',
                'Approved By',
                'Approved At',
                'Created At'
            ]);

            // CSV Data
            foreach ($cases as $case) {
                fputcsv($handle, [
                    $case->id,
                    $case->user->name,
                    $case->user->email,
                    $case->county,
                    $case->case_type,
                    $case->gender,
                    $case->incident_date,
                    $case->status,
                    $case->description,
                    $case->contact_information,
                    $case->verifier?->name,
                    $case->verified_at?->format('Y-m-d H:i:s'),
                    $case->approver?->name,
                    $case->approved_at?->format('Y-m-d H:i:s'),
                    $case->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get cases over time data - Database agnostic approach
     */
    private function getCasesOverTime()
    {
        $driver = DB::getDriverName();

        // Get the appropriate date format expression
        $monthFormat = $this->getMonthFormatExpression($driver);

        try {
            return CaseModel::select(
                DB::raw("$monthFormat as month"),
                'status',
                DB::raw('count(*) as count')
            )
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->groupBy(DB::raw($monthFormat), 'status')
                ->orderBy(DB::raw($monthFormat))
                ->get()
                ->groupBy('month');
        } catch (\Exception $e) {
            // Fallback: Use PHP to group data by month
            return $this->getCasesOverTimeFallback();
        }
    }

    /**
     * Get monthly trends data - Database agnostic approach
     */
    private function getMonthlyTrends()
    {
        $driver = DB::getDriverName();

        // Get the appropriate date format expression for incident_date
        $monthFormat = $this->getMonthFormatExpression($driver, 'incident_date');

        try {
            return CaseModel::select(
                DB::raw("$monthFormat as month"),
                'case_type',
                DB::raw('count(*) as count')
            )
                ->where('incident_date', '>=', Carbon::now()->subYear())
                ->groupBy(DB::raw($monthFormat), 'case_type')
                ->orderBy(DB::raw($monthFormat))
                ->get()
                ->groupBy('month');
        } catch (\Exception $e) {
            // Fallback: Use PHP to group data by month
            return $this->getMonthlyTrendsFallback();
        }
    }

    /**
     * Get processing statistics - Database agnostic approach
     */
    private function getProcessingStats()
    {
        $driver = DB::getDriverName();

        try {
            // Get average verification time
            $avgVerificationTime = $this->getAverageDaysDifference('verified_at', 'created_at', $driver);

            // Get average approval time
            $avgApprovalTime = $this->getAverageDaysDifference('approved_at', 'verified_at', $driver);

            // Calculate rates
            $totalCases = CaseModel::count();
            $nonPendingCases = CaseModel::where('status', '!=', 'pending')->count();
            $approvedCases = CaseModel::where('status', 'approved')->count();

            return [
                'avg_verification_time' => round($avgVerificationTime, 1),
                'avg_approval_time' => round($avgApprovalTime, 1),
                'verification_rate' => $totalCases > 0 ? ($nonPendingCases / $totalCases * 100) : 0,
                'approval_rate' => $nonPendingCases > 0 ? ($approvedCases / $nonPendingCases * 100) : 0,
            ];
        } catch (\Exception $e) {
            // Fallback: Calculate using PHP
            return $this->getProcessingStatsFallback();
        }
    }

    /**
     * Get average processing time - Database agnostic approach
     */
    private function getAverageProcessingTime()
    {
        $driver = DB::getDriverName();

        try {
            return $this->getAverageDaysDifference('approved_at', 'created_at', $driver);
        } catch (\Exception $e) {
            // Fallback: Calculate using PHP
            return $this->getAverageProcessingTimeFallback();
        }
    }

    /**
     * Get the appropriate month format expression for different databases
     */
    private function getMonthFormatExpression($driver, $column = 'created_at')
    {
        return match ($driver) {
            'sqlite' => "strftime('%Y-%m', $column)",
            'pgsql' => "to_char($column, 'YYYY-MM')",
            'mysql' => "DATE_FORMAT($column, '%Y-%m')",
            'sqlsrv' => "FORMAT($column, 'yyyy-MM')",
            default => "DATE_FORMAT($column, '%Y-%m')", // MySQL fallback
        };
    }

    /**
     * Get average days difference between two date columns
     */
    private function getAverageDaysDifference($endColumn, $startColumn, $driver)
    {
        $diffExpression = match ($driver) {
            'sqlite' => "julianday($endColumn) - julianday($startColumn)",
            'pgsql' => "EXTRACT(epoch FROM ($endColumn - $startColumn)) / 86400",
            'mysql' => "DATEDIFF($endColumn, $startColumn)",
            'sqlsrv' => "DATEDIFF(day, $startColumn, $endColumn)",
            default => "DATEDIFF($endColumn, $startColumn)", // MySQL fallback
        };

        $result = CaseModel::whereNotNull($endColumn)
            ->whereNotNull($startColumn)
            ->selectRaw("AVG($diffExpression) as avg_days")
            ->first();

        return $result->avg_days ?? 0;
    }

    /**
     * Fallback method for cases over time using PHP
     */
    private function getCasesOverTimeFallback()
    {
        $cases = CaseModel::select('status', 'created_at')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get();

        $grouped = $cases->groupBy(function ($case) {
            return $case->created_at->format('Y-m');
        });

        $result = collect();
        foreach ($grouped as $month => $monthCases) {
            $statusGroups = $monthCases->groupBy('status');
            foreach ($statusGroups as $status => $statusCases) {
                $result->push((object)[
                    'month' => $month,
                    'status' => $status,
                    'count' => $statusCases->count()
                ]);
            }
        }

        return $result->groupBy('month');
    }

    /**
     * Fallback method for monthly trends using PHP
     */
    private function getMonthlyTrendsFallback()
    {
        $cases = CaseModel::select('case_type', 'incident_date')
            ->where('incident_date', '>=', Carbon::now()->subYear())
            ->get();

        $grouped = $cases->groupBy(function ($case) {
            return Carbon::parse($case->incident_date)->format('Y-m');
        });

        $result = collect();
        foreach ($grouped as $month => $monthCases) {
            $typeGroups = $monthCases->groupBy('case_type');
            foreach ($typeGroups as $type => $typeCases) {
                $result->push((object)[
                    'month' => $month,
                    'case_type' => $type,
                    'count' => $typeCases->count()
                ]);
            }
        }

        return $result->groupBy('month');
    }

    /**
     * Fallback method for processing stats using PHP
     */
    private function getProcessingStatsFallback()
    {
        // Get cases with verification dates
        $verifiedCases = CaseModel::whereNotNull('verified_at')
            ->whereNotNull('created_at')
            ->get(['verified_at', 'created_at']);

        $avgVerificationTime = 0;
        if ($verifiedCases->count() > 0) {
            $totalVerificationDays = $verifiedCases->sum(function ($case) {
                return Carbon::parse($case->verified_at)->diffInDays(Carbon::parse($case->created_at));
            });
            $avgVerificationTime = $totalVerificationDays / $verifiedCases->count();
        }

        // Get cases with approval dates
        $approvedCases = CaseModel::whereNotNull('approved_at')
            ->whereNotNull('verified_at')
            ->get(['approved_at', 'verified_at']);

        $avgApprovalTime = 0;
        if ($approvedCases->count() > 0) {
            $totalApprovalDays = $approvedCases->sum(function ($case) {
                return Carbon::parse($case->approved_at)->diffInDays(Carbon::parse($case->verified_at));
            });
            $avgApprovalTime = $totalApprovalDays / $approvedCases->count();
        }

        // Calculate rates
        $totalCases = CaseModel::count();
        $nonPendingCases = CaseModel::where('status', '!=', 'pending')->count();
        $approvedCasesCount = CaseModel::where('status', 'approved')->count();

        return [
            'avg_verification_time' => round($avgVerificationTime, 1),
            'avg_approval_time' => round($avgApprovalTime, 1),
            'verification_rate' => $totalCases > 0 ? ($nonPendingCases / $totalCases * 100) : 0,
            'approval_rate' => $nonPendingCases > 0 ? ($approvedCasesCount / $nonPendingCases * 100) : 0,
        ];
    }

    /**
     * Fallback method for average processing time using PHP
     */
    private function getAverageProcessingTimeFallback()
    {
        $cases = CaseModel::whereNotNull('approved_at')
            ->whereNotNull('created_at')
            ->get(['approved_at', 'created_at']);

        if ($cases->count() === 0) {
            return 0;
        }

        $totalDays = $cases->sum(function ($case) {
            return Carbon::parse($case->approved_at)->diffInDays(Carbon::parse($case->created_at));
        });

        return $totalDays / $cases->count();
    }
}
