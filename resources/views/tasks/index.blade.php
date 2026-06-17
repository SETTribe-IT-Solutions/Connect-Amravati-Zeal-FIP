@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark">Assigned Task Ledger & Enforcement Records</h2>
        <p class="text-secondary">Track statuses, search records, and execute progress reports.</p>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Task Title</th>
                    <th>Assignee</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td><strong>{{ $task->task_number }}</strong></td>
                        <td>
                            <div class="fw-bold">{{ $task->title }}</div>
                            @if(!empty($task->remarks))
                                <small class="text-muted italic">Remarks: {{ $task->remarks }}</small>
                            @endif
                        </td>
                        <td>{{ $task->assignee->name ?? 'N/A' }} ({{ $task->assignee->designation ?? 'N/A' }})</td>
                        <td>
                            @php
                                $p = strtolower($task->priority);
                                $p_cls = ($p === 'critical' || $p === 'high') ? 'danger' : (($p === 'medium') ? 'warning' : 'success');
                            @endphp
                            <span class="badge bg-{{ $p_cls }} text-uppercase">{{ $task->priority }}</span>
                        </td>
                        <td>
                            @php
                                $s = strtolower($task->status);
                                $s_cls = ($s === 'completed') ? 'success' : (($s === 'in progress') ? 'info' : (($s === 'overdue') ? 'danger' : 'warning'));
                            @endphp
                            <span class="badge bg-{{ $s_cls }} text-uppercase">{{ $task->status }}</span>
                        </td>
                        <td>{{ $task->due_date }}</td>
                        <td>
                            @if(Auth::user()->roles->first()->name ?? '' === 'BDO' || Auth::user()->roles->first()->name ?? '' === 'Talathi' || Auth::user()->roles->first()->name ?? '' === 'Gramsevak' || Auth::user()->roles->first()->name ?? '' === 'System Administrator')
                                <!-- Task Performers: update progress button -->
                                <button class="btn btn-sm btn-outline-primary" onclick="openUpdateModal('{{ $task->id }}', '{{ $task->status }}', '{{ $task->remarks }}')">
                                    Update Status
                                </button>
                            @else
                                <!-- Task Assigners: audit log details -->
                                <button class="btn btn-sm btn-outline-secondary" onclick="alert('Displaying secure NIC transaction logs for Task {{ $task->task_number }}...')">
                                    Audit Logs
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No task records found in database</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination Links -->
        <div class="mt-3">
            {{ $tasks->links() }}
        </div>
    </div>
</div>

<!-- Performers Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-top border-5 border-primary">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Update Task Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Current Status</label>
                        <select name="status" id="taskStatusSelect" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Work remarks / Logs</label>
                        <textarea name="remarks" id="taskRemarksArea" class="form-select" rows="3" placeholder="Enter comments or progress logs..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openUpdateModal(taskId, status, remarks) {
        $('#statusForm').attr('action', '/tasks/' + taskId + '/status');
        $('#taskStatusSelect').val(status);
        $('#taskRemarksArea').val(remarks);
        
        const myModal = new bootstrap.Modal(document.getElementById('statusModal'));
        myModal.show();
    }
</script>
@endsection
