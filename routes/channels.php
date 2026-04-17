<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.doctor.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.medical_rep.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.company.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
