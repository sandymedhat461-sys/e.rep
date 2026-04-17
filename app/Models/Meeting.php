<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    protected $table = 'meetings';

    protected $fillable = [
        'doctor_id',
        'rep_id',
        'status',
        'meeting_link',
        'notes',
        'room_name',
        'scheduled_at',
        'points_awarded',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function rep(): BelongsTo
    {
        return $this->belongsTo(MedicalRep::class, 'rep_id');
    }

    public function medicalRep(): BelongsTo
    {
        return $this->belongsTo(MedicalRep::class, 'rep_id');
    }
}
