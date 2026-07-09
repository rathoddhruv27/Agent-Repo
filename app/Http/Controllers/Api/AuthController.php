<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(AuthRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $accessToken = $user->createToken('Personal Access Token');
            $refreshToken = $user->createToken('Refresh Token');

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $accessToken->accessToken,
                    // 'refresh_token' => $refreshToken->accessToken,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and create token
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();
            
            // Revoke previous tokens
            $user->tokens()->delete();

            $accessToken = $user->createToken('Personal Access Token');
            $refreshToken = $user->createToken('Refresh Token');
            
            // \DB::table('oauth_access_tokens')->where('user_id', $user->id)->update([
            //     'revoked' => true,
            // ]);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $accessToken->accessToken,
                    'refresh_token' => $refreshToken->accessToken,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(AuthRequest $request): JsonResponse
    {
        try {
            $refreshTokenString = $request->refresh_token;
            
            // Decode the JWT to get the token ID (jti claim)
            $tokenParts = explode('.', $refreshTokenString);
            // dd($tokenParts);

            if (count($tokenParts) !== 3) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid refresh token format'
                ], 401);
            }
            
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            if (!isset($payload['jti'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid refresh token structure'
                ], 401);
            }
            
            $tokenId = $payload['jti'];
            
            // Find the token by ID
            $token = Token::find($tokenId);
            // dd($token);
            
            if (!$token || $token->revoked || $token->expires_at->isPast()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired refresh token'
                ], 401);
            }

            $user = $token->user;
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }
            
            // Revoke all old tokens for this user
            $user->tokens()->delete();
            
            // Create new access token
            $newAccessToken = $user->createToken('Personal Access Token');
            $newRefreshToken = $user->createToken('Refresh Token');

            return response()->json([
                'status' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $newAccessToken->accessToken,
                    'refresh_token' => $newRefreshToken->accessToken,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->token()->revoke();

            return response()->json([
                'status' => true,
                'message' => 'Logout successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the authenticated User
     */
    public function user(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'User profile retrieved successfully',
                'data' => [
                    'user' => new UserResource($request->user()),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
