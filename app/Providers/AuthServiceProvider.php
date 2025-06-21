<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Community;
use App\Models\Puzzle;
use App\Policies\CommunityPolicy;
use App\Policies\PuzzlePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Community::class => CommunityPolicy::class,
        Puzzle::class=>PuzzlePolicy::class,
    ];


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
