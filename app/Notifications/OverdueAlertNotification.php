<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Connect Amravati: CRITICAL OVERDUE ALERT')
            ->greeting('Attention ' . $notifiable->name . ',')
            ->line('The following task assigned to you is officially OVERDUE:')
            ->line('Task Number: ' . $this->task->task_number)
            ->line('Title: ' . $this->task->title)
            ->line('Original Due Date: ' . $this->task->due_date)
            ->action('Update Progress Instantly', url('/tasks/' . $this->task->id))
            ->line('Failing to submit updates may trigger administrative escalations.')
            ->salutation('District IT Enforcement Cell Amravati');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'task_number' => $this->task->task_number,
            'due_date' => $this->task->due_date,
            'alert_type' => 'Overdue',
        ];
    }
}
