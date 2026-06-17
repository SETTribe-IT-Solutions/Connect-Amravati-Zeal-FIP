<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Connect Amravati: New Official Announcement')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new official announcement has been published targeting your jurisdiction:')
            ->line('Title: ' . $this->announcement->title)
            ->action('View Notice Board', url('/announcements/' . $this->announcement->id))
            ->salutation('District Administration Amravati');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'sender' => $this->announcement->sender->name ?? 'System',
        ];
    }
}
