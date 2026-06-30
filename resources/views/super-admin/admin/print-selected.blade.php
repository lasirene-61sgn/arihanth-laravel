<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - Arihanth Jewellers</title>
</head>
<body style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 30px; color: #334155; background-color: #fff;">

    @foreach($admins as $index => $admin)
    <div style="max-width: 850px; margin: 0 auto 40px auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
        <div style="padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
            <div>
                <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 60px;">
                <h2 style="margin: 10px 0 0 0; font-size: 18px; color: #0f172a;">Arihanth Jewellers Pvt Ltd</h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Administrator Report</span><br>
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">System ID #{{ $index + 1 }}</span>
            </div>
        </div>

        <div style="padding: 30px;">
            <div style="margin-bottom: 25px; background: #1e293b; padding: 15px 20px; border-radius: 6px; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px; font-weight: 500;">
                    {{ $admin->full_name ?? 'N/A' }}
                </h1>
                @if($admin->designation)
                    <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">
                        {{ $admin->designation }}
                    </p>
                @endif
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                @if($admin->full_name)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px; width: 30%;">Full Name</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">{{ $admin->full_name }}</td>
                </tr>
                @endif

                @if($admin->email_id)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Email ID</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $admin->email_id }}</td>
                </tr>
                @endif

                @if($admin->mobile_no)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Mobile Number</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $admin->mobile_no }}</td>
                </tr>
                @endif

                @if($admin->designation)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Designation</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $admin->designation }}</td>
                </tr>
                @endif

                @if($admin->created_at)
                <tr>
                    <td style="padding: 12px 0; color: #94a3b8; font-size: 11px;">Created Date</td>
                    <td style="padding: 12px 0; color: #94a3b8; font-size: 11px;">
                        {{ $admin->created_at->format('d M Y, h:i A') }}
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <div style="background: #f8fafc; padding: 15px 25px; text-align: center; font-size: 10px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 2px;">
            Arihanth Jewellers Pvt Ltd - Official Admin Record
        </div>
    </div>

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
    @endforeach

</body>
</html>