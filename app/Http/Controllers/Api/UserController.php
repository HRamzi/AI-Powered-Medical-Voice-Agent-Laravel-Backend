<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create or update user from Clerk authentication
     * Matches frontend: POST /api/users
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Check if user already exists
            $existingUser = User::where('email', $user->email)->first();
            
            if (!$existingUser) {
                // Create new user with default credits
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'clerk_id' => $user->clerk_id,
                    'credits' => 10 // Default credits for new users
                ]);
                
                return response()->json($newUser);
            }

            // Return existing user
            return response()->json($existingUser);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 