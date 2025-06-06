<?php

namespace App\Listeners;

use App\Events\JoinRequestSubmitted;
use App\Models\User;
use App\Notifications\JoinRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
        Log::error($admin);
        $admin->notify(new JoinRequestNotification($event->user,$event->community));
    }
}
