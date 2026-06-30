@extends('super-admin.layouts.app')

@section('title', 'Craftsman Production - ' . $craftsman->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Production Dashboard: {{ $craftsman->name }} ({{ $craftsman->craftman_code }})</h1>
                <a href="{{ route('super-admin.craftsman-production.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Work Orders</h6>
                            <h2 class="mb-0">{{ count($workOrders) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Purchase Orders</h6>
                            <h2 class="mb-0">{{ count($purchaseOrders) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Total PO Weight</h6>
                            <h2 class="mb-0">{{ number_format($purchaseOrders->sum('total_calculated_weight'), 3) }} g</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title opacity-75">Current Tab</h6>
                            <h2 class="mb-0 text-capitalize">{{ str_replace('_', ' ', $tab) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Filter -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Filter by Buyer</h5>
                </div>
                <div class="card-body">
                    <form action="{{ request()->url() }}" method="GET" class="row g-3">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="col-md-10">
                            <select name="buyer_code" class="form-select">
                                <option value="">All Buyers</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->bp_code }}" {{ $buyerCode == $buyer->bp_code ? 'selected' : '' }}>
                                        {{ $buyer->business_name ?? $buyer->name }} ({{ $buyer->bp_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Buyer Metrics Summary -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Buyer Metrics Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order Type</th>
                                    <th>Allocated</th>
                                    <th>In Process</th>
                                    <th>Overdue</th>
                                    <th>Completed</th>
                                    <th>Rejected</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Work Orders</strong></td>
                                    <td>{{ $buyerMetrics['work_orders']['allocated'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['in_process'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['overdue'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['completed'] }}</td>
                                    <td>{{ $buyerMetrics['work_orders']['rejected'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Purchase Orders</strong></td>
                                    <td>{{ $buyerMetrics['purchase_orders']['allocated'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['in_process'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['overdue'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['completed'] }}</td>
                                    <td>{{ $buyerMetrics['purchase_orders']['rejected'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Status Tabs -->
            <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'new' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'new']) }}">New</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'in_process' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'in_process']) }}">In Process</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'completed' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'completed']) }}">Completed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'overdue' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'overdue']) }}">Overdue</a>
                </li>
            </ul>

            <!-- Work Orders Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Work Orders</h5>
                    <span class="badge bg-primary rounded-pill">{{ count($workOrders) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>WO Number</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Quantity</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workOrders as $wo)
                                <tr>
                                    <td><strong>{{ $wo->work_order_number }}</strong></td>
                                    <td>{{ $wo->product_name }}</td>
                                    <td>{{ $wo->customer_name }}</td>
                                    <td>{{ $wo->quantity }}</td>
                                    <td>{{ $wo->craftsman_due_date ? $wo->craftsman_due_date->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $wo->status == 'completed' ? 'bg-success' : ($wo->isOverdue() ? 'bg-danger' : 'bg-info') }}">
                                            {{ strtoupper($wo->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No work orders found for this status.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Purchase Orders Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Purchase Orders</h5>
                    <span class="badge bg-info rounded-pill">{{ count($purchaseOrders) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>PO Code</th>
                                    <th>Items Count</th>
                                    <th>Total Weight</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $po)
                                <tr>
                                    <td><strong>{{ $po->purchase_order_code }}</strong></td>
                                    <td>{{ count($po->items ?? []) }}</td>
                                    <td>{{ number_format($po->total_calculated_weight, 3) }} g</td>
                                    <td>{{ $po->due_date ? $po->due_date->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $po->status == 'approved' ? 'bg-success' : 'bg-info' }}">
                                            {{ strtoupper($po->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No purchase orders found for this status.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
