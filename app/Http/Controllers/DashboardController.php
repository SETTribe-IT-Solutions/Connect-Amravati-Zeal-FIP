<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Appreciation;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $userRole = Auth::user()->roles->first()->name ?? 'Guest';
        $userType = Auth::user()->designation; // Assigner or Performer logic

        // Initialize counters
        $query = Task::query();

        // Performers see tasks assigned to them. Assigners see everything in their jurisdiction
        if (in_array($userRole, ['BDO', 'Talathi', 'Gramsevak', 'System Administrator'])) {
            $query->where('assigned_to', $userId);
        } else {
            // Assigners: Collector, Deputy Collector, SDO, Tehsildar see all tasks in district
            // Optionally filter by taluka if SDO/Tehsildar
            if ($userRole === 'SDO') {
                $query->whereHas('assignee', function($q) {
                    $q->where('taluka_id', Auth::user()->taluka_id);
                });
            }
        }

        $tasks = $query->get();
        
        $metrics = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'Completed')->count(),
            'progress' => $tasks->where('status', 'In Progress')->count(),
            'pending' => $tasks->where('status', 'Pending')->count(),
            'overdue' => $tasks->where('status', 'Overdue')->count(),
        ];

        // Chart Data compilation (Taluka-wise performance metrics for Chart.js)
        $chartData = Task::select('talukas.name as taluka', DB::raw('count(tasks.id) as total'))
            ->join('users', 'tasks.assigned_to', '=', 'users.id')
            ->join('talukas', 'users.taluka_id', '=', 'talukas.id')
            ->groupBy('talukas.name')
            ->get();

        $announcements = Announcement::latest()->take(5)->get();
        $appreciations = Appreciation::with(['recipient', 'sender'])->latest()->take(5)->get();

        return view('dashboard', compact('metrics', 'chartData', 'announcements', 'appreciations'));
    }
}
