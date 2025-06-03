<?php

namespace App\Notifications;

use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PuzzleVotedNotification extends Notification
{
    use Queueable;

    public Puzzle $puzzle;
    public User $user;
    public bool $isUp;
    public function __construct(Puzzle $puzzle, User $user, bool $isUp)
    {
        $this->puzzle=$puzzle;
        $this->user=$user;
        $this->isUp=$isUp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable){
        $message=($this->isUp)?$this->user->name.' voted up for your '.$this->puzzle->title.' puzzle': $this->user->name . ' voted down for your ' . $this->puzzle->title . ' puzzle';
        return [
            'message'=>$message,
            'puzzle_id'=>$this->puzzle->id,
            'user_id'=>$this->user->id
        ];
    }
}
