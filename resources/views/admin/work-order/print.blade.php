<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order {{ $workOrder->work_order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            background: #fff;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h2 {
            background: #f0f0f0;
            padding: 10px;
            margin: 0 0 15px;
            border-left: 4px solid #007bff;
            font-size: 18px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }
        
        .detail-value {
            font-size: 15px;
            margin-top: 3px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-new { background: #007bff; color: white; }
        .status-allocated { background: #28a745; color: white; }
        .status-for_approval { background: #ffc107; color: black; }
        .status-completed { background: #28a745; color: white; }
        .status-in_process { background: #17a2b8; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        
        .narration {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            color: #666;
            font-size: 12px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>WORK ORDER DETAILS</h1>
            <p>Order Number: {{ $workOrder->work_order_number }}</p>
        </div>
        
        <div class="section">
            <h2>Basic Information</h2>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Work Order Number</div>
                    <div class="detail-value">{{ $workOrder->work_order_number }}</div>
                </div>
                 <div class="col-4 text-center">
                  @php
            $displayImage = $workOrder->product_image;
            $isPdf = false;

            if ($displayImage) {
            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
            }
            elseif ($workOrder->product && $workOrder->product->images && $workOrder->product->images->count() > 0) {
            $displayImage = $workOrder->product->images->first()->path;
            $isPdf = Str::endsWith(strtolower($displayImage), '.pdf');
            }
            @endphp
            @if($displayImage)

            @if($isPdf)
            {{-- PDF preview for print --}}
            <embed
                src="{{ asset($displayImage) }}"
                type="application/pdf"
                width="80"
                height="80" />
            @else
            {{-- Normal Image --}}
            <img src="{{ asset($displayImage) }}"
                class="product-image"
                style="width:80px;height:80px;object-fit:contain;">
            @endif

            @else
            <div class="border p-5 text-muted">No Image</div>
            @endif
            @if($workOrder->product && $workOrder->product->images->count() > 1)
            <div style="margin-top:5px;">
                @foreach($workOrder->product->images as $img)
                <img src="{{ asset($img->path) }}"
                    style="width:50px;height:50px;object-fit:contain;margin-right:3px;">
                @endforeach
            </div>
            @endif
            </div>
                <div class="detail-item">
                    <div class="detail-label">Customer Name</div>
                    <div class="detail-value">{{ $workOrder->customer_name }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Product Name</div>
                    <div class="detail-value">{{ $workOrder->product_name }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Quantity</div>
                    <div class="detail-value">{{ $workOrder->quantity }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Order Date</div>
                    <div class="detail-value">{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Craftsman Due Date</div>
                    <div class="detail-value">{{ $workOrder->craftsman_due_date ? $workOrder->craftsman_due_date->format('d M, Y') : 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        @if($workOrder->status == 'new')
                            <span class="status-badge status-new">New</span>
                        @elseif($workOrder->status == 'allocated')
                            <span class="status-badge status-allocated">Allocated</span>
                        @elseif($workOrder->status == 'for_approval')
                            <span class="status-badge status-for_approval">For Approval</span>
                        @elseif($workOrder->status == 'completed')
                            <span class="status-badge status-completed">Completed</span>
                        @else
                            <span class="status-badge status-{{ str_replace('_', '-', $workOrder->status ?? 'unknown') }}">{{ ucfirst($workOrder->status ?? 'Unknown') }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Craftsman Status</div>
                    <div class="detail-value">
                        @if($workOrder->craftsman_status == 'allocated')
                            <span class="status-badge status-allocated">Allocated</span>
                        @elseif($workOrder->craftsman_status == 'in_process')
                            <span class="status-badge status-in_process">In Process</span>
                        @elseif($workOrder->craftsman_status == 'completed')
                            <span class="status-badge status-completed">Completed</span>
                        @elseif($workOrder->craftsman_status == 'rejected')
                            <span class="status-badge status-rejected">Rejected</span>
                        @else
                            <span class="status-badge status-{{ str_replace('_', '-', $workOrder->craftsman_status ?? 'unknown') }}">{{ ucfirst($workOrder->craftsman_status ?? 'Unknown') }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">BP Code</div>
                    <div class="detail-value">{{ $workOrder->bp_code ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Reference No</div>
                    <div class="detail-value">{{ $workOrder->reference_no ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Product Category</div>
                    <div class="detail-value">{{ $workOrder->product_category ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Type</div>
                    <div class="detail-value">{{ $workOrder->type ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Order Type</div>
                    <div class="detail-value">{{ $workOrder->order_type ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Open/Close</div>
                    <div class="detail-value">{{ $workOrder->open_close ?? 'N/A' }}</div>
                </div>
                
                @if($workOrder->allocated_craftsman_bp_code)
                <div class="detail-item">
                    <div class="detail-label">Allocated Craftsman</div>
                    <div class="detail-value">{{ $workOrder->craftsman ? $workOrder->craftsman->business_name : 'N/A' }}</div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="section">
            <h2>Product Specifications</h2>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Weight From</div>
                    <div class="detail-value">{{ $workOrder->weight_from ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Weight To</div>
                    <div class="detail-value">{{ $workOrder->weight_to ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">HUID</div>
                    <div class="detail-value">{{ $workOrder->hallmark ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Rodium</div>
                    <div class="detail-value">{{ $workOrder->rodium ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Hook</div>
                    <div class="detail-value">{{ $workOrder->hook ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Size</div>
                    <div class="detail-value">{{ $workOrder->size ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Stone</div>
                    <div class="detail-value">{{ $workOrder->stone ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Enamel</div>
                    <div class="detail-value">{{ $workOrder->enamel ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Length</div>
                    <div class="detail-value">{{ $workOrder->length ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Product Code</div>
                    <div class="detail-value">{{ $workOrder->product_code ?? 'N/A' }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Relabel Code</div>
                    <div class="detail-value">{{ $workOrder->relabel_code ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>Narrations</h2>
            <div class="detail-item">
                <div class="detail-label">Narration for Craftsman</div>
                <div class="narration">{{ $workOrder->narration_craftsman ?? 'N/A' }}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">Narration for Admin</div>
                <div class="narration">{{ $workOrder->narration_admin ?? 'N/A'}} </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Generated on {{ now()->format('d M, Y H:i') }} | Work Order: {{ $workOrder->work_order_number }}</p>
        </div>
    </div>
</body>
</html>