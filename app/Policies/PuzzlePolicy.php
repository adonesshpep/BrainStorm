<?php

namespace App\Policies;

use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PuzzlePolicy
{
    public function modify(User $user, Puzzle $puzzle)
    {
        return ($user->id===$puzzle->user_id);
    }
}
