<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function exportExcel()
    {
        return Excel::download(new TasksExport, 'connect_amravati_tasks_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $tasks = Task::with(['assignee', 'assigner'])->get();
        $pdf = Pdf::loadView('reports.pdf_template', compact('tasks'));
        return $pdf->download('connect_amravati_tasks_' . date('Y-m-d') . '.pdf');
    }
}
