<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Key User Profile - Arihanth Jewellers</title>
</head>
<body style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 30px; color: #334155; background-color: #fff;">

    @foreach($keyUsers as $index => $keyUser)
    <div style="max-width: 850px; margin: 0 auto 40px auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
        <div style="padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
            <div>
                <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 60px;">
                <h2 style="margin: 10px 0 0 0; font-size: 18px; color: #0f172a;">Arihanth Jewellers Pvt Ltd</h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Key User Report</span><br>
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">User #{{ $index + 1 }}</span>
            </div>
        </div>

        <div style="padding: 30px;">
            <div style="margin-bottom: 25px; background: #1e293b; padding: 15px 20px; border-radius: 6px; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px; font-weight: 500;">
                    {{ $keyUser->full_name ?? 'N/A' }}
                </h1>
                @if($keyUser->user_code)
                    <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">
                        User Code: {{ $keyUser->user_code }}
                    </p>
                @endif
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                @if($keyUser->full_name)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px; width: 30%;">Full Name</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">{{ $keyUser->full_name }}</td>
                </tr>
                @endif

                @if($keyUser->email_id)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Email Address</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $keyUser->email_id }}</td>
                </tr>
                @endif

                @if($keyUser->mobile_no)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Mobile Number</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $keyUser->mobile_no }}</td>
                </tr>
                @endif

                @if($keyUser->city)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">City</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $keyUser->city }}</td>
                </tr>
                @endif

                @if($keyUser->aadhar_no)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Aadhar No</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">[Aadhaar Redacted]</td>
                </tr>
                @endif

                @if($keyUser->status)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Account Status</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">
                        <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">{{ ucfirst($keyUser->status) }}</span>
                    </td>
                </tr>
                @endif

                @if($keyUser->created_at)
                <tr>
                    <td style="padding: 12px 0; color: #94a3b8; font-size: 11px;">Created Date</td>
                    <td style="padding: 12px 0; color: #94a3b8; font-size: 11px;">
                        {{ $keyUser->created_at->format('d M Y, h:i A') }}
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <div style="background: #f8fafc; padding: 15px 25px; text-align: center; font-size: 10px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 2px;">
            Arihanth Jewellers Pvt Ltd - Confidential User Record
        </div>
    </div>

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
    @endforeach

</body>
</html>