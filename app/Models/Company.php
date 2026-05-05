<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'companies';

    protected $fillable = [
        'company_name',
        'email',
        'password',
        'hotline',
        'commercial_register',
        'company_profile_image',
        'company_id_image',
        'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function medicalReps(): HasMany
    {
        return $this->hasMany(MedicalRep::class);
    }

    public function drugs(): HasMany
    {
        return $this->hasMany(Drug::class);
    }
}
