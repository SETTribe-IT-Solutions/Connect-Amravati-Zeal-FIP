<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Models\TaskHistory;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function getTasksForUser($userId, $filters = [])
    {
        $query = $this->model->with(['assignee', 'assigner']);

        if (isset($filters['status']) && $filters['status'] !== 'All') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority']) && $filters['priority'] !== 'All') {
            $query->where('priority', $filters['priority']);
        }

        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_to', $userId)
              ->orWhere('assigned_by', $userId);
        })->latest()->paginate(15);
    }

    public function createWithHistory(array $attributes, $creatorId)
    {
        return DB::transaction(function () use ($attributes, $creatorId) {
            $task = $this->create($attributes);

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => $creatorId,
                'action' => 'Created',
                'to_state' => 'Pending',
                'remarks' => $attributes['remarks'] ?? 'Task assigned initial state.',
            ]);

            return $task;
        });
    }

    public function updateStatus($taskId, $status, $remarks, $userId)
    {
        return DB::transaction(function () use ($taskId, $status, $remarks, $userId) {
            $task = $this->find($taskId);
            $oldStatus = $task->status;

            $task->update([
                'status' => $status,
                'remarks' => $remarks ?: $task->remarks,
            ]);

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'action' => 'Status Change',
                'from_state' => $oldStatus,
                'to_state' => $status,
                'remarks' => $remarks,
            ]);

            return $task;
        });
    }
}
