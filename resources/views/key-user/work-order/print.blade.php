<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order - {{ $workOrder->work_order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .section-title {
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .info-row {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .badge-lg {
            font-size: 1rem;
            padding: 8px 12px;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header text-center">
            <h1 class="mb-2">WORK ORDER</h1>
            <h2 class="text-primary">{{ $workOrder->work_order_number }}</h2>
            <p class="text-muted mb-0">Order Date : {{ now()->format('d M, Y') }}</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h4 class="section-title">Work Order Details</h4>
                
                <div class="info-row">
                    <div class="info-label">BP Code:</div>
                    <div>{{ $workOrder->bp_code }}</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Due Date:</div>
                    <div>{{ $workOrder->due_date->format('d M, Y') }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Product Category:</div>
                    <div>{{ $workOrder->product_category }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Type:</div>
                    <div>{{ $workOrder->type }}</div>
                </div>
                
                <!-- <div class="info-row">
                    <div class="info-label">Order Type:</div>
                    <div>
                        @if($workOrder->order_type == 'Regular')
                            <span class="badge bg-primary badge-lg">{{ $workOrder->order_type }}</span>
                        @elseif($workOrder->order_type == 'Urgent')
                            <span class="badge bg-warning badge-lg">{{ $workOrder->order_type }}</span>
                        @else
                            <span class="badge bg-danger badge-lg">{{ $workOrder->order_type }}</span>
                        @endif
                    </div>
                </div> -->
            </div>
            
            <div class="col-md-6">
                <h4 class="section-title">Quantity & Status</h4>
                
                <div class="info-row">
                    <div class="info-label">Quantity:</div>
                    <div>{{ $workOrder->quantity }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Size:</div>
                    <div>{{ $workOrder->size }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Open/Close:</div>
                    <div>
                        @if($workOrder->open_close == 'Open')
                            <span class="badge bg-success badge-lg">{{ $workOrder->open_close }}</span>
                        @else
                            <span class="badge bg-secondary badge-lg">{{ $workOrder->open_close }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Enamel:</div>
                    <div>{{ $workOrder->enamel }}</div>
                </div>
            </div>
        </div>
        
        @if($workOrder->description)
        <div class="row mt-4">
            <div class="col-12">
                <h4 class="section-title">Description</h4>
                <div>{{ $workOrder->description }}</div>
            </div>
        </div>
        @endif
        
        @if($workOrder->design_image)
        <div class="row mt-4">
            <div class="col-12">
                <h4 class="section-title">Design Image</h4>
                <div class="text-center">
                    <img src="{{ asset('storage/' . $workOrder->design_image) }}" 
                         alt="Design Image" 
                         class="img-fluid rounded" 
                         style="max-height: 300px;">
                </div>
            </div>
        </div>
        @endif
        
        <div class="row mt-5 pt-4 border-top">
            <div class="col-6">
                <div class="text-center">
                    <div class="mb-1"><strong>Prepared By</strong></div>
                    <div class="text-muted">Key User</div>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center">
                    <div class="mb-1"><strong>Approved By</strong></div>
                    <div class="text-muted">____________________</div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Work Order
            </button>
            <a href="{{ route('key-user.work-order.show', $workOrder) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>