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

            // Make OpenAI API call to suggest doctors based on symptoms
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

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 