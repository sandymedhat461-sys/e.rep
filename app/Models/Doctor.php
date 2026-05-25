<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Authenticatable
{
    protected $table = 'doctors';

    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'phone',
        'national_id',
        'email',
        'password',
        'specialization',
        'hospital_name',
        'status',
        'syndicate_id',
        'profile_image',
        'syndicate_id_image',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function drugReviews(): HasMany
    {
        return $this->hasMany(DrugReview::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function eventInvitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    public function eventRequests(): HasMany
    {
        return $this->hasMany(EventRequest::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function doctorPoints(): HasMany
    {
        return $this->hasMany(DoctorPoint::class);
    }

    public function drugSamples(): HasMany
    {
        return $this->hasMany(DrugSample::class);
    }

    public function medicalReps(): BelongsToMany
    {
        return $this->belongsToMany(MedicalRep::class, 'rep_doctors', 'doctor_id', 'rep_id');
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
