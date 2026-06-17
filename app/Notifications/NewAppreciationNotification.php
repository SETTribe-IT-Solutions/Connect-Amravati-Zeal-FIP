<?php

namespace App\Notifications;

use App\Models\Appreciation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppreciationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appreciation;

    public function __construct(Appreciation $appreciation)
    {
        $this->appreciation = $appreciation;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Connect Amravati: Congratulatory Commendation Certificate')
            ->greeting('Congratulations ' . $notifiable->name . ',')
            ->line('You have received a formal certificate of appreciation on the Connect Amravati wall!')
            ->line('Category: ' . $this->appreciation->category)
            ->line('Message: ' . $this->appreciation->message)
            ->action('Access Wall of Fame', url('/appreciations'))
            ->salutation('Office of the District Collector Amravati');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appreciation_id' => $this->appreciation->id,
            'category' => $this->appreciation->category,
            'issuer' => $this->appreciation->sender->name ?? 'System',
        ];
    }
}
