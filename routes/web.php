<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiLogController;

Route::get('/', function () {
    return view('welcome');
});

// Test route to demonstrate API logging
Route::post('/api/test', function () {
    return response()->json([
        'message' => 'Test endpoint for API logging',
        'timestamp' => now()->toISOString(),
        'data' => request()->all(),
    ]);
});

// API Logger Routes
Route::prefix('api-logs')->name('api-logs.')->group(function () {
    Route::get('/', [ApiLogController::class, 'index'])->name('index');
    Route::get('/{apiLog}', [ApiLogController::class, 'show'])->name('show');
    Route::get('/statistics', [ApiLogController::class, 'statistics'])->name('statistics');
    Route::get('/export', [ApiLogController::class, 'export'])->name('export');
    Route::delete('/{apiLog}', [ApiLogController::class, 'destroy'])->name('destroy');
    Route::delete('/bulk', [ApiLogController::class, 'bulkDestroy'])->name('bulk-destroy');
});
