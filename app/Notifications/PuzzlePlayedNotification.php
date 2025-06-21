<?php

namespace App\Notifications;

use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PuzzlePlayedNotification extends Notification
{
    use Queueable;

    public Puzzle $puzzle;
    public User $user;
    public User $creator;
    public bool $isCorrect;
    public function __construct(Puzzle $puzzle,User $user, User $creator, bool $isCorrect)
    {
        $this->puzzle=$puzzle;
        $this->user=$user;
        $this->creator=$creator;
        $this->isCorrect=$isCorrect;
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
        $meesage=($this->isCorrect)?$this->user->name.' solved your puzzle '.$this->puzzle->title:$this->user->name.' failed to solve your puzzle '.$this->puzzle->title;
        return [
            'message'=>$meesage,
            'user_id'=>$this->user->id,
            'puzzle_id'=>$this->puzzle->id
        ];
    }
}
