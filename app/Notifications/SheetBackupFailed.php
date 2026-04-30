<?php

namespace App\Notifications;

use App\Models\SheetBackup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SheetBackupFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SheetBackup $backup) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your sheet backup failed'))
            ->line(__('Your sheet backup could not be created.'))
            ->line(__('Error message:'))
            ->line($this->backup->error_message)
            ->line(__('Please try again or contact an administrator if the problem persists.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
