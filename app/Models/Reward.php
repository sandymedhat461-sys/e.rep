<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $table = 'rewards';

    protected $fillable = [
        'company_id',
        'title',
        'name',
        'description',
        'points_required',
        'quantity_available',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'quantity_available' => 'integer',
            'status' => 'string',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }
}
