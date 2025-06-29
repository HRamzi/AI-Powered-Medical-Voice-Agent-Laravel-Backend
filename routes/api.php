<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Protected routes that require Clerk authentication
Route::middleware('auth.clerk')->group(function () {
    
    // User management (matches frontend: POST /api/users)
    Route::post('/users', [\App\Http\Controllers\Api\UserController::class, 'store']);
    
    // Session management (matches frontend: GET/POST /api/session-chat)
    Route::post('/session-chat', [\App\Http\Controllers\Api\SessionController::class, 'store']);
    Route::get('/session-chat', [\App\Http\Controllers\Api\SessionController::class, 'show']);
    
    // Doctor suggestions (matches frontend: POST /api/suggest-doctors)
    Route::post('/suggest-doctors', [\App\Http\Controllers\Api\DoctorController::class, 'suggest']);
    
    // Medical report generation (matches frontend: POST /api/medical-report)
    Route::post('/medical-report', [\App\Http\Controllers\Api\ReportController::class, 'generate']);
    
    // Get authenticated user profile
    Route::get('/profile', function (Request $request) {
        return response()->json([
            'user' => auth()->user(),
            'message' => 'Successfully authenticated with Clerk'
        ]);
    });
    
    // Test endpoint for AI Medical Voice Agent
    Route::get('/test', function (Request $request) {
        return response()->json([
            'user' => auth()->user(),
            'credits' => auth()->user()->credits,
            'message' => 'AI Medical Voice Agent - Authentication Working!'
        ]);
    });
    
});

// Public routes (no authentication required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'AI Medical Voice Agent API',
        'timestamp' => now()
    ]);
}); 