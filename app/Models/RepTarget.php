<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepTarget extends Model
{
    protected $table = 'rep_targets';

    protected $fillable = [
        'rep_id',
        'target_type',
        'target_value',
        'current_value',
        'period',
        'period_start',
        'period_end',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'integer',
            'current_value' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function rep(): BelongsTo
    {
        return $this->belongsTo(MedicalRep::class, 'rep_id');
    }
}
