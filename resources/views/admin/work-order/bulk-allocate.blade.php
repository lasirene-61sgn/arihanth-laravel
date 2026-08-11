@extends('admin.layouts.app')

@section('title', 'Bulk Allocate Work Orders')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Bulk Allocate Work Orders</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Allocate {{ $workOrders->count() }} Work Order(s) to Craftsman</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.work-order.bulk-allocate') }}" method="POST">
                        @csrf
                        
                        <!-- Hidden inputs for work order IDs -->
                        @foreach($workOrders as $workOrder)
                            <input type="hidden" name="work_order_ids[]" value="{{ $workOrder->id }}">
                        @endforeach
                        
                        @if(isset($suggestedCraftsmen) && count($suggestedCraftsmen) > 0)
                        <div class="alert alert-info mb-4 border-info">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-lightbulb-fill text-warning me-2" style="font-size: 1.2rem;"></i>
                                <strong>Suggested Craftsmen for these items:</strong>
                            </div>
                            <p class="small mb-2">Based on historical completions of these categories and design codes, we recommend:</p>
                            <div class="d-flex flex-column gap-2">
                                @foreach($suggestedCraftsmen as $index => $stat)
                                    @php $c = $stat['craftsman']; @endphp
                                    <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded border">
                                        <div>
                                            <span class="badge bg-{{ $index === 0 ? 'success' : 'secondary' }} me-2">#{{ $index + 1 }}</span>
                                            <strong>{{ $c->business_name }}</strong> ({{ $c->craftman_code }})
                                            <span class="text-muted small ms-2"><i class="bi bi-check-circle-fill text-success"></i> {{ $stat['completed_count'] }} similar completed</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary select-suggestion-btn" data-code="{{ $c->craftman_code }}">Select</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="allocated_craftsman_bp_code" class="form-label">Select Craftsman *</label>
                                <select class="form-select select2 @error('allocated_craftsman_bp_code') is-invalid @enderror" 
                                       id="allocated_craftsman_bp_code" name="allocated_craftsman_bp_code" required>
                                    <option value="">Select Craftsman</option>
                                    @foreach($craftsmen as $craftsman)
                                        <option value="{{ $craftsman->craftman_code }}" {{ old('allocated_craftsman_bp_code') == $craftsman->craftman_code ? 'selected' : '' }}>
                                            {{ $craftsman->craftman_code }} - {{ $craftsman->business_name }} {{ $craftsman->dear ? '('.$craftsman->dear.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('allocated_craftsman_bp_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="craftsman_due_date" class="form-label">Craftsman Due Date</label>
                                <input type="date" class="form-control @error('craftsman_due_date') is-invalid @enderror" 
                                       id="craftsman_due_date" name="craftsman_due_date" value="{{ old('craftsman_due_date') }}">
                                @error('craftsman_due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                    <option value="">None (Normal)</option>
                                    <option value="Normal" {{ old('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Work Orders Summary -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Work Orders to be Allocated</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Work Order Number</th>
                                                <th>Customer Name</th>
                                                <th>Product Name</th>
                                                <th>Quantity</th>
                                                <th>Due Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($workOrders as $workOrder)
                                            <tr>
                                                <td>{{ $workOrder->work_order_number }}</td>
                                                <td>{{ $workOrder->customer_name }}</td>
                                                <td>{{ $workOrder->product_name }}</td>
                                                <td>{{ $workOrder->quantity }}</td>
                                                <td>{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.work-order.index', ['tab' => 'new-orders']) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-people"></i> Allocate {{ $workOrders->count() }} Work Order(s)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        var craftsmanSelect = $('.select2');
        craftsmanSelect.select2({
            placeholder: "Select Craftsman",
            allowClear: true,
            width: '100%'
        });

        // Suggested Craftsmen Selection Logic
        $('.select-suggestion-btn').on('click', function() {
            const code = $(this).data('code');
            craftsmanSelect.val(code).trigger('change');
            
            // Visual feedback
            $('.select-suggestion-btn').removeClass('btn-primary text-white').addClass('btn-outline-primary').text('Select');
            $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white').html('<i class="bi bi-check-lg"></i> Selected');
        });
    });
</script>
@endsection
