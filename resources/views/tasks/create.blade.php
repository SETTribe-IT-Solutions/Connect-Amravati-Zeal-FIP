@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark">Allocate New Duty / Task Target</h2>
        <p class="text-secondary">Assign duties, select targeted division range, and dispatch alerts.</p>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold text-muted">Task Subject / Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Drought Relief Campaign" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold text-muted">Priority Classification</label>
                <select name="priority" class="form-select">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Task Description & Directives</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Detail the duties and deliverables clearly..." required></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold text-muted">Target Division / Taluka</label>
                <select name="taluka" id="createTaskTaluka" class="form-select" onchange="updateVillageDropdown(this.value)">
                    <option value="">Select Taluka...</option>
                    @foreach($talukas as $taluka)
                        <option value="{{ $taluka->name }}">{{ $taluka->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold text-muted">Target Village / Circle</label>
                <select name="village" id="createTaskVillage" class="form-select">
                    <option value="">Select Village...</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold text-muted">Assign To (Performer User)</label>
                <select name="assigned_to" class="form-select" required>
                    @foreach($performers as $perf)
                        <option value="{{ $perf->id }}">{{ $perf->name }} ({{ $perf->designation }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold text-muted">Target Completion Due Date</label>
                <input type="date" name="due_date" class="form-control" required value="{{ date('Y-m-d', strtotime('+7 days')) }}">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold text-muted">Attach Official Directives File (Guidelines)</label>
            <input type="file" name="attachment" class="form-control">
            <small class="text-muted">Supported formats: PDF, DOCX, XLS up to 10MB.</small>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-outline-secondary btn-sm px-4">Clear Details</button>
            <button type="submit" class="btn btn-primary btn-sm px-4">Allocate Target Task</button>
        </div>
    </form>
</div>

<script>
    // Dynamic Taluka to Village mapping engine
    const divisionData = {
        @foreach($talukas as $taluka)
            "{{ $taluka->name }}": [
                @foreach($taluka->villages as $village)
                    "{{ $village->name }}",
                @endforeach
            ],
        @endforeach
    };

    function updateVillageDropdown(talukaName) {
        const villageSelect = $('#createTaskVillage');
        villageSelect.html('<option value="">Select Village...</option>');
        
        if (divisionData[talukaName]) {
            divisionData[talukaName].forEach(village => {
                villageSelect.append(`<option value="${village}">${village}</option>`);
            });
        }
    }
</script>
@endsection
