<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order #{{ $workOrder->work_order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .image-section {
            text-align: center;
            margin: 20px 0;
        }
        .image-section img {
            max-width: 300px;
            max-height: 300px;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Work Order</h1>
        <h2>#{{ $workOrder->work_order_number }}</h2>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">BP Code:</span>
            {{ $workOrder->bp_code }}
        </div>
        <div class="info-item">
            <span class="info-label">Customer Name:</span>
            {{ $workOrder->customer_name }}
        </div>
        <div class="info-item">
            <span class="info-label">Reference No:</span>
            {{ $workOrder->reference_no ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Order Date:</span>
            {{ $workOrder->created_at ? \Carbon\Carbon::parse($workOrder->created_at)->format('d M, Y') : 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Due Date:</span>
            {{ $workOrder->due_date ? \Carbon\Carbon::parse($workOrder->due_date)->format('d M, Y') : 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Product Category:</span>
            {{ $workOrder->product_category ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Subcategory:</span>
            {{ $workOrder->subcategory ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Product Name:</span>
            {{ $workOrder->product_name }}
        </div>
        <div class="info-item">
            <span class="info-label">Product Code:</span>
            {{ $workOrder->product_code ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Type:</span>
            {{ $workOrder->type ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Open/Close:</span>
            {{ $workOrder->open_close ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Quantity:</span>
            {{ $workOrder->quantity }}
        </div>
        <div class="info-item">
            <span class="info-label">Weight Range:</span>
            {{ $workOrder->weight_from ?: 'N/A' }} - {{ $workOrder->weight_to ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Hallmark:</span>
            {{ $workOrder->hallmark ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Rodium:</span>
            {{ $workOrder->rodium ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Hook:</span>
            {{ $workOrder->hook ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Size:</span>
            {{ $workOrder->size ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Stone:</span>
            {{ $workOrder->stone ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Enamel:</span>
            {{ $workOrder->enamel ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Length:</span>
            {{ $workOrder->length ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Relabel Code:</span>
            {{ $workOrder->relabel_code ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Narration:</span>
            {{ $workOrder->narration_admin ?: 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Status:</span>
            @if($workOrder->status == 'new')
                <span>New</span>
            @elseif($workOrder->status == 'allocated')
                <span>Allocated</span>
            @elseif($workOrder->craftsman_status == 'in_process')
                <span>In Process</span>
            @elseif($workOrder->craftsman_status == 'completed')
                <span>Completed</span>
            @elseif($workOrder->craftsman_status == 'rejected')
                <span>Rejected</span>
            @else
                <span>Unknown</span>
            @endif
        </div>
    </div>

    @if($workOrder->product_image)
    <div class="image-section">
        <h3>Product Image</h3>
        <img src="{{ asset($workOrder->product_image) }}" alt="Product Image">
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <p>Prepared By</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <p>Checked By</p>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <p>Approved By</p>
            <div class="signature-line"></div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <a href="{{ route('user.work-order.show', $workOrder) }}" class="btn btn-secondary">Back to Details</a>
    </div>
</body>
</html>