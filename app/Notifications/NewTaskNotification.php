<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTaskNotification extends Notification implements ShouldQueue
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
            ->subject('Connect Amravati: New Task Allocated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been allocated a new duty task: ' . $this->task->title)
            ->line('Description: ' . $this->task->description)
            ->line('Priority: ' . $this->task->priority)
            ->action('Access Task Board', url('/tasks/' . $this->task->id))
            ->line('Please complete this duty on or before: ' . $this->task->due_date)
            ->salutation('District Administration Amravati');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'assigned_by' => $this->task->assigner->name,
            'due_date' => $this->task->due_date,
        ];
    }
}
