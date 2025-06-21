<?php

namespace App\Events;

use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PuzzlePlayed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Puzzle $puzzle;
    public User $user;
    public User $creator;
    public bool $isCorrect;
    public function __construct(Puzzle $puzzle, User $user,User $creator,bool $isCorrect)
    {
        $this->puzzle=$puzzle;
        $this->user=$user;
        $this->creator=$creator;
        $this->isCorrect=$isCorrect;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
