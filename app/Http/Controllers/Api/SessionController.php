<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'symptoms' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Dummy logic for doctor suggestion (replace later with OpenAI)
        $suggestedDoctor = 'cardiologist';

        // Dummy voice profile ID (replace later with Wapi.ai)
        $voiceProfileId = 'voice_profile_' . uniqid();

        $session = Session::create([
            'user_id' => $user->id,
            'symptoms' => $request->symptoms,
            'notes' => $request->notes,
            'doctor_type' => $suggestedDoctor,
            'voice_profile_id' => $voiceProfileId,
        ]);

        return response()->json([
            'session_id' => $session->id,
            'doctor_type' => $session->doctor_type,
            'voice_profile_id' => $session->voice_profile_id,
        ]);
    }
}
