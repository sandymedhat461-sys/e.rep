<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class MedicalRep extends Authenticatable
{
    protected $table = 'medical_reps';

    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'category_id',
        'full_name',
        'email',
        'password',
        'phone',
        'national_id',
        'company_name',
        'status',
        'profile_image',
        'company_id_image',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DrugCategory::class, 'category_id');
    }

    public function eventInvitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class, 'invited_by_rep_id');
    }

    public function invitedByRep(): HasMany
    {
        return $this->hasMany(EventInvitation::class, 'invited_by_rep_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'rep_id');
    }

    public function drugSamples(): HasMany
    {
        return $this->hasMany(DrugSample::class, 'rep_id');
    }

    public function repTargets(): HasMany
    {
        return $this->hasMany(RepTarget::class, 'rep_id');
    }

    public function repDrugAssignments(): HasMany
    {
        return $this->hasMany(RepDrugAssignment::class, 'rep_id');
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'rep_doctors', 'rep_id', 'doctor_id');
    }

    public function drugs(): BelongsToMany
    {
        return $this->belongsToMany(Drug::class, 'rep_drug_assignments', 'rep_id', 'drug_id');
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
