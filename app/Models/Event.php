<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'location',
        'event_date',
        'points_required',
        'max_capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'points_required' => 'integer',
            'max_capacity' => 'integer',
            'status' => 'string',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function eventInvitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class, 'event_id');
    }

    public function eventRequests(): HasMany
    {
        return $this->hasMany(EventRequest::class, 'event_id');
    }
}
