<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TasksExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Task::with(['assignee', 'assigner'])->get();
    }

    public function headings(): array
    {
        return [
            'Task ID',
            'Task Number',
            'Title',
            'Description',
            'Priority',
            'Status',
            'Due Date',
            'Assigned To',
            'Assigned By',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->task_number,
            $row->title,
            $row->description,
            $row->priority,
            $row->status,
            $row->due_date,
            $row->assignee->name ?? 'N/A',
            $row->assigner->name ?? 'N/A',
            $row->remarks,
        ];
    }
}
