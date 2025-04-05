<?php

namespace App\Policies;

use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SolutinoPolicy
{
    public function add(User $user, Puzzle $puzzle)
    {
        return ($user->id === $puzzle->user_id) ? Response::allow() : Response::deny('not authorized');
    }
}
