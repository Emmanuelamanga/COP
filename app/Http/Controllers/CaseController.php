<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    public function index()
    {
        $cases = CaseModel::with(['user', 'verifier', 'approver'])
            ->when(!Auth::user()->isAdmin(), function($query) {
                if (Auth::user()->isVerifier()) {
                    return $query->whereIn('status', ['pending', 'verified']);
                }
                if (Auth::user()->isApprover()) {
                    return $query->whereIn('status', ['verified', 'approved']);
                }
                return $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Cases/Index', [
            'cases' => $cases,
            'canVerify' => Auth::user()->isVerifier(),
            'canApprove' => Auth::user()->isApprover(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Cases/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'county' => 'required|string|max:255',
            'case_type' => 'required|in:GBV,SRH,Economic Empowerment',
            'gender' => 'required|in:Male,Female,Others',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'contact_information' => 'required|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        CaseModel::create($validated);

        return redirect()->route('cases.index')
            ->with('success', 'Case submitted successfully!');
    }

    public function show(CaseModel $case)
    {
        $case->load(['user', 'verifier', 'approver']);
        
        return Inertia::render('Cases/Show', [
            'case' => $case,
            'canVerify' => Auth::user()->isVerifier(),
            'canApprove' => Auth::user()->isApprover(),
        ]);
    }

    public function verify(Request $request, CaseModel $case)
    {
        if (!Auth::user()->isVerifier()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string',
        ]);

        $case->update([
            'status' => $validated['status'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Case verification updated successfully!');
    }

    public function approve(Request $request, CaseModel $case)
    {
        if (!Auth::user()->isApprover() || $case->status !== 'verified') {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $case->update([
            'status' => $validated['status'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Case approval updated successfully!');
    }
}