<?php

namespace App\Notifications;

use App\Models\SheetBackup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class SheetBackupReady extends Notification implements ShouldQueue
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
        $url = URL::signedRoute(
            'sheet-backups.download',
            ['backup' => $this->backup->id],
            now()->addDays(7),
        );

        return (new MailMessage)
            ->subject(__('Your sheet backup is ready'))
            ->line(__('Your sheet backup has finished and is ready to download.'))
            ->line(__('The backup contains :count sheets (:size MB).', [
                'count' => $this->backup->sheet_count,
                'size' => round($this->backup->file_size / 1024 / 1024, 1),
            ]))
            ->action(__('Download backup'), $url)
            ->line(__('This download link is valid for 7 days, after which the backup will be deleted automatically.'));
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
