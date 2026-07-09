<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use App\Http\Requests\Auth\AuthRequest;
use App\Services\AuthTokenService;

class AuthController extends Controller
{

    protected AuthTokenService $authTokenService;
    public function __construct(AuthTokenService $authTokenService)
    {
        $this->authTokenService = $authTokenService;
    }

    public function register(AuthRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully.',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    public function login(AuthRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
                'data' => [],
            ], 401);
        }

        // Optional: revoke old tokens before issuing new ones
        $user->tokens()->delete();

        // Create new access token
        $tokenInstance = $user->createToken('auth_token');
        $accessToken = $tokenInstance->accessToken;

        // Create custom JWT-style refresh token using access token ID as jti
        $refreshToken = $this->authTokenService->generateRefreshToken(
            $tokenInstance->token->id,
            now()->addDays(30)->timestamp
        );  

        \DB::table('oauth_refresh_tokens')->insert([
            'id' => $refreshToken,
            'access_token_id' => $tokenInstance->token->id,
            'revoked' => false,
            'expires_at' => now()->addDays(30),
        ]);

        // dd($refreshToken);
        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => $tokenInstance->token->expires_at,
            ],
        ], 200);
    }

    public function refreshToken(AuthRequest $request)
    {
        $validated = $request->validated();
        $refreshToken = trim($validated['refresh_token']);

        $tokenParts = explode('.', $refreshToken);
        // dd($tokenParts);
        if (count($tokenParts) !== 3) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token format.',
                'data' => [],
            ], 400);
        }

        $payloadEncoded = $tokenParts[1];
        $payloadJson = $this->authTokenService->base64UrlDecode($payloadEncoded);
        $payload = json_decode($payloadJson, true);

        if (!$payload || !isset($payload['jti'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token payload or missing jti.',
                'data' => [],
            ], 400);
        }

        // dd($payload, $refreshToken);
        if (!$this->authTokenService->verifyRefreshToken($refreshToken)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid refresh token signature.',
                'data' => [],
            ], 401);
        }

        if (!isset($payload['exp']) || now()->timestamp >= $payload['exp']) {
            return response()->json([
                'status' => false,
                'message' => 'Refresh token has expired.',
                'data' => [],
            ], 401);
        }

        $jti = $payload['jti'];

        $accessToken = Token::find($jti);

        // dd($accessToken, $jti, $payload, $request->all());

        if (!$accessToken || $accessToken->revoked || $accessToken->expires_at->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'Token is invalid, revoked, or expired.',
                'data' => [],
            ], 401);
        }

        $user = User::find($accessToken->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
                'data' => [],
            ], 404);
        }

        DB::beginTransaction();

        try {
            $user->tokens()->delete();

            $tokenInstance = $user->createToken('auth_token');
            $newAccessToken = $tokenInstance->accessToken;

            $newRefreshToken = $this->authTokenService->generateRefreshToken(
                $tokenInstance->token->id,
                now()->addDays(30)->timestamp
            );

            \DB::table('oauth_refresh_tokens')
                ->where('id', $refreshToken)
                ->update([
                    'id' => $newRefreshToken,
                    'access_token_id' => $tokenInstance->token->id,
                    'revoked' => false,
                    'expires_at' => now()->addDays(30),
                ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tokens refreshed successfully.',
                'data' => [
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'expires_at' => $tokenInstance->token->expires_at,
                ],
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to refresh token.',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();

        if (!$accessToken) {
            return response()->json([
                'status' => false,
                'message' => 'Authenticated token not found.',
                'data' => [],
            ], 401);
        }

        if ($accessToken->revoked) {
            return response()->json([
                'status' => false,
                'message' => 'User is already logged out or session expired.',
                'data' => [],
            ], 401);
        }

        $accessToken->revoke();

        \DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Logout successful.',
            'data' => [],
        ], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'message' => 'User fetched successfully.',
            'data' => $user,
        ], 200);
    }
}