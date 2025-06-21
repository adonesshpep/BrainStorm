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

class PuzzleGetVoted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Puzzle $puzzle;
    public User $user;
    public bool $isUp;
    public function __construct(Puzzle $puzzle,User $user, bool $isUp)
    {
        $this->puzzle=$puzzle;
        $this->user=$user;
        $this->isUp=$isUp;
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
