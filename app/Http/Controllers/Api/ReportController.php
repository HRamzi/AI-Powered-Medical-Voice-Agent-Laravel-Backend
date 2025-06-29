<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    /**
     * Generate medical report using OpenAI
     * Matches frontend: POST /api/medical-report
     */
    public function generate(Request $request)
    {
        $request->validate([
            'sessionId' => 'required|string',
            'sessionDetail' => 'required|array',
            'messages' => 'required|array'
        ]);

        try {
            $sessionId = $request->input('sessionId');
            $sessionDetail = $request->input('sessionDetail');
            $messages = $request->input('messages');

            $reportPrompt = "You are an AI Medical Voice Agent that just finished a voice conversation with a user. Based on doctor AI agent info and Conversation between AI medical agent and user, generate a structured report with the following fields:
2. agent: the medical specialist name (e.g., \"General Physician AI\")
3. user: name of the patient or \"Anonymous\" if not provided
4. timestamp: current date and time in ISO format
5. chiefComplaint: one-sentence summary of the main health concern
6. summary: a 2-3 sentence summary of the conversation, symptoms, and recommendations
7. symptoms: list of symptoms mentioned by the user
8. duration: how long the user has experienced the symptoms
9. severity: mild, moderate, or severe
10. medicationsMentioned: list of any medicines mentioned
11. recommendations: list of AI suggestions (e.g., rest, see a doctor)
Return the result in this JSON format:
{
 \"agent\": \"string\",
 \"user\": \"string\",
 \"timestamp\": \"ISO Date string\",
 \"chiefComplaint\": \"string\",
 \"summary\": \"string\",
 \"symptoms\": [\"symptom1\", \"symptom2\"],
 \"duration\": \"string\",
 \"severity\": \"string\",
 \"medicationsMentioned\": [\"med1\", \"med2\"],
 \"recommendations\": [\"rec1\", \"rec2\"],
}
Only include valid fields. Respond with nothing else.";

            $userInput = "AI Doctor Agent Info:" . json_encode($sessionDetail) . ", Conversation:" . json_encode($messages);

            // Make OpenAI API call to generate report
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => env('OPENAI_MODEL', 'gpt-4'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $reportPrompt
                    ],
                    [
                        'role' => 'user', 
                        'content' => $userInput
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawResp = $data['choices'][0]['message']['content'];
                
                // Clean up the response (remove markdown formatting)
                $cleanResp = trim($rawResp);
                $cleanResp = str_replace(['```json', '```'], '', $cleanResp);
                
                $reportData = json_decode($cleanResp, true);

                // Save to Database - Update session with report and conversation
                $session = Session::where('session_id', $sessionId)->first();
                if ($session) {
                    $session->update([
                        'report' => $reportData,
                        'conversation' => $messages
                    ]);
                }

                return response()->json($reportData);
            }

            return response()->json(['error' => 'Failed to generate report'], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 