<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class ClerkAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Extract Bearer token from Authorization header
            $token = $this->extractBearerToken($request);
            
            if (!$token) {
                return response()->json(['error' => 'Authorization token required'], 401);
            }

            // Verify and decode the JWT token
            $payload = $this->verifyClerkToken($token);
            
            if (!$payload) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            // Find or create user from Clerk claims
            $user = User::createOrUpdateFromClerk($payload);
            
            // Set the authenticated user for this request
            auth()->setUser($user);
            
            return $next($request);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Extract Bearer token from Authorization header
     */
    private function extractBearerToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        
        return substr($authHeader, 7); // Remove "Bearer " prefix
    }

    /**
     * Verify Clerk JWT token and return payload
     * Simplified version - in production, you should verify the signature
     */
    private function verifyClerkToken(string $token): ?array
    {
        try {
            // Split the JWT token into parts
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                throw new Exception('Invalid JWT format');
            }
            
            // Decode the payload (second part)
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
            
            if (!$payload) {
                throw new Exception('Invalid JWT payload');
            }
            
            // Validate Clerk-specific claims
            $this->validateClerkClaims($payload);
            
            return $payload;
            
        } catch (Exception $e) {
            throw new Exception('Token verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate Clerk JWT claims
     */
    private function validateClerkClaims(array $payload): void
    {
        // Check if token is expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }
        
        // Check issuer (should be Clerk)
        if (!isset($payload['iss']) || !str_contains($payload['iss'], 'clerk')) {
            throw new Exception('Invalid token issuer');
        }
        
        // Check if user ID exists
        if (!isset($payload['sub'])) {
            throw new Exception('Token missing user ID');
        }
        
        // Additional validations can be added here
        // e.g., audience (aud), not before (nbf), etc.
    }
}
