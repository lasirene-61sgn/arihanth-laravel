<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Craftsmen Report</title>
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
        <h1>Selected Craftsmen Report</h1>
        <p class="report-info">Generated on: {{ date('Y-m-d H:i:s') }}</p>
        <p class="report-info">Total Craftsmen: {{ count($craftmen) }}</p>
    </div>

    @foreach($craftmen as $index => $craftman)
        <h3>Craftman #{{ $index + 1 }}: {{ $craftman->business_name }}</h3>
        <table>
            <tr>
                <th width="30%">Field</th>
                <th width="70%">Value</th>
            </tr>
            <tr>
                <td><strong>Craftman Code</strong></td>
                <td>{{ $craftman->craftman_code }}</td>
            </tr>
            <tr>
                <td><strong>Business Name</strong></td>
                <td>{{ $craftman->business_name }}</td>
            </tr>
            <tr>
                <td><strong>Contact Person</strong></td>
                <td>{{ $craftman->name }}</td>
            </tr>
            <tr>
                <td><strong>Mobile</strong></td>
                <td>{{ $craftman->mobile }}</td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td>{{ $craftman->email }}</td>
            </tr>
            <tr>
                <td><strong>City</strong></td>
                <td>{{ $craftman->city ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>State</strong></td>
                <td>{{ $craftman->state ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Note</strong></td>
                <td>{{ $craftman->note ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Address</strong></td>
                <td>
                    {{ $craftman->door_no ?? '' }} {{ $craftman->shop_no ?? '' }} {{ $craftman->complex_name ?? '' }} {{ $craftman->building_name ?? '' }},
                    {{ $craftman->street_name ?? '' }}, {{ $craftman->area ?? '' }},
                    {{ $craftman->city ?? 'N/A' }}, {{ $craftman->state ?? 'N/A' }} - {{ $craftman->pincode ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td><strong>Created At</strong></td>
                <td>{{ $craftman->created_at ?? 'N/A' }}</td>
            </tr>
        </table>
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
