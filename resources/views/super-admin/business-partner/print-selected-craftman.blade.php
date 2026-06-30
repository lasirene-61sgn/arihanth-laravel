<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Craftsmen Profile - Arihanth Jewellers</title>
</head>
<body style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 30px; color: #334155; background-color: #fff;">

    @foreach($craftmen as $index => $craftman)
    <div style="max-width: 850px; margin: 0 auto 40px auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
        <div style="padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
            <div>
                <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 60px;">
                <h2 style="margin: 10px 0 0 0; font-size: 18px; color: #0f172a;">Arihanth Jewellers Pvt Ltd</h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Craftsman Profile</span><br>
                <span style="font-size: 14px; font-weight: 600; color: #64748b;">Record #{{ $index + 1 }}</span>
            </div>
        </div>

        <div style="padding: 30px;">
            <div style="margin-bottom: 25px;">
                <h1 style="margin: 0; font-size: 24px; color: #1e293b; border-left: 4px solid #1e293b; padding-left: 15px;">
                    {{ $craftman->business_name ?? 'Unnamed Business' }}
                </h1>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                @if($craftman->craftman_code)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px; width: 30%;">Craftsman Code</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px; font-weight: 600;">{{ $craftman->craftman_code }}</td>
                </tr>
                @endif

                @if($craftman->name)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Contact Person</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $craftman->name }}</td>
                </tr>
                @endif

                @if($craftman->mobile)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Mobile Number</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $craftman->mobile }}</td>
                </tr>
                @endif

                @if($craftman->email)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Email Address</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">{{ $craftman->email }}</td>
                </tr>
                @endif

                @if($craftman->city || $craftman->state)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px;">Location</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 13px;">
                        {{ $craftman->city }}{{ $craftman->city && $craftman->state ? ', ' : '' }}{{ $craftman->state }}
                    </td>
                </tr>
                @endif

                @if($craftman->note)
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 13px; vertical-align: top;">Special Notes</td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 13px; font-style: italic; line-height: 1.5;">
                        "{{ $craftman->note }}"
                    </td>
                </tr>
                @endif

                @if($craftman->created_at)
                <tr>
                    <td style="padding: 12px 0; color: #94a3b8; font-size: 11px;">Registration Date</td>
                    <td style="padding: 12px 0; color: #94a3b8; font-size: 11px;">{{ $craftman->created_at }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div style="background: #f8fafc; padding: 15px 25px; text-align: center; font-size: 10px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 2px;">
            Arihanth Jewellers Pvt Ltd - Internal ERP Document
        </div>
    </div>

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
    @endforeach

</body>
</html>