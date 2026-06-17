<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification implements ShouldQueue
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
            ->subject('Connect Amravati: Task Completed Alert')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The task you assigned has been marked as completed: ' . $this->task->title)
            ->line('Completed By: ' . $this->task->assignee->name)
            ->line('Completion Remarks: ' . $this->task->remarks)
            ->action('Audit Task Details', url('/tasks/' . $this->task->id))
            ->salutation('District Administration Amravati');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'completed_by' => $this->task->assignee->name,
            'status' => 'Completed',
        ];
    }
}
