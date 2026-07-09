<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\WebAuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [AgentController::class, 'agent']);
    Route::post('/agent/ask', [AgentController::class, 'ask']);
    Route::get('/history', [AgentController::class, 'history']);
    Route::get('/history/prompts', [AgentController::class, 'prompts']);
    Route::get('/history/{id}', [AgentController::class, 'agent']);
    Route::post('/logout', [WebAuthController::class, 'logout']);
    Route::patch('/history/{id}', [AgentController::class, 'renameHistory']);
    Route::delete('/history/{id}', [AgentController::class, 'deleteHistory']);
});