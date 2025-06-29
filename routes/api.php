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



// Vapi webhook endpoint (public - Vapi will call this)
Route::post('/webhook/vapi', function (Request $request) {
    $message = $request->input('message');
    
    switch ($message['type']) {
        case 'status-update':
            \Log::info("Call {$message['call']['id']}: {$message['call']['status']}");
            break;
            
        case 'transcript':
            \Log::info("{$message['role']}: {$message['transcript']}");
            // You could store real-time transcripts here
            break;
            
        case 'function-call':
            // Handle custom function calls from Vapi
            return handleVapiFunctionCall($message);
    }
    
    return response()->json(['received' => true]);
}); 

function handleVapiFunctionCall($message) {
    $functionCall = $message['functionCall'];
    
    switch ($functionCall['name']) {
        case 'lookup_patient_history':
            // Example: Look up patient medical history
            $patientData = [
                'patientId' => $functionCall['parameters']['patientId'], 
                'lastVisit' => '2024-01-15',
                'conditions' => ['hypertension', 'diabetes']
            ];
            return response()->json(['result' => $patientData]);
            
        default:
            return response()->json(['error' => 'Unknown function'], 400);
    }
} 