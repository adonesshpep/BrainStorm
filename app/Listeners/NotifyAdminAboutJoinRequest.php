<?php

namespace App\Listeners;

use App\Events\JoinRequestSubmitted;
use App\Models\User;
use App\Notifications\JoinRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminAboutJoinRequest
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
    public function handle(JoinRequestSubmitted $event): void
    {
        $admin=User::where('id',$event->community->admin_id)->first();
        $admin->notify(new JoinRequestNotification($event->user,$event->community));
    }
}
