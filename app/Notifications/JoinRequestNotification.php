<?php

namespace App\Notifications;

use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public User $user;
    public Community $community;

    public function __construct(User $user,Community $community)
    {
        $this->user=$user;
        $this->community=$community;
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
        return [
            'message'=>$this->user->name.' request joining to '.$this->community->name,
            'community_id'=>$this->community->id,
            'user_id'=>$this->user->id
        ];
    }
}
