<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;

class CreateUserAvatarOnRegistration
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
    public function handle(Registered $event): void
    {
        $user = $event->user;
        $user->avatar();
        \Log::info("Default avatar created or retrieved for new user: {$user->id}");
    }
}
