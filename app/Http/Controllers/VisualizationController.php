<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class VisualizationController extends Controller
{
    public function index(Request $request)
    {
        $query = CaseModel::approved();

        // Apply filters
        if ($request->county) {
            $query->where('county', $request->county);
        }
        
        if ($request->case_type) {
            $query->where('case_type', $request->case_type);
        }
        
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('incident_date', [$request->date_from, $request->date_to]);
        }

        // Get chart data
        $casesByType = $query->clone()
            ->selectRaw('case_type, COUNT(*) as count')
            ->groupBy('case_type')
            ->pluck('count', 'case_type');

        $casesByGender = $query->clone()
            ->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender');

        $casesByCounty = $query->clone()
            ->selectRaw('county, COUNT(*) as count')
            ->groupBy('county')
            ->orderByDesc('count')
            ->take(10)
            ->pluck('count', 'county');

        $casesOverTime = $query->clone()
            ->selectRaw('DATE(incident_date) as date, COUNT(*) as count')
            ->where('incident_date', '>=', Carbon::now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get filter options
        $counties = CaseModel::approved()->distinct()->pluck('county')->sort();

        return Inertia::render('Visualization/Index', [
            'chartData' => [
                'casesByType' => $casesByType,
                'casesByGender' => $casesByGender,
                'casesByCounty' => $casesByCounty,
                'casesOverTime' => $casesOverTime,
            ],
            'filters' => $request->only(['county', 'case_type', 'gender', 'date_from', 'date_to']),
            'counties' => $counties,
        ]);
    }
}