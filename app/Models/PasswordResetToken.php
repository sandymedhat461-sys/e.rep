<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    public $incrementing = false;

    protected $keyType = 'string';

   
    protected $primaryKey = 'email';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'user_type',
        'token',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
