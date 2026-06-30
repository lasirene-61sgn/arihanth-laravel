<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Report - Arihanth Jewellers</title>
</head>
<body style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 30px; color: #334155;">

    @foreach($buyers as $index => $buyer)
    <div style="max-width: 850px; margin: 0 auto 40px auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff;">
        
        <div style="padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 60px;">
                <h2 style="margin: 10px 0 0 0; font-size: 18px; color: #0f172a;">Arihanth Jewellers Pvt Ltd</h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Selected Buyer Report</span><br>
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">Buyer #{{ $index + 1 }}</span>
            </div>
        </div>

        <div style="padding: 25px;">
            <table style="width: 100%; border-collapse: collapse;">
                @if($buyer->bp_code)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px; width: 30%;">BP Code</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px; font-weight: 600;">{{ $buyer->bp_code }}</td>
                </tr>
                @endif

                @if($buyer->business_name)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Business Name</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px; font-weight: 600;">{{ $buyer->business_name }}</td>
                </tr>
                @endif

                @if($buyer->name)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Contact Person</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->name }}</td>
                </tr>
                @endif

                @if($buyer->mobile)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Mobile</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->mobile }}</td>
                </tr>
                @endif

                @if($buyer->email)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Email</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->email }}</td>
                </tr>
                @endif

                @if($buyer->landline)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Landline</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->landline }}</td>
                </tr>
                @endif

                @if($buyer->business_email)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Business Email</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->business_email }}</td>
                </tr>
                @endif

                @if($buyer->refered_by)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Referred By</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->refered_by }}</td>
                </tr>
                @endif

                @if($buyer->more)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">More Info</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->more }}</td>
                </tr>
                @endif

                @if($buyer->city || $buyer->state)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px; vertical-align: top;">Address</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px; line-height: 1.5;">
                        {{ $buyer->door_no }} {{ $buyer->shop_no }} {{ $buyer->complex_name }} {{ $buyer->building_name }}<br>
                        {{ $buyer->street_name }}, {{ $buyer->area }}<br>
                        {{ $buyer->city }}, {{ $buyer->state }} - {{ $buyer->pincode }}
                    </td>
                </tr>
                @endif

                @if($buyer->map_location)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Map Location</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->map_location }}</td>
                </tr>
                @endif

                @if($buyer->location_guide)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Location Guide</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px;">{{ $buyer->location_guide }}</td>
                </tr>
                @endif

                @if($buyer->bis_no || $buyer->gst_no || $buyer->pan_no || $buyer->aadhar_no)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px; vertical-align: top;">KYC Details</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px; line-height: 1.6;">
                        @if($buyer->bis_no) <strong>BIS No:</strong> {{ $buyer->bis_no }}<br> @endif
                        @if($buyer->gst_no) <strong>GST No:</strong> {{ $buyer->gst_no }}<br> @endif
                        @if($buyer->msme_no) <strong>MSME No:</strong> {{ $buyer->msme_no }}<br> @endif
                        @if($buyer->pan_no) <strong>PAN No:</strong> {{ $buyer->pan_no }}<br> @endif
                        @if($buyer->tan_no) <strong>TAN No:</strong> {{ $buyer->tan_no }}<br> @endif
                        @if($buyer->cin_no) <strong>CIN No:</strong> {{ $buyer->cin_no }}<br> @endif
                        @if($buyer->aadhar_no) <strong>Aadhar No:</strong> {{ $buyer->aadhar_no }}<br> @endif
                        @if($buyer->aadhar_name) <strong>Aadhar Name:</strong> {{ $buyer->aadhar_name }} @endif
                    </td>
                </tr>
                @endif

                @if($buyer->bank_name || $buyer->account_no)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px; vertical-align: top;">Bank Details</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px; line-height: 1.6;">
                        @if($buyer->bank_name) <strong>Bank:</strong> {{ $buyer->bank_name }}<br> @endif
                        @if($buyer->account_name) <strong>Account:</strong> {{ $buyer->account_name }}<br> @endif
                        @if($buyer->account_no) <strong>A/C No:</strong> {{ $buyer->account_no }}<br> @endif
                        @if($buyer->ifsc_code) <strong>IFSC:</strong> {{ $buyer->ifsc_code }}<br> @endif
                        @if($buyer->branch) <strong>Branch:</strong> {{ $buyer->branch }} ({{ $buyer->bank_city }}) @endif
                    </td>
                </tr>
                @endif

                @if($buyer->note)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #64748b; font-size: 13px;">Note</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f8fafc; color: #1e293b; font-size: 13px; font-style: italic;">{{ $buyer->note }}</td>
                </tr>
                @endif

                @if($buyer->created_at)
                <tr>
                    <td style="padding: 10px 0; color: #94a3b8; font-size: 11px;">System Date</td>
                    <td style="padding: 10px 0; color: #94a3b8; font-size: 11px;">{{ $buyer->created_at }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div style="background: #f8fafc; padding: 15px 25px; text-align: center; font-size: 10px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px;">
            Arihanth Jewellers Pvt Ltd - Confidential Report
        </div>
    </div>

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
    @endforeach

</body>
</html>