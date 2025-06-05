<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {

        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $stats = [
            'total_cases' => CaseModel::count(),
            'pending_cases' => CaseModel::pending()->count(),
            'verified_cases' => CaseModel::verified()->count(),
            'approved_cases' => CaseModel::approved()->count(),
        ];

        $recentCases = CaseModel::with('user')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentCases' => $recentCases,
        ]);
    }
}