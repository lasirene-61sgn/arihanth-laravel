@extends('super-admin.layouts.print')

@section('title', 'Bulk Print Purchase Orders')

@section('styles')
<style>
    @media print {
        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
        body { -webkit-print-color-adjust: exact; background-color: white !important; }
        .no-print { display: none !important; }
        img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
    }

    body {
        background-color: #f1f5f9;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: #1e293b;
    }

    .po-container {
        max-width: 900px;
        margin: 20px auto;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* ── Header: Company on left, PO# on right ── */
    .header-section {
        padding: 24px 30px;
        background-color: #f8fafc;
        border-bottom: 4px solid #0f172a;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .company-block { max-width: 340px; }
    .brand-name { font-weight: 800; font-size: 17px; color: #0f172a; letter-spacing: -0.5px; margin-bottom: 6px; }
    .company-meta { font-size: 10.5px; color: #475569; line-height: 1.6; }
    .company-meta span { margin-right: 12px; }
    .company-meta strong { color: #1e293b; }

    /* ── PO Banner (dark bar) ── */
    .po-banner {
        background: #0f172a;
        color: #ffffff;
        padding: 15px 25px;
        margin: 25px 30px;
        border-radius: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* ── Craftsman Details Box ── */
    .craftsman-box {
        margin: 0 30px 20px 30px;
        padding: 14px 18px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 4px solid #16a34a;
        border-radius: 8px;
    }
    .craftsman-box-title {
        font-size: 10px;
        font-weight: 800;
        color: #15803d;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    .craftsman-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 20px;
        font-size: 11.5px;
        color: #1e293b;
    }
    .craftsman-grid .label { color: #64748b; font-weight: 600; }
    .craftsman-grid .val { font-weight: 700; color: #0f172a; }
    .craftsman-address {
        margin-top: 8px;
        font-size: 11px;
        color: #334155;
        line-height: 1.55;
        border-top: 1px dashed #bbf7d0;
        padding-top: 8px;
    }

    .content-area { padding: 0 30px 30px 30px; }

    .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .table th {
        background-color: #f8fafc !important;
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        padding: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    .table td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 13px;
    }

    .product-img-box {
        border: 1.5px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px;
        background: #fff;
        display: inline-block;
    }

    .item-image {
        max-height: 110px;
        max-width: 110px;
        object-fit: contain;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    .footer-bar {
        background: #0f172a;
        color: #94a3b8;
        text-align: center;
        font-size: 10px;
        padding: 10px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .sig-box {
        border-top: 2px solid #0f172a;
        width: 180px;
        text-align: center;
        padding-top: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #1e293b;
    }
</style>
@endsection

@section('content')
<div class="row no-print mb-4 mt-2">
    <div class="col-12 text-center">
        <button onclick="window.print()" class="btn btn-dark btn-lg shadow-sm"><i class="bi bi-printer"></i> PRINT ALL PURCHASE ORDERS</button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-lg shadow-sm">CLOSE</button>
    </div>
</div>

@foreach($ordersWithDetails as $index => $data)
@php
    $po        = $data['order'];
    $items     = $data['items'];
    $craftsman = $data['craftsman'] ?? null;

    // Build craftsman full address string
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

<div class="po-container {{ !$loop->last ? 'page-break' : '' }}">

    {{-- ─── HEADER: Company Logo + Contact | PO Code ─── --}}
    <div class="header-section">
        <div class="company-block">
            <img src="{{ asset('images/ajlogo.png') }}" alt="Logo" style="height: 55px; margin-bottom: 6px;">
            <div class="brand-name">ARIHANTH JEWELLERS PVT LTD</div>
            <div class="company-meta">
                @if(!empty($company['address']))
                    <div>{{ $company['address'] }}</div>
                @endif
                <div style="margin-top: 3px;">
                    @if(!empty($company['mobile']))
                        <span><strong>Mob:</strong> {{ $company['mobile'] }}</span>
                    @endif
                    @if(!empty($company['email']))
                        <span><strong>Email:</strong> {{ $company['email'] }}</span>
                    @endif
                </div>
                <div style="margin-top: 3px;">
                    @if(!empty($company['gst']))
                        <span><strong>GST:</strong> {{ $company['gst'] }}</span>
                    @endif
                    @if(!empty($company['cin']))
                        <span><strong>CIN:</strong> {{ $company['cin'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Purchase Order #</div>
            <div style="font-size: 28px; font-weight: 900; color: #0f172a; line-height: 1;">{{ $po->purchase_order_code }}</div>
            <div style="font-size: 12px; margin-top: 5px; color: #475569;">Issued: <strong>{{ $po->created_at->format('d M Y') }}</strong></div>
        </div>
    </div>

    {{-- ─── DARK BANNER: Craftsman Code + Due Date ─── --}}
    <div class="po-banner">
        <div>
            <small style="opacity: 0.7; font-size: 10px; text-transform: uppercase;">Allocated Craftsman</small>
            <div style="font-size: 16px; font-weight: 600;">{{ $po->allocated_craftsman_code ?: 'PENDING ALLOCATION' }}</div>
        </div>
        <div style="text-align: right;">
            <small style="opacity: 0.7; font-size: 10px; text-transform: uppercase;">Due Date</small>
            <div style="font-size: 16px; font-weight: 600; color: #fda4af;">{{ $po->due_date ? $po->due_date->format('d M, Y') : 'N/A' }}</div>
        </div>
    </div>

    {{-- ─── CRAFTSMAN DETAILS SECTION ─── --}}
    @if($craftsman)
    <div class="craftsman-box">
        <div class="craftsman-box-title">🔨 Craftsman Details</div>
        <div class="craftsman-grid">
            <div>
                <span class="label">Name:</span>
                <span class="val"> {{ $craftsman->business_name ?: 'N/A' }}</span>
            </div>
            <!-- <div>
                <span class="label">Contact Name:</span>
                <span class="val"> {{ $craftsman->name ?: 'N/A' }}</span>
            </div> -->
            <div>
                <span class="label">Mobile:</span>
                <span class="val"> {{ $craftsman->mobile ?: 'N/A' }}</span>
            </div>
            <!-- <div>
                <span class="label">Email:</span>
                <span class="val"> {{ $craftsman->email ?: ($craftsman->business_email ?: 'N/A') }}</span>
            </div> -->
            <div>
                <span class="label">GST No:</span>
                <span class="val"> {{ $craftsman->gst_no ?: 'N/A' }}</span>
            </div>
            <div>
                <span class="label">CIN No:</span>
                <span class="val"> {{ $craftsman->cin_no ?: 'N/A' }}</span>
            </div>
        </div>
        @if(!empty($craftsmanAddress))
        <div class="craftsman-address">
            <span style="font-weight: 600; color: #15803d;">Address:</span> {{ $craftsmanAddress }}
        </div>
        @endif
    </div>
    @endif
<div class="content-area">
    <h3 class="brand-name">Kindly  deliver the ordered items within the specified {{ $po->due_date ? $po->due_date->format('d M, Y') : 'N/A' }}. 
        Please carry a printed copy of this purchase order while delivering the goods.</h3>
</div>
    {{-- ─── ITEMS TABLE ─── --}}
    <div class="content-area">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 140px;">Visual Ref</th>
                    <th>Product Details</th>
                    <th style="width: 150px;">Grams Breakdown</th>
                    <th style="width: 100px;" class="text-end">Total Wt.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                <tr>
                    <td class="text-muted">{{ $idx + 1 }}</td>
                    <td>
                        <div class="product-img-box">
                            @php
                                $imagePath = !empty($item['image']) ? $item['image'] : null;
                                $imageSrc = null;

                                if ($imagePath) {
                                    $imageSrc = str_contains($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath);
                                } else {
                                    if(isset($item['design']) && !empty($item['design']->image)) {
                                        $path = $item['design']->image;
                                        $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                    } elseif(isset($item['product']) && $item['product']->images && count($item['product']->images) > 0) {
                                        $path = $item['product']->images[0]->path;
                                        $imageSrc = str_starts_with($path, 'storage/') || str_starts_with($path, 'images/') ? asset($path) : asset('storage/' . $path);
                                    }
                                }
                            @endphp
                            @if($imageSrc)
                                <img src="{{ $imageSrc }}" class="item-image" alt="Design">
                            @else
                                <div style="height: 100px; width: 100px; display: flex; align-items: center; justify-content: center; background: #f8fafc; color: #cbd5e1; font-size: 10px;">NO IMAGE</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a; font-size: 14px;">{{ $item['product'] ? $item['product']->product_name : 'Unknown Product' }}</div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                            <strong>Code:</strong> {{ $item['design'] ? $item['design']->design_code : ($item['product'] ? $item['product']->design_code : 'N/A') }}<br>
                            <strong>Cat:</strong> {{ $item['category_name'] ?? 'N/A' }}
                        </div>
                        @if(!empty($item['item_notes']))
                        <div style="margin-top: 8px; font-size: 11px; padding: 4px 8px; background: #fffbeb; border-left: 3px solid #f59e0b; color: #92400e;">
                            {{ is_array($item['item_notes']) ? implode(', ', $item['item_notes']) : $item['item_notes'] }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if(isset($item['grams']) && is_array($item['grams']))
                            <div style="font-size: 12px; line-height: 1.4;">
                                @foreach($item['grams'] as $k => $gram)
                                    <div><span style="color: #64748b;">{{ $gram }}g</span> <span style="font-weight: 700;">x{{ $item['quantity'][$k] }}</span></div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold" style="font-size: 15px; color: #0f172a;">
                        {{ isset($item['total']) ? number_format($item['total'], 2) : '0.00' }}g
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f8fafc;">
                    <td colspan="4" class="text-end fw-bold" style="padding: 15px; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">Gross Order Weight:</td>
                    <td class="text-end fw-bold" style="padding: 15px; font-size: 18px; color: #e11d48;">
                        {{ number_format(collect($items)->sum('total'), 2) }}g
                    </td>
                </tr>
            </tfoot>
        </table>

        @if($po->notes)
        <div style="margin-top: 25px; padding: 15px; border-left: 5px solid #0f172a; background: #f8fafc;">
            <span style="font-size: 10px; font-weight: 800; color: #0f172a; text-transform: uppercase;">Special Order Notes:</span>
            <p style="margin: 5px 0 0 0; font-size: 13px; color: #334155; line-height: 1.5;">{{ $po->notes }}</p>
        </div>
        @endif

        <div style="margin-top: 60px; display: flex; justify-content: space-between; padding: 0 40px;">
            <div class="sig-box">ISSUED BY</div>
            <div class="sig-box">APPROVED BY</div>
        </div>
    </div>

    <div class="footer-bar">
        Arihanth Jewellers • Production Purchase Order • Page {{ $index + 1 }}
    </div>
</div>
@endforeach

<script>
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('auto_print')) {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    }
</script>
@endsection