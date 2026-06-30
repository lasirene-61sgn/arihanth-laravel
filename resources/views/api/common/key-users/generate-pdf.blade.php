<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key Users Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
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
            font-size: 10px;
            color: #999;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            font-size: 10px;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .summary {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .summary span {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Key Users Report</h1>
        <p>Generated on: {{ date('d M Y, H:i A') }}</p>
    </div>

    <div class="summary">
        <span>Total Records:</span> {{ count($keyUsers) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User Code</th>
                <th>Full Name</th>
                <th>Email ID</th>
                <th>Mobile No</th>
                <th>BP Code</th>
                <th>City</th>
                <th>Status</th>
                <th>Joined Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($keyUsers as $index => $user)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $user->user_code }}</td>
                <td>{{ $user->full_name }}</td>
                <td>{{ $user->email_id }}</td>
                <td>{{ $user->mobile_no }}</td>
                <td>{{ $user->bp_code }} {{ $user->buyer->business_name ?? '' }}</td>
                <td>{{ $user->city ?? 'N/A' }}</td>
                <td>
                    <span class="{{ $user->status == 1 ? 'status-active' : 'status-inactive' }}">
                        {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>{{ $user->created_at ? $user->created_at->format('d M Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page <span class="pagenum"></span> | AJPL {{ date('d,M Y') }}
    </div>
</body>
</html>
