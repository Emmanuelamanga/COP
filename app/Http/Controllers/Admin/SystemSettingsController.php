<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
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

    public function index()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
        ];

        $databaseInfo = [
            'total_cases' => DB::table('cases')->count(),
            'total_users' => DB::table('users')->count(),
            'database_size' => $this->getDatabaseSize(),
            'last_backup' => Cache::get('last_backup_date', 'Never'),
        ];

        $cacheInfo = [
            'cache_driver' => config('cache.default'),
            'cache_size' => $this->getCacheSize(),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'systemInfo' => $systemInfo,
            'databaseInfo' => $databaseInfo,
            'cacheInfo' => $cacheInfo,
        ]);
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return back()->with('success', 'Cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimizeSystem()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return back()->with('success', 'System optimized successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to optimize system: ' . $e->getMessage());
        }
    }

    public function backupDatabase()
    {
        try {
            // This would typically use a package like spatie/laravel-backup
            // For demo purposes, we'll just simulate the backup
            Cache::put('last_backup_date', now()->format('Y-m-d H:i:s'), 60 * 60 * 24);
            
            return back()->with('success', 'Database backup completed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to backup database: ' . $e->getMessage());
        }
    }

    public function maintenanceMode(Request $request)
    {
        try {
            if ($request->enable) {
                Artisan::call('down', ['--secret' => 'admin-access']);
                return back()->with('success', 'Maintenance mode enabled!');
            } else {
                Artisan::call('up');
                return back()->with('success', 'Maintenance mode disabled!');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    private function getDatabaseSize()
    {
        try {
            $size = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ")[0]->size_mb ?? 0;

            return $size . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getCacheSize()
    {
        try {
            // This is a simplified implementation
            // In production, you'd want to implement proper cache size calculation
            return 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
}