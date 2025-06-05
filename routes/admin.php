<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\CaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    
    Route::get('/export', [AdminController::class, 'exportData'])->name('export');
     Route::get('/analytics/export', [AdminController::class, 'exportAnalytics'])->name('analytics.export');


    // User Management
    Route::resource('users', UserManagementController::class);
    Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::post('/users/bulk-action', [UserManagementController::class, 'bulkAction'])
        ->name('users.bulk-action');

    // System Settings
    Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/clear-cache', [SystemSettingsController::class, 'clearCache'])
        ->name('settings.clear-cache');
    Route::post('/settings/optimize', [SystemSettingsController::class, 'optimizeSystem'])
        ->name('settings.optimize');
    Route::post('/settings/backup', [SystemSettingsController::class, 'backupDatabase'])
        ->name('settings.backup');
    Route::post('/settings/maintenance', [SystemSettingsController::class, 'maintenanceMode'])
        ->name('settings.maintenance');

    Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::post('/users/bulk-action', [UserManagementController::class, 'bulkAction'])
        ->name('users.bulk-action');

    Route::resource('cases', CaseController::class);
    Route::patch('/cases/{case}/update-status', [CaseController::class, 'updateStatus'])
        ->name('cases.update-status');
    Route::post('/cases/bulk-action', [CaseController::class, 'bulkAction'])
        ->name('cases.bulk-action');
    Route::get('/cases/export', [CaseController::class, 'export'])
        ->name('cases.export');
});