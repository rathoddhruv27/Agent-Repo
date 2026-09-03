<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'profile_image', 'custom_instructions_about', 'custom_instructions_respond', 'custom_instructions_enabled', 'gemini_api_key', 'openai_api_key', 'groq_api_key', 'deepseek_api_key', 'anthropic_api_key'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{   
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'custom_instructions_about',
        'custom_instructions_respond',
        'custom_instructions_enabled',
        'gemini_api_key',
        'openai_api_key',
        'groq_api_key',
        'deepseek_api_key',
        'anthropic_api_key',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the interactions for the user.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
}
