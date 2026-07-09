<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Passport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\HasApiTokens;
use Illuminate\Notifications\Notifiable;


class oauth_refresh_tokens extends Model
{
    protected $table = 'oauth_refresh_tokens';
    protected $fillable = [
        'id',
        'access_token_id',
        'revoked',
        'expires_at',
    ];
    protected $hidden = [
        'id',
        'access_token_id',
        'revoked',
        'expires_at',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    public $timestamps = false;
}
