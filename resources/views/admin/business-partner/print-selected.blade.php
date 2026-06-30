<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Buyers Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-info {
            margin-bottom: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            body {
                font-size: 10px;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Selected Buyers Report</h1>
        <p class="report-info">Generated on: {{ date('Y-m-d H:i:s') }}</p>
        <p class="report-info">Total Buyers: {{ count($buyers) }}</p>
    </div>

    @foreach($buyers as $index => $buyer)
        <h3>Buyer #{{ $index + 1 }}: {{ $buyer->business_name }}</h3>
        <table>
            <tr>
                <th width="30%">Field</th>
                <th width="70%">Value</th>
            </tr>
            <tr>
                <td><strong>BP Code</strong></td>
                <td>{{ $buyer->bp_code }}</td>
            </tr>
            <tr>
                <td><strong>Business Name</strong></td>
                <td>{{ $buyer->business_name }}</td>
            </tr>
            <tr>
                <td><strong>Contact Person</strong></td>
                <td>{{ $buyer->name }}</td>
            </tr>
            <tr>
                <td><strong>Mobile</strong></td>
                <td>{{ $buyer->mobile }}</td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td>{{ $buyer->email }}</td>
            </tr>
            <tr>
                <td><strong>Landline</strong></td>
                <td>{{ $buyer->landline ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Business Email</strong></td>
                <td>{{ $buyer->business_email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Note</strong></td>
                <td>{{ $buyer->note ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Address</strong></td>
                <td>
                    {{ $buyer->door_no ?? '' }} {{ $buyer->shop_no ?? '' }} {{ $buyer->complex_name ?? '' }} {{ $buyer->building_name ?? '' }},
                    {{ $buyer->street_name ?? '' }}, {{ $buyer->area ?? '' }},
                    {{ $buyer->city ?? 'N/A' }}, {{ $buyer->state ?? 'N/A' }} - {{ $buyer->pincode ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td><strong>Created At</strong></td>
                <td>{{ $buyer->created_at ?? 'N/A' }}</td>
            </tr>
        </table>
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
