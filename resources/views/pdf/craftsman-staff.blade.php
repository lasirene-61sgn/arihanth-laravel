<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Craftsman Staff Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            color: #111;
        }
        .date {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-active {
            background-color: #e2fcd6;
            color: #2b7a0b;
        }
        .badge-inactive {
            background-color: #fde8e8;
            color: #9b1c1c;
        }
    </style>
</head>
<body>

    <h2>Craftsman Staff Report</h2>
    <div class="date">Generated on: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Staff Code</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Craftsman</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffs as $staff)
                <tr>
                    <td>{{ $staff->id }}</td>
                    <td>{{ $staff->staff_code ?? 'N/A' }}</td>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->mobile }}</td>
                    <td>{{ $staff->craftsman->name ?? 'N/A' }}</td>
                    <td>
                        @if($staff->is_active == 1)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $staff->created_at ? $staff->created_at->format('Y-m-d') : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #777;">No staff records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>