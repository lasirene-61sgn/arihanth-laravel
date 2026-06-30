<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Key Users Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .report-info { margin-bottom: 10px; }
        .page-break { page-break-after: always; }
        @media print {
            body { font-size: 10px; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Selected Key Users Report</h1>
        <p class="report-info">Generated on: {{ date('Y-m-d H:i:s') }}</p>
        <p class="report-info">Total Key Users: {{ count($keyUsers) }}</p>
    </div>

    @foreach($keyUsers as $index => $keyUser)
        <h3>Key User #{{ $index + 1 }}: {{ $keyUser->full_name }}</h3>
        <table>
            <tr>
                <th width="30%">Field</th>
                <th width="70%">Value</th>
            </tr>
            <tr>
                <td><strong>User Code</strong></td>
                <td>{{ $keyUser->user_code }}</td>
            </tr>
            <tr>
                <td><strong>Full Name</strong></td>
                <td>{{ $keyUser->full_name }}</td>
            </tr>
            <tr>
                <td><strong>Email ID</strong></td>
                <td>{{ $keyUser->email_id }}</td>
            </tr>
            <tr>
                <td><strong>Mobile No</strong></td>
                <td>{{ $keyUser->mobile_no }}</td>
            </tr>
            <tr>
                <td><strong>City</strong></td>
                <td>{{ $keyUser->city }}</td>
            </tr>
            <tr>
                <td><strong>Aadhar No</strong></td>
                <td>{{ $keyUser->aadhar_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>{{ ucfirst($keyUser->status) }}</td>
            </tr>
            <tr>
                <td><strong>Created At</strong></td>
                <td>{{ $keyUser->created_at->format('d M Y, H:i') }}</td>
            </tr>
        </table>
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
