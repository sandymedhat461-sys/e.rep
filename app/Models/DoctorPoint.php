<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPoint extends Model
{
    protected $table = 'doctor_points';

    protected $fillable = [
        'doctor_id',
        'source',
        'source_id',
        'value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
