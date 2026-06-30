<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repairs Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            font-size: 8px;
        }
        .status {
            font-weight: bold;
            text-transform: capitalize;
        }
        .summary {
            margin-bottom: 15px;
            font-size: 12px;
        }
        .summary span {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Repairs Report</h1>
        <p>Generated on: {{ date('d M Y, H:i A') }}</p>
    </div>

    <div class="summary">
        <span>Total Records:</span> {{ count($repairs) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Date</th>
                <th>Buyer</th>
                <th>Product Name</th>
                <th>Weight</th>
                <th>Repair Details</th>
                <th>Status</th>
                <th>Craftsman</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repairs as $index => $repair)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>#{{ $repair->id }}</td>
                <td>{{ \Carbon\Carbon::parse($repair->repair_date)->format('d M Y') }}</td>
                <td>{{ $repair->buyer ? $repair->buyer->business_name : 'N/A' }}</td>
                <td>{{ $repair->product_name }}</td>
                <td>{{ $repair->weight ?? '0' }}</td>
                <td>{{ $repair->repair_details }}</td>
                <td>
                    <span class="status">
                        {{ str_replace('_', ' ', $repair->status) }}
                    </span>
                </td>
                <td>{{ $repair->craftsman ? $repair->craftsman->name : ($repair->allocated_craftsman_code ?? 'Not Allocated') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page <span class="pagenum"></span> | AJPL
    </div>
</body>
</html>
