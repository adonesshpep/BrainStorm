<?php

namespace App\Listeners;

use App\Events\PuzzleGetVoted;
use App\Notifications\PuzzleVotedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCreatorOnPuzzleVote
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
    public function handle(PuzzleGetVoted $event): void
    {
        $event->puzzle->user()->notify(new PuzzleVotedNotification($event->puzzle,$event->user,$event->isUp));
    }
}
