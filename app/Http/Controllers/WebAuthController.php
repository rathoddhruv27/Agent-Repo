<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class WebAuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect('/login');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended('/');
            }
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $file->move(public_path('avatars'), $filename);

            if ($user->profile_image) {
                $oldPath = public_path($user->profile_image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $imagePath = 'avatars/' . $filename;
            $user->update([
                'profile_image' => $imagePath,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Profile image uploaded successfully',
                'url' => '/' . $imagePath,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No file uploaded',
        ], 400);
    }

    public function updateName(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Display name updated successfully',
            'name' => $user->name,
        ]);
    }

    public function updateInstructions(Request $request)
    {
        $validated = $request->validate([
            'custom_instructions_about' => ['nullable', 'string', 'max:5000'],
            'custom_instructions_respond' => ['nullable', 'string', 'max:5000'],
            'custom_instructions_enabled' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        $user->update([
            'custom_instructions_about' => $validated['custom_instructions_about'],
            'custom_instructions_respond' => $validated['custom_instructions_respond'],
            'custom_instructions_enabled' => $validated['custom_instructions_enabled'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Personalization instructions updated successfully',
        ]);
    }
}
