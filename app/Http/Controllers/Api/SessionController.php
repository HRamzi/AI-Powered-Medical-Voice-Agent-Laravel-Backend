<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Create a new consultation session
     * Matches frontend: POST /api/session-chat
     */
    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'required|string',
            'selectedDoctor' => 'required|array',
        ]);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $session = Session::create([
                'user_id' => $user->id,
                'notes' => $request->input('notes'),
                'selected_doctor' => $request->input('selectedDoctor'),
                'created_by' => $user->email,
                'created_on' => now()->toString(),
            ]);

            // Return the session data matching frontend expectations
            return response()->json([
                'id' => $session->id,
                'sessionId' => $session->session_id,
                'notes' => $session->notes,
                'selectedDoctor' => $session->selected_doctor,
                'conversation' => $session->conversation,
                'report' => $session->report,
                'createdBy' => $session->created_by,
                'createdOn' => $session->created_on,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get consultation session(s)
     * Matches frontend: GET /api/session-chat?sessionId={id|all}
     */
    public function show(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $sessionId = $request->query('sessionId');

            if ($sessionId === 'all') {
                // Return all sessions for the authenticated user
                $sessions = Session::where('created_by', $user->email)
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($session) {
                        return [
                            'id' => $session->id,
                            'sessionId' => $session->session_id,
                            'notes' => $session->notes,
                            'selectedDoctor' => $session->selected_doctor,
                            'conversation' => $session->conversation,
                            'report' => $session->report,
                            'createdBy' => $session->created_by,
                            'createdOn' => $session->created_on,
                        ];
                    });

                return response()->json($sessions);
            } else {
                // Return specific session by session_id
                $session = Session::where('session_id', $sessionId)->first();

                if (!$session) {
                    return response()->json(['error' => 'Session not found'], 404);
                }

                return response()->json([
                    'id' => $session->id,
                    'sessionId' => $session->session_id,
                    'notes' => $session->notes,
                    'selectedDoctor' => $session->selected_doctor,
                    'conversation' => $session->conversation,
                    'report' => $session->report,
                    'createdBy' => $session->created_by,
                    'createdOn' => $session->created_on,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
