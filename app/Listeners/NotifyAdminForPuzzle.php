<?php

namespace App\Listeners;

use App\Events\PuzzlePendingEvent;
use App\Models\Community;
use App\Models\User;
use App\Notifications\AdminPuzzleApprovalNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminForPuzzle
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PuzzlePendingEvent $event): void
    {
        $community=Community::where('id',$event->puzzle->community_id);
        $admin=User::where('id',$community->admin_id)->first();
        $admin->notify(new AdminPuzzleApprovalNotification($event->puzzle));

    }
}
