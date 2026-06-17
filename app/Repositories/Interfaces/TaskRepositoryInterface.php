<?php

namespace App\Repositories\Interfaces;

use App\Repositories\BaseRepositoryInterface;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    public function getTasksForUser($userId, $filters = []);
    public function createWithHistory(array $attributes, $creatorId);
    public function updateStatus($taskId, $status, $remarks, $userId);
}
