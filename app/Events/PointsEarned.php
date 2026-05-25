<?php

namespace App\Events;

use App\Models\DoctorPoint;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// cspell:ignore Dispatchable InteractsWithSockets SerializesModels
class PointsEarned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DoctorPoint $point) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('doctor.'.$this->point->doctor_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'points.earned';
    }

    /**
     * @return array{points_added: int, description: ?string, source_type: string}
     */
    public function broadcastWith(): array
    {
        return [
            'points_added' => $this->point->points,
            'description' => $this->point->description,
            'source_type' => $this->point->source_type,
        ];
    }
}
