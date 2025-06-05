<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
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

    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $users = $query->withCount('cases')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'verifiers' => User::where('role', 'verifier')->count(),
            'approvers' => User::where('role', 'approver')->count(),
            'regular_users' => User::where('role', 'user')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,verifier,approver,admin',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load(['cases' => function($query) {
            $query->latest()->take(10);
        }]);

        $userStats = [
            'total_cases' => $user->cases()->count(),
            'pending_cases' => $user->cases()->where('status', 'pending')->count(),
            'verified_cases' => $user->cases()->where('status', 'verified')->count(),
            'approved_cases' => $user->cases()->where('status', 'approved')->count(),
            'rejected_cases' => $user->cases()->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'userStats' => $userStats,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => 'required|in:user,verifier,approver,admin',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $validated['is_active'] ?? false;

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        if ($user->cases()->exists()) {
            return back()->with('error', 'Cannot delete user with existing cases!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully!");
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = collect($validated['user_ids'])->reject(auth()->id());

        switch ($validated['action']) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['is_active' => true]);
                return back()->with('success', 'Selected users activated successfully!');

            case 'deactivate':
                User::whereIn('id', $userIds)->update(['is_active' => false]);
                return back()->with('success', 'Selected users deactivated successfully!');

            case 'delete':
                $usersWithCases = User::whereIn('id', $userIds)->has('cases')->count();
                if ($usersWithCases > 0) {
                    return back()->with('error', 'Cannot delete users with existing cases!');
                }
                User::whereIn('id', $userIds)->delete();
                return back()->with('success', 'Selected users deleted successfully!');
        }
    }
}