<?php

namespace App\Services;

use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Notifications\NewTaskNotification;
use App\Notifications\TaskCompletedNotification;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Notification;

class TaskService
{
    protected $taskRepo;

    public function __construct(TaskRepositoryInterface $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function getTasksForUser($userId, $filters = [])
    {
        return $this->taskRepo->getTasksForUser($userId, $filters);
    }

    public function allocateTask(array $data, $assignedBy)
    {
        $data['task_number'] = 'TSK-' . strtoupper(uniqid());
        $data['assigned_by'] = $assignedBy;
        $data['status'] = 'Pending';

        $task = $this->taskRepo->createWithHistory($data, $assignedBy);

        // Notify Assignee
        $assignee = User::find($task->assigned_to);
        if ($assignee) {
            $assignee->notify(new NewTaskNotification($task));
        }

        return $task;
    }

    public function updateTaskStatus($taskId, $status, $remarks, $userId)
    {
        $task = $this->taskRepo->updateStatus($taskId, $status, $remarks, $userId);

        // If completed, notify the assigner
        if ($status === 'Completed') {
            $assigner = User::find($task->assigned_by);
            if ($assigner) {
                $assigner->notify(new TaskCompletedNotification($task));
            }
        }

        return $task;
    }
}
