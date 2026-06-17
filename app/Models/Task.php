<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'task_number',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'assigned_by',
        'assigned_to',
        'remarks',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }
}
