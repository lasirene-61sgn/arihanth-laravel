<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Buyers Report</title>
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
        <h1>All Buyers Report</h1>
        <p class="report-info">Generated on: {{ date('Y-m-d H:i:s') }}</p>
        <p class="report-info">Total Buyers: {{ count($buyers) }}</p>
    </div>

    @if(count($buyers) > 0)
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
                    <td><strong>Referred By</strong></td>
                    <td>{{ $buyer->refered_by ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>More Info</strong></td>
                    <td>{{ $buyer->more ?? 'N/A' }}</td>
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
                    <td><strong>Map Location</strong></td>
                    <td>{{ $buyer->map_location ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Location Guide</strong></td>
                    <td>{{ $buyer->location_guide ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>KYC Details</strong></td>
                    <td>
                        <strong>BIS No:</strong> {{ $buyer->bis_no ?? 'N/A' }}<br>
                        <strong>GST No:</strong> {{ $buyer->gst_no ?? 'N/A' }}<br>
                        <strong>MSME No:</strong> {{ $buyer->msme_no ?? 'N/A' }}<br>
                        <strong>PAN No:</strong> {{ $buyer->pan_no ?? 'N/A' }}<br>
                        <strong>TAN No:</strong> {{ $buyer->tan_no ?? 'N/A' }}<br>
                        <strong>CIN No:</strong> {{ $buyer->cin_no ?? 'N/A' }}<br>
                        <strong>Aadhar No:</strong> {{ $buyer->aadhar_no ?? 'N/A' }}<br>
                        <strong>Aadhar Name:</strong> {{ $buyer->aadhar_name ?? 'N/A' }}<br>
                    </td>
                </tr>
                <tr>
                    <td><strong>Bank Details</strong></td>
                    <td>
                        <strong>Bank Name:</strong> {{ $buyer->bank_name ?? 'N/A' }}<br>
                        <strong>Account Name:</strong> {{ $buyer->account_name ?? 'N/A' }}<br>
                        <strong>Account No:</strong> {{ $buyer->account_no ?? 'N/A' }}<br>
                        <strong>IFSC Code:</strong> {{ $buyer->ifsc_code ?? 'N/A' }}<br>
                        <strong>Branch:</strong> {{ $buyer->branch ?? 'N/A' }}<br>
                        <strong>Bank City:</strong> {{ $buyer->bank_city ?? 'N/A' }}<br>
                        <strong>Bank State:</strong> {{ $buyer->bank_state ?? 'N/A' }}<br>
                    </td>
                </tr>
                <tr>
                    <td><strong>Note</strong></td>
                    <td>{{ $buyer->note ?? 'N/A' }}</td>
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
    @else
        <p>No buyers found.</p>
    @endif
</body>
</html>