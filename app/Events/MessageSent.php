<?php

namespace App\Events;

use App\Models\Company;
use App\Models\Doctor;
use App\Models\MedicalRep;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcasts on private channels via Laravel's broadcaster abstraction
 * (Pusher, Reverb, Ably, redis, etc.) — no driver-specific configuration here.
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        //
    }

    public function broadcastOn(): array
    {
        $channels = [];
        $receiver = $this->message->receiver;

        if ($receiver instanceof MedicalRep) {
            $channels[] = new PrivateChannel('chat.medical_rep.'.$receiver->id);
        } elseif ($receiver instanceof Doctor) {
            $channels[] = new PrivateChannel('chat.doctor.'.$receiver->id);
        } elseif ($receiver instanceof Company) {
            $channels[] = new PrivateChannel('chat.company.'.$receiver->id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->sender;
        $senderName = '';
        if ($sender instanceof Doctor || $sender instanceof MedicalRep) {
            $senderName = (string) $sender->full_name;
        } elseif ($sender instanceof Company) {
            $senderName = (string) $sender->company_name;
        }

        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $senderName,
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }
}
