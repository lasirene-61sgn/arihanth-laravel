@extends('super-admin.layouts.app')

@section('title', 'Finance Management')

@section('styles')
<style>
    /* Custom Styling to match the uploaded images */
    .finance-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        align-items: center;
        transition: transform 0.2s;
        height: 100%;
    }
    .finance-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #6c757d;
        font-size: 1.2rem;
    }
    .card-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 2px;
        font-weight: 500;
    }
    .card-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #333;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #444;
        margin: 30px 0 15px 0;
        display: flex;
        align-items: center;
    }
    .section-title i { margin-right: 10px; }
    .sub-text {
        font-size: 0.7rem;
        color: #999;
        display: block;
    }
    hr.separator {
        border-top: 1px solid #ddd;
        margin: 30px 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold">Finance Management</h2>
        <div class="d-flex gap-2">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="viewType" id="daily" value="daily">
                <label class="form-check-label" for="daily">Daily</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="viewType" id="weekly" checked>
                <label class="form-check-label" for="weekly">Weekly</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="viewType" id="monthly">
                <label class="form-check-label" for="monthly">Monthly</label>
            </div>
        </div>
    </div>

    <div class="section-title">
        <i class="bi bi-briefcase"></i> Transaction Overview
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="card-label">Total Sales</div>
                    <div class="card-value">₹ 15,00,000</div>
                    <span class="sub-text">Since this month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-cart-check"></i></div>
                <div>
                    <div class="card-label">Total Purchases</div>
                    <div class="card-value">₹ 12,00,000</div>
                    <span class="sub-text">Since this month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="card-label">Total Receipts</div>
                    <div class="card-value">4000</div>
                    <span class="sub-text">Since this month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-file-earmark-minus"></i></div>
                <div>
                    <div class="card-label">Credit Note</div>
                    <div class="card-value">80</div>
                    <span class="sub-text">Since Started</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-file-earmark-plus"></i></div>
                <div>
                    <div class="card-label">Debit Note</div>
                    <div class="card-value">50</div>
                    <span class="sub-text">Since Started</span>
                </div>
            </div>
        </div>
    </div>

    <hr class="separator">

    <div class="section-title">
        <i class="bi bi-calculator"></i> Tax Deductions and Compliance
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-percent"></i></div>
                <div>
                    <div class="card-label">TDS 94C</div>
                    <div class="card-value">163</div>
                    <span class="sub-text">Since this week</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-percent"></i></div>
                <div>
                    <div class="card-label">TDS 194Q</div>
                    <div class="card-value">35</div>
                    <span class="sub-text">Since this week</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-percent"></i></div>
                <div>
                    <div class="card-label">TDS 94J</div>
                    <div class="card-value">38</div>
                    <span class="sub-text">Since this week</span>
                </div>
            </div>
        </div>
    </div>

    <hr class="separator">

    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
        <div class="section-title m-0">
            <i class="bi bi-search"></i> Financial Dashboard and Insights
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <input type="radio" class="btn-check" name="entityType" id="customer" checked>
            <label class="btn btn-outline-secondary" for="customer">Customer</label>
            <input type="radio" class="btn-check" name="entityType" id="supplier">
            <label class="btn btn-outline-secondary" for="supplier">Supplier</label>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="card-label">Sundry Debtors</div>
                    <div class="card-value text-primary">₹ 12,365,000</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="card-label">Sundry Creditors</div>
                    <div class="card-value text-danger">₹ -25,000</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="card-label">Total Sales Invoice</div>
                    <div class="card-value">163</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="card-label">Total Purchase Invoice</div>
                    <div class="card-value">35</div>
                </div>
            </div>
        </div>
    </div>
    <br><div class="row g-3">
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="card-label">Balance</div>
                    <div class="card-value">₹ 12,365,000</div>
                    <span class="sub-text">Since this Week</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="card-label">Last Sale Date</div>
                    <div class="card-value">27-Nov-2024</div>
                    <span class="sub-text">Since this Month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="card-label">Last Purchase Date</div>
                    <div class="card-value">26-Nov-2024</div>
                    <span class="sub-text">Since this Month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-calendar-minus"></i></div>
                <div>
                    <div class="card-label">Last Receipt Date</div>
                    <div class="card-value">15-Nov-2024</div>
                    <span class="sub-text">Since this Month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="finance-card">
                <div class="card-icon-circle"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="card-label">Total Sales Invoice</div>
                    <div class="card-value">163</div>
                    <span class="sub-text">Since this Week</span>
                </div>
            </div>
        </div>
</div>
@endsection