<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\CaseStatusHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        $query = CaseModel::with(['user', 'verifier', 'approver']);

        // Apply role-based filtering
        if (!Auth::user()->isAdmin()) {
            if (Auth::user()->isVerifier()) {
                $query->whereIn('status', ['pending', 'verified']);
            } elseif (Auth::user()->isApprover()) {
                $query->whereIn('status', ['verified', 'approved']);
            } else {
                $query->where('user_id', Auth::id());
            }
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('county', 'like', "%{$search}%")
                  ->orWhere('case_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('gender', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply case type filter
        if ($request->filled('case_type')) {
            $query->where('case_type', $request->case_type);
        }

        // Apply date range filter
        if ($request->filled('date_range')) {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            }
        }

        $cases = $query->latest('created_at')->paginate(15);

        $layout = Auth::user()->isAdmin() ? 'Admin/Cases/Index' : 'Cases/Index';

        return Inertia::render($layout, [
            'cases' => $cases,
            'canVerify' => Auth::user()->isVerifier() || Auth::user()->isAdmin(),
            'canApprove' => Auth::user()->isApprover() || Auth::user()->isAdmin(),
            'filters' => $request->only(['search', 'status', 'case_type', 'date_range'])
        ]);
    }

    public function create()
    {
        $layout = Auth::user()->isAdmin() ? 'Admin/Cases/Create' : 'Cases/Create';

        return Inertia::render($layout);
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
        $validated['status'] = 'pending';

        $case = CaseModel::create($validated);

        // Log status history
        $this->logStatusHistory($case, null, 'pending', 'Case submitted');

        $route = Auth::user()->isAdmin() ? 'admin.cases.index' : 'cases.index';

        return redirect()->route($route)
            ->with('success', 'Case submitted successfully!');
    }

    public function show(CaseModel $case)
    {
        // Check permissions
        if (!$this->canViewCase($case)) {
            abort(403, 'You do not have permission to view this case.');
        }

        $case->load(['user', 'verifier', 'approver']);
        
        // Load status history
        $statusHistory = CaseStatusHistory::where('case_id', $case->id)
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();
        
        
        $layout = Auth::user()->isAdmin() ? 'Admin/Cases/Index' : 'Cases/Index';

        return Inertia::render($layout, [
            'case' => $case,
            'statusHistory' => $statusHistory,
            'canVerify' => Auth::user()->isVerifier() || Auth::user()->isAdmin(),
            'canApprove' => Auth::user()->isApprover() || Auth::user()->isAdmin(),
        ]);
    }

    public function verify(Request $request, CaseModel $case)
    {
        // Check permissions
        if (!Auth::user()->isVerifier() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not have permission to verify cases.');
        }

        if ($case->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending cases can be verified.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $case->status;

        $case->update([
            'status' => $validated['status'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['notes'],
        ]);

        // Log status history
        $this->logStatusHistory($case, $oldStatus, $validated['status'], $validated['notes']);

        $message = $validated['status'] === 'verified' 
            ? 'Case verified successfully!' 
            : 'Case rejected successfully!';

        return back()->with('success', $message);
    }

    public function approve(Request $request, CaseModel $case)
    {
        // Check permissions
        if (!Auth::user()->isApprover() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not have permission to approve cases.');
        }

        if ($case->status !== 'verified') {
            return back()->withErrors(['status' => 'Only verified cases can be approved.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $case->status;

        $case->update([
            'status' => $validated['status'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $validated['notes'],
        ]);

        // Log status history
        $this->logStatusHistory($case, $oldStatus, $validated['status'], $validated['notes']);

        $message = $validated['status'] === 'approved' 
            ? 'Case approved successfully!' 
            : 'Case rejected successfully!';

        return back()->with('success', $message);
    }

    public function edit(CaseModel $case)
    {
        // Check permissions
        if (!$this->canEditCase($case)) {
            abort(403, 'You do not have permission to edit this case.');
        }

        $layout = Auth::user()->isAdmin() ? 'Admin/Cases/Edit' : 'Cases/Edit';


        return Inertia::render( $layout, [
            'case' => $case
        ]);
    }

    public function update(Request $request, CaseModel $case)
    {
        // Check permissions
        if (!$this->canEditCase($case)) {
            abort(403, 'You do not have permission to edit this case.');
        }

        $validated = $request->validate([
            'county' => 'required|string|max:255',
            'case_type' => 'required|in:GBV,SRH,Economic Empowerment',
            'gender' => 'required|in:Male,Female,Others',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'contact_information' => 'required|string|max:255',
        ]);

        $case->update($validated);

        $route = Auth::user()->isAdmin() ? 'admin.cases.show' : 'cases.show';


        return redirect()->route($route, $case)
            ->with('success', 'Case updated successfully!');
    }

    public function destroy(CaseModel $case)
    {
        // Check permissions - only admin or case owner can delete
        if (!Auth::user()->isAdmin() && $case->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this case.');
        }

        // Delete related status history
        CaseStatusHistory::where('case_id', $case->id)->delete();
        
        $case->delete();

        $route = Auth::user()->isAdmin() ? 'admin.cases.index' : 'cases.index';


        return redirect()->route($route)
            ->with('success', 'Case deleted successfully!');
    }

    public function bulkAction(Request $request)
    {
        // Check permissions
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can perform bulk actions.');
        }

        $validated = $request->validate([
            'action' => 'required|in:verify,approve,reject,delete',
            'case_ids' => 'required|array',
            'case_ids.*' => 'exists:case_models,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $cases = CaseModel::whereIn('id', $validated['case_ids']);
        $count = $cases->count();

        switch ($validated['action']) {
            case 'verify':
                $cases->where('status', 'pending')->update([
                    'status' => 'verified',
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'verification_notes' => $validated['notes']
                ]);
                break;

            case 'approve':
                $cases->where('status', 'verified')->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_notes' => $validated['notes']
                ]);
                break;

            case 'reject':
                $cases->whereIn('status', ['pending', 'verified'])->update([
                    'status' => 'rejected',
                    'verification_notes' => $validated['notes']
                ]);
                break;

            case 'delete':
                // Delete status history first
                CaseStatusHistory::whereIn('case_id', $validated['case_ids'])->delete();
                $cases->delete();
                break;
        }

        return back()->with('success', "Bulk action completed on {$count} case(s).");
    }

    public function export(Request $request)
    {
        // This would integrate with Laravel Excel or similar
        // For now, return JSON export
        $query = CaseModel::with(['user', 'verifier', 'approver']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('county', 'like', "%{$search}%")
                  ->orWhere('case_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('case_type')) {
            $query->where('case_type', $request->case_type);
        }

        $cases = $query->get();

        return response()->json($cases);
    }

    // Helper methods
    private function canViewCase(CaseModel $case): bool
    {
        if (Auth::user()->isAdmin()) {
            return true;
        }

        if (Auth::user()->isVerifier() && in_array($case->status, ['pending', 'verified'])) {
            return true;
        }

        if (Auth::user()->isApprover() && in_array($case->status, ['verified', 'approved'])) {
            return true;
        }

        return $case->user_id === Auth::id();
    }

    private function canEditCase(CaseModel $case): bool
    {
        if (Auth::user()->isAdmin()) {
            return true;
        }

        // Only case owner can edit, and only if not yet approved
        return $case->user_id === Auth::id() && $case->status !== 'approved';
    }

    private function logStatusHistory(CaseModel $case, ?string $fromStatus, string $toStatus, ?string $note = null): void
    {
        CaseStatusHistory::create([
            'case_id' => $case->id,
            'from_status' => $fromStatus ?? 'new',
            'to_status' => $toStatus,
            'note' => $note,
            'changed_by' => Auth::id()
        ]);
    }

    private function getDateRange(string $range): ?array
    {
        $now = now();
        
        return match($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => null
        };
    }
}