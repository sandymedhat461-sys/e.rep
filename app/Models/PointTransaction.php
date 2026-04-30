<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    protected $table = 'point_transactions';

    protected $fillable = [
        'pointable_type',
        'pointable_id',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function pointable(): MorphTo
    {
        return $this->morphTo();
    }
}
