@extends('layouts.app')

@section('title', 'Create Account — Aureon')

@section('content')
<div class="auth-center">
    <div class="auth-card mt-3">

        <div class="text-center mb-3">
            <img src="{{ asset('robo.png') }}" alt="Aureon" width="44" height="44" class="mb-2">
            <h2 class="fw-bold text-white mb-1" style="font-size: 1.4rem;">Create your account</h2>
            <p class="auth-subtitle">Start chatting with Aureon for free</p>
        </div>

        {{-- Flash error --}}
        @if(session('error'))
            <div class="auth-alert mb-4">{{ session('error') }}</div>
        @endif

        <form action="/register" method="POST" novalidate>
            @csrf

            <div class="auth-field mb-2">
                <label for="name">Full name</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="John Doe"
                       required
                       autofocus
                       class="{{ $errors->has('name') ? 'is-error' : '' }}">
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field mb-2">
                <label for="email">Email address</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required
                       class="{{ $errors->has('email') ? 'is-error' : '' }}">
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field mb-2">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Min. 8 characters"
                           required
                           class="{{ $errors->has('password') ? 'is-error' : '' }}">
                    <button type="button" class="eye-btn" onclick="togglePassword('password', this)" tabindex="-1">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field mb-3">
                <label for="password_confirmation">Confirm password</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="••••••••"
                           required>
                    <button type="button" class="eye-btn" onclick="togglePassword('password_confirmation', this)" tabindex="-1">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-btn w-100">Create Account</button>
        </form>

        <p class="text-center mt-3 auth-footer-text">
            Already have an account? <a href="/login">Sign in</a>
        </p>
    </div>
</div>
@endsection

@section('styles')
<style>
.auth-center {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    width: 100%;
    padding: 24px;
    box-sizing: border-box;
    /* overflow-y: auto; */
}
.auth-card {
    background: var(--bg-card);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 20px;
    padding: 22px 20px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5);
    /* overflow-y: auto;
    overflow-x: hidden; */
    margin-bottom: 20px;
}
.auth-subtitle {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0;
}
.auth-alert {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.875rem;
}
.auth-field label {
    display: block;
    color: #8a8a8a;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 8px;
}
.auth-field input {
    width: 100%;
    background: #181818;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #ececec;
    padding: 10px 14px;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
    box-sizing: border-box;
}
.auth-field input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(84, 54, 218, 0.15);
}
.auth-field input.is-error { border-color: #ef4444; }
.auth-field input::placeholder { color: #444; }
/* Fix browser autofill white background */
.auth-field input:-webkit-autofill,
.auth-field input:-webkit-autofill:hover,
.auth-field input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 30px #181818 inset !important;
    -webkit-text-fill-color: #ececec !important;
    caret-color: #ececec;
    border-radius: 12px;
}
.password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.password-wrapper input { padding-right: 44px; }
.eye-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: #555;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    transition: color 0.2s;
}
.eye-btn:hover { color: #ececec; }
.field-error {
    display: block;
    color: #f87171;
    font-size: 0.8rem;
    margin-top: 6px;
}
.auth-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 11px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    font-family: inherit;
    letter-spacing: 0.01em;
}
.auth-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}
.auth-btn:active { transform: translateY(0); }
.auth-footer-text {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin: 0;
}
.auth-footer-text a {
    color: #7c6ce6;
    font-weight: 600;
    text-decoration: none;
}
.auth-footer-text a:hover { text-decoration: underline; }
</style>
@endsection

@section('scripts')
<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword
        ? '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
@endsection
