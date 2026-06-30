<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Purchase Orders - Admin</title>
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            .page-break { page-break-after: always; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: white !important; }
            .no-print { display: none !important; }
            img { image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f1f5f9; color: #1e293b; }

        .print-btn-bar {
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 16px 0;
        }
        .print-btn-bar button {
            padding: 10px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: none;
        }
        .btn-print { background: #0f172a; color: white; }
        .btn-close-win { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1 !important; }

        /* ── PO Card ── */
        .po-card {
            max-width: 900px;
            margin: 0 auto 30px auto;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 15px rgba(0,0,0,0.08);
        }

        /* ── Header ── */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 24px 30px;
            background: #f8fafc;
            border-bottom: 4px solid #0f172a;
        }
        .brand-name { font-weight: 800; font-size: 17px; color: #0f172a; margin-bottom: 6px; }
        .company-meta { font-size: 10.5px; color: #475569; line-height: 1.65; }
        .company-meta span { margin-right: 12px; }
        .company-meta strong { color: #1e293b; }

        /* ── Dark Banner ── */
        .po-banner {
            background: #0f172a;
            color: #fff;
            padding: 14px 26px;
            margin: 22px 30px;
            border-radius: 7px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .po-banner small { opacity: 0.65; font-size: 10px; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .po-banner .val { font-size: 15px; font-weight: 700; }
        .po-banner .val-due { font-size: 15px; font-weight: 700; color: #fda4af; }

        /* ── Craftsman Box ── */
        .craftsman-box {
            margin: 0 30px 20px;
            padding: 14px 18px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 8px;
        }
        .craftsman-box-title {
            font-size: 10px; font-weight: 800; color: #15803d;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;
        }
        .craftsman-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px;
            font-size: 11.5px;
        }
        .craftsman-grid .lbl { color: #64748b; font-weight: 600; }
        .craftsman-grid .vl  { font-weight: 700; color: #0f172a; }
        .craftsman-address {
            margin-top: 8px; font-size: 11px; color: #334155; line-height: 1.55;
            border-top: 1px dashed #bbf7d0; padding-top: 8px;
        }

        /* ── Items Table ── */
        .content-area { padding: 0 30px 28px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th {
            background: #f8fafc; color: #64748b; text-transform: uppercase;
            font-size: 10.5px; letter-spacing: 1px; padding: 11px 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        table td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 13px; }
        table tfoot td { padding: 14px 12px; background: #f8fafc; }

        .img-box { border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 4px; background: #fff; display: inline-block; }
        .item-img { max-height: 100px; max-width: 100px; object-fit: contain; image-rendering: -webkit-optimize-contrast; }
        .no-img { height: 90px; width: 90px; display: flex; align-items: center; justify-content: center; background: #f8fafc; color: #cbd5e1; font-size: 10px; }

        /* ── Notes ── */
        .notes-box { margin-top: 22px; padding: 14px; border-left: 5px solid #0f172a; background: #f8fafc; }
        .notes-box .notes-label { font-size: 10px; font-weight: 800; color: #0f172a; text-transform: uppercase; }
        .notes-box p { margin-top: 5px; font-size: 13px; color: #334155; line-height: 1.5; }

        /* ── Signatures ── */
        .sig-row { margin-top: 55px; display: flex; justify-content: space-between; padding: 0 40px; }
        .sig-box {
            border-top: 2px solid #0f172a; width: 175px; text-align: center;
            padding-top: 8px; font-size: 11px; font-weight: 800;
            text-transform: uppercase; color: #1e293b;
        }

        /* ── Footer bar ── */
        .footer-bar {
            background: #0f172a; color: #94a3b8; text-align: center;
            font-size: 10px; padding: 10px; text-transform: uppercase; letter-spacing: 2px;
        }
    </style>
</head>
<body>

<div class="print-btn-bar no-print">
    <button class="btn-print" onclick="window.print()">🖨 PRINT ALL PURCHASE ORDERS</button>
    <button class="btn-close-win" onclick="window.close()">CLOSE</button>
</div>

@foreach($ordersWithDetails as $index => $data)
@php
    $po        = $data['order'];
    $items     = $data['items'];
    $craftsman = $data['craftsman'] ?? null;

    // Build craftsman full address
    $craftsmanAddress = '';
    if ($craftsman) {
        $addrParts = array_filter([
            $craftsman->door_no,
            $craftsman->shop_no,
            $craftsman->complex_name,
            $craftsman->building_name,
            $craftsman->street_name,
            $craftsman->area,
            $craftsman->city,
            $craftsman->state,
            $craftsman->pincode,
        ]);
        $craftsmanAddress = implode(', ', $addrParts);
    }
@endphp

<div class="po-card {{ !$loop->last ? 'page-break' : '' }}">

    {{-- ── HEADER ── --}}
    <div class="header-section">
        <div>
            <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 55px; margin-bottom: 6px;">
            <div class="brand-name">ARIHANTH JEWELLERS PVT LTD</div>
            <div class="company-meta">
                @if(!empty($company['address']))
                    <div>{{ $company['address'] }}</div>
                @endif
                <div style="margin-top:3px;">
                    @if(!empty($company['mobile']))
                        <span><strong>Mob:</strong> {{ $company['mobile'] }}</span>
                    @endif
                    @if(!empty($company['email']))
                        <span><strong>Email:</strong> {{ $company['email'] }}</span>
                    @endif
                </div>
                <div style="margin-top:3px;">
                    @if(!empty($company['gst']))
                        <span><strong>GST:</strong> {{ $company['gst'] }}</span>
                    @endif
                    @if(!empty($company['cin']))
                        <span><strong>CIN:</strong> {{ $company['cin'] }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">Purchase Order #</div>
            <div style="font-size:26px;font-weight:900;color:#0f172a;line-height:1;">{{ $po->purchase_order_code }}</div>
            <div style="font-size:12px;margin-top:5px;color:#475569;">Issued: <strong>{{ $po->created_at->format('d M Y') }}</strong></div>
        </div>
    </div>

    {{-- ── DARK BANNER ── --}}
    <div class="po-banner">
        <div>
            <small>Allocated Craftsman</small>
            <div class="val">{{ $po->allocated_craftsman_code ?: 'PENDING ALLOCATION' }}</div>
        </div>
        <div style="text-align:right;">
            <small>Production Due Date</small>
            <div class="val-due">{{ $po->due_date ? $po->due_date->format('d M, Y') : 'N/A' }}</div>
        </div>
    </div>

    {{-- ── CRAFTSMAN DETAILS ── --}}
    @if($craftsman)
    <div class="craftsman-box">
        <div class="craftsman-box-title">🔨 Craftsman Details</div>
        <div class="craftsman-grid">
            <div><span class="lbl">Name: </span><span class="vl">{{ $craftsman->business_name ?: 'N/A' }}</span></div>
            <!-- <div><span class="lbl">Contact Name: </span><span class="vl">{{ $craftsman->name ?: 'N/A' }}</span></div> -->
            <div><span class="lbl">Mobile: </span><span class="vl">{{ $craftsman->mobile ?: 'N/A' }}</span></div>
            <!-- <div><span class="lbl">Email: </span><span class="vl">{{ $craftsman->email ?: ($craftsman->business_email ?: 'N/A') }}</span></div> -->
            <div><span class="lbl">GST No: </span><span class="vl">{{ $craftsman->gst_no ?: 'N/A' }}</span></div>
            <div><span class="lbl">CIN No: </span><span class="vl">{{ $craftsman->cin_no ?: 'N/A' }}</span></div>
        </div>
        @if(!empty($craftsmanAddress))
        <div class="craftsman-address">
            <strong style="color:#15803d;">Address:</strong> {{ $craftsmanAddress }}
        </div>
        @endif
    </div>
    @endif
<div class="content-area">
    <h3 class="brand-name">Kindly  deliver the ordered items within the specified {{ $po->due_date ? $po->due_date->format('d M, Y') : 'N/A' }}. 
        Please carry a printed copy of this purchase order while delivering the goods.</h3>
</div>
    {{-- ── ITEMS TABLE ── --}}
    <div class="content-area">
        <table>
            <thead>
                <tr>
                    <th style="width:46px;">#</th>
                    <th style="width:130px;">Visual Ref</th>
                    <th>Product Details</th>
                    <th style="width:145px;">Grams Breakdown</th>
                    <th style="width:95px;text-align:right;">Total Wt.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                <tr>
                    <td style="color:#94a3b8;font-weight:600;">{{ $idx + 1 }}</td>
                    <td>
                        <div class="img-box">
                            @php
                                $imgPath = !empty($item['image']) ? $item['image'] : null;
                                $imgSrc  = null;

                                if ($imgPath) {
                                    $imgSrc = str_contains($imgPath, 'images/') ? asset($imgPath) : asset('storage/' . $imgPath);
                                } else {
                                    if(isset($item['design']) && !empty($item['design']->image)) {
                                        $path = $item['design']->image;
                                        $imgSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                    } elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0) {
                                        $path = $item['product']->images[0]->path;
                                        $imgSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                    }
                                }
                            @endphp
                            @if($imgSrc)
                                <img src="{{ $imgSrc }}" class="item-img" alt="Design">
                            @else
                                <div class="no-img">NO IMAGE</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:700;color:#0f172a;font-size:14px;">{{ $item['product'] ? $item['product']->product_name : 'Unknown Product' }}</div>
                        <div style="font-size:11px;color:#64748b;margin-top:3px;">
                            <strong>Code:</strong> {{ $item['design'] ? $item['design']->design_code : ($item['product'] ? $item['product']->design_code : 'N/A') }}<br>
                            <strong>Cat:</strong> {{ $item['category_name'] ?? 'N/A' }}
                        </div>
                        @if(!empty($item['item_notes']))
                        <div style="margin-top:8px;font-size:11px;padding:4px 8px;background:#fffbeb;border-left:3px solid #f59e0b;color:#92400e;">
                            {{ is_array($item['item_notes']) ? implode(', ', $item['item_notes']) : $item['item_notes'] }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if(isset($item['grams']) && is_array($item['grams']))
                            <div style="font-size:12px;line-height:1.5;">
                                @foreach($item['grams'] as $k => $gram)
                                    <div><span style="color:#64748b;">{{ $gram }}g</span> <strong>x{{ $item['quantity'][$k] }}</strong></div>
                                @endforeach
                            </div>
                        @else
                            <span style="color:#94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:800;font-size:15px;color:#0f172a;">
                        {{ isset($item['total']) ? number_format($item['total'], 2) : '0.00' }}g
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;font-weight:800;text-transform:uppercase;font-size:11px;letter-spacing:1px;">Gross Order Weight:</td>
                    <td style="text-align:right;font-size:18px;font-weight:900;color:#e11d48;">
                        {{ number_format(collect($items)->sum('total'), 2) }}g
                    </td>
                </tr>
            </tfoot>
        </table>

        @if($po->notes)
        <div class="notes-box">
            <span class="notes-label">Special Order Notes:</span>
            <p>{{ $po->notes }}</p>
        </div>
        @endif

        <div class="sig-row">
            <div class="sig-box">ISSUED BY</div>
            <div class="sig-box">APPROVED BY</div>
        </div>
    </div>

    <div class="footer-bar">
        Arihanth Jewellers • Production Purchase Order • Page {{ $index + 1 }} of {{ count($ordersWithDetails) }}
    </div>
</div>
@endforeach

<script>
    window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('auto_print')) {
            setTimeout(() => { window.print(); }, 600);
        }
    };
</script>
</body>
</html>