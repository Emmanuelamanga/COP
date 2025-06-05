<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\VisualizationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/visualization', [VisualizationController::class, 'index'])
    ->name('visualization.index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('cases', CaseController::class);
    
    Route::patch('/cases/{case}/verify', [CaseController::class, 'verify'])
        ->name('cases.verify')
        ->middleware('can:verify,case');
    
    Route::patch('/cases/{case}/approve', [CaseController::class, 'approve'])
        ->name('cases.approve')
        ->middleware('can:approve,case');
    
     Route::post('/cases/bulk-action', [CaseController::class, 'bulkAction'])
        ->name('cases.bulk-action')
        ->middleware('role:admin');
    
    // Export cases
    Route::get('/cases-export', [CaseController::class, 'export'])
        ->name('cases.export')
        ->middleware('role:admin');
        
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
