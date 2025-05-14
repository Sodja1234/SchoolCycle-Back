<?php

namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use App\Mail\RegisteredUserMail;
use App\Events\RegisteredUserEvent;
use App\Mail\UserRegisteredMail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegisteredListener
{
    /**
     * Create the event listener.
     */
    public function __construct(private Mailer $mailer)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegisteredEvent $event): void
    {
        $this->mailer->send(new UserRegisteredMail($event->user));
    }
}