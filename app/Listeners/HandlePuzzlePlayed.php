<?php

namespace App\Listeners;

use App\Events\PuzzlePlayed;
use App\Notifications\PuzzlePlayedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandlePuzzlePlayed
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
    public function handle(PuzzlePlayed $event): void
    {
        $event->creator->notify(new PuzzlePlayedNotification($event->puzzle,$event->user,$event->creator,$event->isCorrect));
        $scoreChanges = [
            'hard' => ['gain' => 15, 'lose' => 10, 'creator' => 3],
            'med' => ['gain' => 10, 'lose' => 5, 'creator' => 2],
            'low' => ['gain' => 5, 'lose' => 2, 'creator' => 1],
        ];
        if (!$event->puzzle->community_id) {
            if ($event->puzzle->status) {
                if ($event->isCorrect) {
                    $event->user->overall_score += $scoreChanges[$event->puzzle->level]['gain'];
                } else {
                    $event->user->overall_score -= $scoreChanges[$event->puzzle->level]['lose'];
                    $event->creator->overall_score += $scoreChanges[$event->puzzle->level]['creator'];
                }
                $event->user->save();
                $event->creator->save();
            }
        }
    }
}
