<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Connect Amravati Task Ledger Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0a2540;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0a2540;
            margin: 0;
            font-size: 20px;
        }
        .header p {
            color: #c5a880;
            margin: 5px 0 0;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            color: #0a2540;
            font-weight: bold;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .high { background-color: #fee2e2; color: #ef4444; }
        .medium { background-color: #fef3c7; color: #d97706; }
        .low { background-color: #d1fae5; color: #059669; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #777;
            border-top: 1px dashed #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Connect Amravati</h1>
        <p>Official Government Task Allocation Ledger Report</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Task ID</th>
                <th>Title</th>
                <th>Assignee</th>
                <th>Assigner</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->task_number }}</td>
                    <td><strong>{{ $task->title }}</strong></td>
                    <td>{{ $task->assignee->name ?? 'N/A' }} ({{ $task->assignee->designation ?? 'N/A' }})</td>
                    <td>{{ $task->assigner->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ strtolower($task->priority) }}">{{ $task->priority }}</span>
                    </td>
                    <td>{{ $task->status }}</td>
                    <td>{{ $task->due_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated automatically via NIC Amravati. Compliant with Government Data Audits. © {{ date('Y') }} All Rights Reserved.
    </div>
</body>
</html>
