@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark">District Administration Performance Dashboard</h2>
        <p class="text-secondary">Real-time indicators of task metrics, announcements, and employee appreciations.</p>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase small fw-bold">Total Assigned</span>
                    <h3 class="fw-bold mb-0 mt-1">{{ $metrics['total'] }}</h3>
                </div>
                <i class="fa-solid fa-folder-open text-primary fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase small fw-bold">Completed</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success">{{ $metrics['completed'] }}</h3>
                </div>
                <i class="fa-solid fa-circle-check text-success fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase small fw-bold">In Progress</span>
                    <h3 class="fw-bold mb-0 mt-1 text-info">{{ $metrics['progress'] }}</h3>
                </div>
                <i class="fa-solid fa-hourglass-half text-info fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-danger border-4 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase small fw-bold">Overdue</span>
                    <h3 class="fw-bold mb-0 mt-1 text-danger">{{ $metrics['overdue'] }}</h3>
                </div>
                <i class="fa-solid fa-triangle-exclamation text-danger fs-3"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart Visualizer -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 bg-white">
            <h5 class="fw-bold mb-4">Taluka-wise Task Status Distribution</h5>
            <canvas id="performanceChart" style="max-height: 320px;"></canvas>
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 bg-white mb-4">
            <h5 class="fw-bold mb-3">Notice Board & Announcements</h5>
            <div class="list-group list-group-flush">
                @forelse($announcements as $ann)
                    <div class="list-group-item px-0">
                        <strong class="text-primary">{{ $ann->title }}</strong>
                        <p class="small text-muted mb-0">{{ $ann->content }}</p>
                    </div>
                @empty
                    <p class="text-muted small">No announcements found</p>
                @endforelse
            </div>
        </div>
        
        <div class="card border-0 shadow-sm p-4 bg-white">
            <h5 class="fw-bold mb-3">Employee Appreciation Wall</h5>
            <div class="list-group list-group-flush">
                @forelse($appreciations as $app)
                    <div class="list-group-item px-0">
                        <strong>{{ $app->recipient->name }}</strong>
                        <p class="small text-muted mb-0">"{{ $app->message }}"</p>
                    </div>
                @empty
                    <p class="text-muted small">Appreciation Wall is empty</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    const labels = {!! json_encode($chartData->pluck('taluka')) !!};
    const totals = {!! json_encode($chartData->pluck('total')) !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Task Assignments count',
                data: totals,
                backgroundColor: 'rgba(10, 37, 64, 0.85)',
                borderColor: '#0a2540',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endsection
