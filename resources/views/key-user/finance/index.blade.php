@extends('key-user.layouts.app')

@section('title', 'Finance Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Finance Management</h2>
        <div>
            <span class="badge bg-warning text-dark fs-6">Under Development</span>
        </div>
    </div>

    <!-- Finance Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-primary text-white">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="stat-number">₹0.00</div>
                    <div class="stat-text">Total Revenue</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-success text-white">
                        <i class="bi bi-arrow-down-up"></i>
                    </div>
                    <div class="stat-number">₹0.00</div>
                    <div class="stat-text">Total Payments</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-info text-white">
                        <i class="bi bi-bank"></i>
                    </div>
                    <div class="stat-number">₹0.00</div>
                    <div class="stat-text">Outstanding Balance</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-warning text-dark">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-number">0%</div>
                    <div class="stat-text">Profit Margin</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="erp-card">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Finance Dashboard</h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                        <i class="bi bi-download me-1"></i> Export Reports
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                        <i class="bi bi-calendar me-1"></i> Date Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-currency-dollar text-primary" style="font-size: 4rem;"></i>
                </div>
                <h3 class="text-muted">Finance Module Coming Soon</h3>
                <p class="text-muted mb-4">
                    This section is currently under development. Features will include:
                </p>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="row text-start">
                            <div class="col-md-6 mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Payment Processing</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Invoice Management</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Financial Reports</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Accounting Integration</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Tax Management</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span>Budget Planning</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary" disabled>
                        <i class="bi bi-bell me-1"></i> Notify Me When Ready
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Placeholder Sections -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="erp-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No transactions available yet</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="erp-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Financial Reports</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Reports will be available soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Future finance functionality will go here
    console.log('Finance module loaded - placeholder');
</script>
@endsection