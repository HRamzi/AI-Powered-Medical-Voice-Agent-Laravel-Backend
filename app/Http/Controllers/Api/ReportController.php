<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Generate medical report using OpenAI
     * Matches frontend: POST /api/medical-report
     */
    public function generate(Request $request)
    {
        Log::info('Medical Report Generation Started');
        
        try {
            $request->validate([
                'sessionId' => 'required|string',
                'sessionDetail' => 'required|array',
                'messages' => 'required|array'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        }

        try {
            $sessionId = $request->input('sessionId');
            $sessionDetail = $request->input('sessionDetail');
            $messages = $request->input('messages');

            // Debug logging
            Log::info('Medical Report Request - SessionId: ' . $sessionId);
            Log::info('Medical Report Request - Messages count: ' . count($messages));

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

            // Generate mock report (replace with OpenAI when API key is configured)
            $reportData = $this->generateMockReport($sessionDetail, $messages);

            // Save to Database - Update session with report and conversation
            try {
                $session = Session::where('session_id', $sessionId)->first();
                if ($session) {
                    $session->update([
                        'report' => $reportData,
                        'conversation' => $messages
                    ]);
                    Log::info('Successfully updated session with report');
                } else {
                    Log::warning('Session not found for ID: ' . $sessionId);
                }
            } catch (\Exception $dbError) {
                Log::error('Database update failed: ' . $dbError->getMessage());
                // Continue anyway - don't fail the whole request for DB issues
            }

            return response()->json($reportData);

            /* 
            // Uncomment when OpenAI API key is configured
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
            */

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate mock medical report based on conversation
     * This provides intelligent mock data until OpenAI API is configured
     */
    private function generateMockReport($sessionDetail, $messages)
    {
        try {
            // Debug: Log the incoming data
            Log::info('ReportController - sessionDetail:', $sessionDetail);
            Log::info('ReportController - messages:', $messages);

            $userMessages = array_filter($messages, function($msg) {
                return isset($msg['role']) && $msg['role'] === 'user';
            });
            
            $assistantMessages = array_filter($messages, function($msg) {
                return isset($msg['role']) && $msg['role'] === 'assistant';
            });

            // Extract symptoms from user messages - with better error handling
            $symptoms = [];
            $userTexts = [];
            
            foreach ($userMessages as $msg) {
                if (isset($msg['text'])) {
                    $userTexts[] = $msg['text'];
                }
            }
            
            $allUserText = strtolower(implode(' ', $userTexts));
        
        if (strpos($allUserText, 'headache') !== false) $symptoms[] = 'headache';
        if (strpos($allUserText, 'pain') !== false) $symptoms[] = 'pain';
        if (strpos($allUserText, 'fever') !== false) $symptoms[] = 'fever';
        if (strpos($allUserText, 'cough') !== false) $symptoms[] = 'cough';
        if (strpos($allUserText, 'tired') !== false || strpos($allUserText, 'fatigue') !== false) $symptoms[] = 'fatigue';
        if (strpos($allUserText, 'nausea') !== false) $symptoms[] = 'nausea';
        if (strpos($allUserText, 'dizzy') !== false) $symptoms[] = 'dizziness';
        
        if (empty($symptoms)) $symptoms = ['general discomfort'];

        // Determine severity based on keywords
        $severity = 'mild';
        if (strpos($allUserText, 'severe') !== false || strpos($allUserText, 'terrible') !== false) {
            $severity = 'severe';
        } elseif (strpos($allUserText, 'moderate') !== false || strpos($allUserText, 'bad') !== false) {
            $severity = 'moderate';
        }

        // Extract duration
        $duration = "Duration not specified by user";
        if (strpos($allUserText, 'morning') !== false) $duration = "Since morning";
        if (strpos($allUserText, 'yesterday') !== false) $duration = "Since yesterday";
        if (strpos($allUserText, 'week') !== false) $duration = "About a week";
        if (strpos($allUserText, 'days') !== false) $duration = "Few days";

            // Generate chief complaint
            $chiefComplaint = count($userMessages) > 0 && isset($userMessages[0]['text']) ? 
                "Patient discussed: " . substr($userMessages[0]['text'], 0, 100) :
                "User did not clarify the main complaint";

            // Generate summary
            $doctorType = $sessionDetail['selectedDoctor']['specialist'] ?? 'AI Doctor';
            $summary = count($messages) > 2 ? 
                "Patient consulted with {$doctorType} regarding health concerns. Conversation included " . count($messages) . " exchanges. AI provided appropriate medical guidance and recommendations." :
                "Brief consultation with limited information provided by user";

            return [
                'agent' => ($sessionDetail['selectedDoctor']['specialist'] ?? 'General Physician') . ' AI',
                'user' => 'Anonymous',
                'timestamp' => now()->toISOString(),
                'chiefComplaint' => $chiefComplaint,
                'summary' => $summary,
                'symptoms' => $symptoms,
                'duration' => $duration,
                'severity' => $severity,
                'medicationsMentioned' => [], // Could extract from conversation if needed
                'recommendations' => [
                    'Monitor symptoms closely',
                    'Stay hydrated and get adequate rest',
                    'Consider consulting with a healthcare provider if symptoms persist',
                    'Follow up if symptoms worsen or new symptoms develop'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error in generateMockReport: ' . $e->getMessage());
            
            // Return a basic fallback report
            return [
                'agent' => 'AI Medical Assistant',
                'user' => 'Anonymous',
                'timestamp' => now()->toISOString(),
                'chiefComplaint' => 'Unable to process conversation details',
                'summary' => 'Error occurred while processing consultation data',
                'symptoms' => ['Unable to determine symptoms'],
                'duration' => 'Unknown',
                'severity' => 'mild',
                'medicationsMentioned' => [],
                'recommendations' => [
                    'Please consult with a healthcare provider',
                    'Monitor your symptoms'
                ]
            ];
        }
    }
} 