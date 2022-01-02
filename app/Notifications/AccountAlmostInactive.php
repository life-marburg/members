<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountAlmostInactive extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Your account will be disabled in 3 days'))
            ->line(__('Due to inactivity, your account will be disabled in 3 days.'))
            ->line(__('If you want to continue using your account, log in now.'))
            ->action(__('Login'), route('dashboard'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
