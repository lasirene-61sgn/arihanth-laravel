<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Craftsmen Report</title>
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
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
            font-weight: bold;
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
        <h1>Craftsmen Report</h1>
        <p>Generated on: {{ date('d M Y, H:i A') }}</p>
    </div>

    <div class="summary">
        <span>Total Records:</span> {{ count($craftsmen) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Craftsman Code</th>
                <th>Business Name</th>
                <th>Name</th>
                <th>GST No</th>
                <th>BIS No</th>
                <th>MSME No</th>
                <th>CIN No</th>
                <th>TAN No</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>City</th>
                <th>State</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($craftsmen as $index => $craftsman)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $craftsman->craftman_code }}</td>
                <td>{{ $craftsman->business_name }}</td>
                <td>{{ $craftsman->name }}</td>
                <td>{{ $craftsman->gst_no ?? 'N/A' }}</td>
                <td>{{ $craftsman->bis_no ?? 'N/A' }}</td>
                <td>{{ $craftsman->msme_no ?? 'N/A' }}</td>
                <td>{{ $craftsman->cin_no ?? 'N/A' }}</td>
                <td>{{ $craftsman->tan_no ?? 'N/A' }}</td>
                <td>{{ $craftsman->mobile }}</td>
                <td>{{ $craftsman->email }}</td>
                <td>{{ $craftsman->city ?? 'N/A' }}</td>
                <td>{{ $craftsman->state ?? 'N/A' }}</td>
                <td>
                    <span class="{{ $craftsman->status == 1 ? 'status-active' : 'status-inactive' }}">
                        {{ $craftsman->status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page <span class="pagenum"></span> | Lasirene ERP System
    </div>
</body>
</html>
