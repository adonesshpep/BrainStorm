<?php

namespace App\Providers;

use App\Events\JoinRequestSubmitted;
use App\Events\NewPuzzleCreated;
use App\Events\PuzzleGetVoted;
use App\Events\PuzzlePendingEvent;
use App\Events\PuzzlePlayed;
use App\Listeners\AiCheckNotification;
use App\Listeners\HandlePuzzlePlayed;
use App\Listeners\NotifyAdminAboutJoinRequest;
use App\Listeners\NotifyAdminForPuzzle;
use App\Listeners\NotifyCreatorOnPuzzleVote;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\NewPuzzleCreated::class=>[
            \App\Listeners\AiCheckNotification::class,
        ],
        PuzzlePendingEvent::class=>[
            NotifyAdminForPuzzle::class,
        ],
        JoinRequestSubmitted::class=>[
            NotifyAdminAboutJoinRequest::class,
        ],
        PuzzleGetVoted::class=>[
            NotifyCreatorOnPuzzleVote::class,
        ],
        PuzzlePlayed::class=>[
            HandlePuzzlePlayed::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
