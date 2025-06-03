<?php

namespace App\Notifications;

use App\Models\Puzzle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPuzzleApprovalNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */public Puzzle $puzzle;
    public function __construct(Puzzle $puzzle)
    {
        $this->puzzle=$puzzle;
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

    public function toDatabase($notifiable)
    {
        return [
            'message' => "A new puzzle '{$this->puzzle->title}' needs admin approval!",
            'puzzle_id' => $this->puzzle->id,
        ];
    }
}
