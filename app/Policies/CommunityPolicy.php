<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommunityPolicy
{
    public function manage(User $user,Community $community){
        return ($user->id===$community->admin_id);
    }
}
