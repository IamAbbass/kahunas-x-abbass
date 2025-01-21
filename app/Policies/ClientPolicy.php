<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    public function update(User $user, Client $client) {
        return $user->id === $client->coach_id;
    }

    public function delete(User $user, Client $client) {
        return $user->id === $client->coach_id;
    }
}
