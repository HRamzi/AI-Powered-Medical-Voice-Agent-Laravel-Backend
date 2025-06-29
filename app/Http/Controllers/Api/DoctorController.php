<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DoctorController extends Controller
{
    /**
     * AI Doctor Agents data (matching frontend shared/list.tsx)
     */
    private function getAIDoctorAgents()
    {
        return [
            [
                "id" => 1,
                "specialist" => "General Physician",
                "description" => "Helps with everyday health concerns and common symptoms.",
                "image" => "/doctor1.png",
                "agentPrompt" => "You are a friendly General Physician AI. Greet the user and quickly ask what symptoms they're experiencing. Keep responses short and helpful.",
                "voiceId" => "will",
                "subscriptionRequired" => false
            ],
            [
                "id" => 2,
                "specialist" => "Pediatrician", 
                "description" => "Expert in children's health, from babies to teens.",
                "image" => "/doctor2.png",
                "agentPrompt" => "You are a kind Pediatrician AI. Ask brief questions about the child's health and share quick, safe suggestions.",
                "voiceId" => "chris",
                "subscriptionRequired" => true
            ],
            [
                "id" => 3,
                "specialist" => "Dermatologist",
                "description" => "Handles skin issues like rashes, acne, or infections.",
                "image" => "/doctor3.png", 
                "agentPrompt" => "You are a knowledgeable Dermatologist AI. Ask short questions about the skin issue and give simple, clear advice.",
                "voiceId" => "sarge",
                "subscriptionRequired" => true
            ],
            [
                "id" => 4,
                "specialist" => "Psychologist",
                "description" => "Supports mental health and emotional well-being.",
                "image" => "/doctor4.png",
                "agentPrompt" => "You are a caring Psychologist AI. Ask how the user is feeling emotionally and give short, supportive tips.",
                "voiceId" => "susan", 
                "subscriptionRequired" => true
            ],
            [
                "id" => 5,
                "specialist" => "Nutritionist",
                "description" => "Provides advice on healthy eating and weight management.",
                "image" => "/doctor5.png",
                "agentPrompt" => "You are a motivating Nutritionist AI. Ask about current diet or goals and suggest quick, healthy tips.",
                "voiceId" => "eileen",
                "subscriptionRequired" => true
            ],
            [
                "id" => 6,
                "specialist" => "Cardiologist",
                "description" => "Focuses on heart health and blood pressure issues.",
                "image" => "/doctor6.png",
                "agentPrompt" => "You are a calm Cardiologist AI. Ask about heart symptoms and offer brief, helpful advice.",
                "voiceId" => "charlotte",
                "subscriptionRequired" => true
            ],
            [
                "id" => 7,
                "specialist" => "ENT Specialist",
                "description" => "Handles ear, nose, and throat-related problems.",
                "image" => "/doctor7.png",
                "agentPrompt" => "You are a friendly ENT AI. Ask quickly about ENT symptoms and give simple, clear suggestions.",
                "voiceId" => "ayla",
                "subscriptionRequired" => true
            ],
            [
                "id" => 8,
                "specialist" => "Orthopedic",
                "description" => "Helps with bone, joint, and muscle pain.",
                "image" => "/doctor8.png",
                "agentPrompt" => "You are an understanding Orthopedic AI. Ask where the pain is and give short, supportive advice.",
                "voiceId" => "aaliyah",
                "subscriptionRequired" => true
            ],
            [
                "id" => 9,
                "specialist" => "Gynecologist",
                "description" => "Cares for women's reproductive and hormonal health.",
                "image" => "/doctor9.png",
                "agentPrompt" => "You are a respectful Gynecologist AI. Ask brief, gentle questions and keep answers short and reassuring.",
                "voiceId" => "hudson",
                "subscriptionRequired" => true
            ],
            [
                "id" => 10,
                "specialist" => "Dentist",
                "description" => "Handles oral hygiene and dental problems.",
                "image" => "/doctor10.png",
                "agentPrompt" => "You are a cheerful Dentist AI. Ask about the dental issue and give quick, calming suggestions.",
                "voiceId" => "atlas",
                "subscriptionRequired" => true
            ]
        ];
    }

    /**
     * Suggest doctors based on user symptoms using OpenAI
     * Matches frontend: POST /api/suggest-doctors
     */
    public function suggest(Request $request)
    {
        $request->validate([
            'notes' => 'required|string'
        ]);

        try {
            $notes = $request->input('notes');
            $doctorAgents = $this->getAIDoctorAgents();

            // Temporary: Return mock suggestions instead of calling OpenAI
            // TODO: Configure OpenAI API key and enable AI suggestions
            
            // Simple keyword-based suggestion logic
            $suggestedDoctors = $this->getMockSuggestions($notes, $doctorAgents);
            
            return response()->json($suggestedDoctors);

            /* 
            // Original OpenAI implementation (commented out until API key is configured)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => env('OPENAI_MODEL', 'gpt-4'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => json_encode($doctorAgents)
                    ],
                    [
                        'role' => 'user', 
                        'content' => "User Notes/Symptoms: {$notes}, Depends on user notes and symptoms, Please suggest list of doctors, Return Object in JSON only"
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawResp = $data['choices'][0]['message']['content'];
                
                // Clean up the response (remove markdown formatting)
                $cleanResp = trim($rawResp);
                $cleanResp = str_replace(['```json', '```'], '', $cleanResp);
                
                $suggestedDoctors = json_decode($cleanResp, true);
                
                return response()->json($suggestedDoctors);
            }

            return response()->json(['error' => 'Failed to get doctor suggestions'], 500);
            */

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mock suggestion logic based on keywords
     * Returns appropriate doctors based on symptom keywords
     */
    private function getMockSuggestions($notes, $doctorAgents)
    {
        $notes = strtolower($notes);
        $suggestions = [];

        // Always include General Physician as first suggestion
        $suggestions[] = $doctorAgents[0]; // General Physician

        // Keyword-based suggestions
        if (str_contains($notes, 'skin') || str_contains($notes, 'rash') || str_contains($notes, 'acne')) {
            $suggestions[] = $doctorAgents[2]; // Dermatologist
        }
        
        if (str_contains($notes, 'heart') || str_contains($notes, 'chest') || str_contains($notes, 'blood pressure')) {
            $suggestions[] = $doctorAgents[5]; // Cardiologist
        }
        
        if (str_contains($notes, 'child') || str_contains($notes, 'baby') || str_contains($notes, 'kid')) {
            $suggestions[] = $doctorAgents[1]; // Pediatrician
        }
        
        if (str_contains($notes, 'mental') || str_contains($notes, 'stress') || str_contains($notes, 'anxiety') || str_contains($notes, 'depression')) {
            $suggestions[] = $doctorAgents[3]; // Psychologist
        }
        
        if (str_contains($notes, 'diet') || str_contains($notes, 'weight') || str_contains($notes, 'nutrition')) {
            $suggestions[] = $doctorAgents[4]; // Nutritionist
        }
        
        if (str_contains($notes, 'ear') || str_contains($notes, 'nose') || str_contains($notes, 'throat') || str_contains($notes, 'sinus')) {
            $suggestions[] = $doctorAgents[6]; // ENT Specialist
        }
        
        if (str_contains($notes, 'bone') || str_contains($notes, 'joint') || str_contains($notes, 'muscle') || str_contains($notes, 'pain')) {
            $suggestions[] = $doctorAgents[7]; // Orthopedic
        }
        
        if (str_contains($notes, 'teeth') || str_contains($notes, 'dental') || str_contains($notes, 'tooth')) {
            $suggestions[] = $doctorAgents[9]; // Dentist
        }

        // Remove duplicates and limit to 3 suggestions
        $suggestions = array_unique($suggestions, SORT_REGULAR);
        return array_slice($suggestions, 0, 3);
    }
} 