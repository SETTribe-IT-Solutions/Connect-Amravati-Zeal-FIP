<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use App\Models\User;
use App\Models\Task;
use App\Models\Taluka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'priority']);
        $tasks = $this->taskService->getTasksForUser(Auth::id(), $filters);
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        // Only assigners can access the create task screen
        $userRole = Auth::user()->roles->first()->name ?? '';
        $assigners = ['Collector', 'Additional Collector', 'Deputy Collector', 'SDO', 'Tehsildar'];

        if (!in_array($userRole, $assigners) && Auth::user()->designation !== 'Super Admin') {
            abort(403, 'Unauthorized action: You do not have permissions to allocate tasks.');
        }

        // Get users who can perform tasks
        $performers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['BDO', 'Talathi', 'Gramsevak', 'System Administrator']);
        })->get();

        $talukas = Taluka::with('villages')->get();

        return view('tasks.create', compact('performers', 'talukas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'required|date|after_or_equal:today',
            'taluka' => 'nullable|string',
            'village' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $this->taskService->allocateTask($validated, Auth::id());

        return redirect()->route('tasks.index')->with('success', 'Task allocated successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,In Progress,Transfer,Completed,Overdue',
            'remarks' => 'nullable|string',
        ]);

        $this->taskService->updateTaskStatus($id, $validated['status'], $validated['remarks'] ?? '', Auth::id());

        return redirect()->back()->with('success', 'Task progress successfully updated!');
    }
}
