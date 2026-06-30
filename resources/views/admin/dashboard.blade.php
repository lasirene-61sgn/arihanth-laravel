@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid dashboard-container">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h4 mb-0">Welcome, <span style="color:var(--primary-magenta); font-weight:800;">{{ Auth::guard('admin')->user()->full_name }}</span></h1>
            </div>

            <div class="row">
                <!-- Left Content - Stats Cards -->
                <div class="col-lg-9 col-md-8 mb-4">
                    <!-- Top Stats Grid -->
                    <div class="row g-3 mb-4">
                        <!-- Business Partner Card -->

                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">

                            <div class="dashboard-stat-card h-100">
                                <a href="{{ route('admin.business-partner.index') }}">
                                    <div class="card-body p-3">

                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($buyersCount + $craftsmenCount) }}</h2>
                                                <p class="stat-label mb-0">BUSINESS PARTNER</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>

                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Buyers Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.business-partner.buyer') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($buyersCount) }}</h2>
                                                <p class="stat-label mb-0">BUYERS</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-person-check"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Craftsman Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.business-partner.craftman') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($craftsmenCount) }}</h2>
                                                <p class="stat-label mb-0">CRAFTSMAN</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-hammer"></i>
                                            </div>

                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- KYC Pending Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.kyc-pending.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($kycPendingCount) }}</h2>
                                                <p class="stat-label mb-0">KYC PENDING</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-clock-history"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Admins Card
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h2 class="stat-number mb-1">{{ number_format($adminsCount) }}</h2>
                                            <p class="stat-label mb-0">ADMINS</p>
                                        </div>
                                        <div class="stat-icon-wrapper">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- Key Users Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.key-user.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($keyUsersCount) }}</h2>
                                                <p class="stat-label mb-0">KEY USERS</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-key"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Users Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.user.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($usersCount) }}</h2>
                                                <p class="stat-label mb-0">USERS</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Finance Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.finance.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">₹{{ number_format($financeTotal) }}</h2>
                                                <p class="stat-label mb-0">FINANCE</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-currency-rupee"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Work Orders Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.work-order.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($workOrdersCount) }}</h2>
                                                <p class="stat-label mb-0">WORK ORDERS</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Products Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.product.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($productsCount) }}</h2>
                                                <p class="stat-label mb-0">PRODUCTS</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Designs Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.design.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($designsCount) }}</h2>
                                                <p class="stat-label mb-0">DESIGNS</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-palette"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Catalogue Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.catalogue.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($cataloguesCount) }}</h2>
                                                <p class="stat-label mb-0">CATALOGUE</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-book"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Orders Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.purchase-order.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($purchaseOrdersCount) }}</h2>
                                                <p class="stat-label mb-0">{{ __('messages.purchase_order') }}</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-cart-check"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Live Stock Order Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.stock-order.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($stockOrdersCount) }}</h2>
                                                <p class="stat-label mb-0">{{ __('messages.live_stock_order') }}</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-box2-heart"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Repairs Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.repairs.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($repairsCount) }}</h2>
                                                <p class="stat-label mb-0">{{ __('messages.repairs') }}</p>
                                            </div>
                                            <div class="stat-icon-wrapper">
                                                <i class="bi bi-tools"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Work Orders Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.work-order.index', ['tab' => 'overdue-orders']) }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($workOrdersOverdueCount) }}</h2>
                                                <p class="stat-label mb-0">Overdue Work Orders</p>
                                            </div>
                                            <div class="stat-icon-wrapper" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                                <i class="bi bi-clock-history"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Purchase Orders Card -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="dashboard-stat-card h-100">
                                <div class="card-body p-3">
                                    <a href="{{ route('admin.purchase-order.index', ['overdue' => 1]) }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h2 class="stat-number mb-1">{{ number_format($purchaseOrdersOverdueCount) }}</h2>
                                                <p class="stat-label mb-0">Overdue Purchase Orders</p>
                                            </div>
                                            <div class="stat-icon-wrapper" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                                <i class="bi bi-clock-history"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Cards Row -->
                    <div class="row g-3 mb-4">
                        <!-- Top Picks Craftsman -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100" data-bs-toggle="modal" data-bs-target="#topPicksCraftsmanModal" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-star text-warning fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ !empty($topPicksCraftsmanFull) ? collect($topPicksCraftsmanFull)->first()['allocated'] : 0 }}</h3>
                                    <p class="analytics-label mb-0">TOP PICKS CRAFTSMAN</p>
                                </div>
                            </div>
                        </div>

                        <!-- Least Picks Craftsman -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100" data-bs-toggle="modal" data-bs-target="#leastPicksCraftsmanModal" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-clock text-info fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ !empty($leastPicksCraftsmanFull) ? collect($leastPicksCraftsmanFull)->first()['allocated'] : 0 }}</h3>
                                    <p class="analytics-label mb-0">LEAST PICKS CRAFTSMAN</p>
                                </div>
                            </div>
                        </div>

                        <!-- Most Selling Products -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100" data-bs-toggle="modal" data-bs-target="#mostSellingProductsModal" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-graph-up-arrow text-success fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ !empty($mostSellingProductsFull) ? collect($mostSellingProductsFull)->first()['count'] : 0 }}</h3>
                                    <p class="analytics-label mb-0">MOST SELLING PRODUCTS</p>
                                </div>
                            </div>
                        </div>

                        <!-- Least Selling Products -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100" data-bs-toggle="modal" data-bs-target="#leastSellingProductsModal" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-graph-down-arrow text-danger fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ !empty($leastSellingProductsFull) ? collect($leastSellingProductsFull)->first()['count'] : 0 }}</h3>
                                    <p class="analytics-label mb-0">LEAST SELLING PRODUCTS</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Payments -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-credit-card fs-5" style="color: var(--primary-magenta);"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ $quickPayments }}</h3>
                                    <p class="analytics-label mb-0">QUICK PAYMENTS</p>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Payments -->
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-exclamation-circle text-warning fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ $overduePayments }}</h3>
                                    <p class="analytics-label mb-0">OVERDUE PAYMENTS</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100" data-bs-toggle="modal" data-bs-target="#topPicksClientsModal" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-people-fill text-success fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ !empty($topPicksClients) ? collect($topPicksClients)->first() : 0 }}</h3>
                                    <p class="analytics-label mb-0">{{ strtoupper(__('messages.top_picks_clients')) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="analytics-card h-100" data-bs-toggle="modal" data-bs-target="#leastPicksClientsModal" style="cursor: pointer;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <i class="bi bi-person-x text-secondary fs-5"></i>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                    <h3 class="analytics-number mb-1">{{ !empty($leastPicksClients) ? collect($leastPicksClients)->first() : 0 }}</h3>
                                    <p class="analytics-label mb-0">{{ strtoupper(__('messages.least_pick_clients')) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links Section -->
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="quick-links-card">
                                <div class="card-header-custom">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="bi bi-link-45deg me-2"></i>Quick Links
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <a href="{{ route('admin.business-partner.buyer') }}" class="quick-link-item">
                                                <i class="bi bi-person-plus-fill"></i>
                                                <span>Add User</span>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <a href="#" class="quick-link-item">
                                                <i class="bi bi-receipt"></i>
                                                <span>Overdue/Payments</span>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <a href="#" class="quick-link-item">
                                                <i class="bi bi-brush-fill"></i>
                                                <span>Designs</span>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <a href="#" class="quick-link-item">
                                                <i class="bi bi-newspaper"></i>
                                                <span>Craftsmen</span>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6">
                                            <a href="#" class="quick-link-item">
                                                <i class="bi bi-book-fill"></i>
                                                <span>Catalogue</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- External Links Section -->
                        <div class="col-12 mt-3">
                            <div class="quick-links-card">
                                <div class="card-header-custom">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="bi bi-box-arrow-up-right me-2"></i>External Links
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar - Calendar -->
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="calendar-card sticky-top" style="top: 20px;">
                        <div class="card-body p-0">
                            <!-- Calendar Header -->
                            <div class="calendar-header p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-calendar-nav" id="prevMonth">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <h5 class="mb-0 calendar-month-title" id="currentMonth">January 2026</h5>
                                    <button class="btn btn-calendar-nav" id="nextMonth">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Calendar Grid -->
                            <div class="calendar-grid p-3">
                                <table class="calendar-table w-100">
                                    <thead>
                                        <tr>
                                            <th>Mon</th>
                                            <th>Tue</th>
                                            <th>Wed</th>
                                            <th>Thu</th>
                                            <th>Fri</th>
                                            <th>Sat</th>
                                            <th>Sun</th>
                                        </tr>
                                    </thead>
                                    <tbody id="calendarBody">
                                        <!-- Calendar days will be generated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Today's Meetings Section -->
                            <div class="meetings-section p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 fw-semibold">Today's Meetings</h6>
                                    <button class="btn btn-primary btn-sm btn-add-meeting" id="addMeeting">
                                        <i class="bi bi-plus-lg"></i> Add Meeting
                                    </button>
                                </div>
                                <div class="text-center text-muted py-4" id="noMeetings">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                    <p class="mb-0 small">No meetings scheduled</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Statistics -->
<!-- Top Picks Craftsman Modal -->
<div class="modal fade" id="topPicksCraftsmanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">TOP PICKS CRAFTSMAN (Top 15)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2" class="align-middle" style="font-size: 0.75rem;">{{ __('messages.craftsman') }}</th>
                                <th rowspan="2" class="text-center align-middle" style="font-size: 0.75rem;">{{ __('messages.bp_code') }}</th>
                                <th colspan="3" class="text-center bg-primary text-white" style="font-size: 0.7rem;">WORK ORDERS (WA)</th>
                                <th colspan="3" class="text-center bg-info text-white" style="font-size: 0.7rem;">PURCHASE ORDERS (PA)</th>
                            </tr>
                            <tr class="bg-white">
                                <th class="text-center py-1" style="font-size: 0.65rem;">PROCESS (C/W)</th>
                                <th class="text-center py-1" style="font-size: 0.65rem;">FOR APPROVAL (C/W)</th>
                                <th class="text-center py-1 text-danger" style="font-size: 0.65rem;">OVERDUE(C/W)</th>
                                <th class="text-center py-1" style="font-size: 0.65rem;">PROCESS (C/W)</th>
                                <th class="text-center py-1" style="font-size: 0.65rem;">FOR APPROVAL (C/W)</th>
                                <th class="text-center py-1 text-danger" style="font-size: 0.65rem;">OVERDUE(C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksCraftsmanFull as $code => $stat)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ $code }}</span></td>
                                
                                {{-- Work Orders --}}
                                <td class="text-center text-primary" style="font-size: 0.75rem;">{{ $stat['wo']['in_process']['count'] }} | {{ number_format($stat['wo']['in_process']['weight'], 2) }}</td>
                                <td class="text-center text-purple-600" style="font-size: 0.75rem;">{{ $stat['wo']['for_approval']['count'] }} | {{ number_format($stat['wo']['for_approval']['weight'], 2) }}</td>
                                <td class="text-center text-danger fw-bold" style="font-size: 0.75rem;">{{ $stat['wo']['overdue']['count'] }} | {{ number_format($stat['wo']['overdue']['weight'], 2) }}</td>
                                
                                {{-- Purchase Orders --}}
                                <td class="text-center text-info" style="font-size: 0.75rem;">{{ $stat['po']['in_process']['count'] }} | {{ number_format($stat['po']['in_process']['weight'], 2) }}</td>
                                <td class="text-center text-purple-600" style="font-size: 0.75rem;">{{ $stat['po']['for_approval']['count'] }} | {{ number_format($stat['po']['for_approval']['weight'], 2) }}</td>
                                <td class="text-center text-warning fw-bold" style="font-size: 0.75rem;">{{ $stat['po']['overdue']['count'] }} | {{ number_format($stat['po']['overdue']['weight'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No data found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Craftsman Modal -->
<div class="modal fade" id="leastPicksCraftsmanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">LEAST PICKS CRAFTSMAN (Top 15)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">{{ __('messages.craftsman') }}</th>
                                <th class="border-0 text-center">{{ __('messages.bp_code') }}</th>
                                <th class="border-0 text-center">{{ __('messages.allocated') }}</th>
                                <th class="border-0 text-center">{{ __('messages.completed') }}</th>
                                <th class="border-0 text-center">{{ __('messages.total_weight') }}</th>
                                <th class="border-0 text-center">{{ __('messages.total_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leastPicksCraftsmanFull as $code => $stat)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark">{{ $code }}</span></td>
                                <td class="text-center fw-bold">{{ $stat['allocated'] }}</td>
                                <td class="text-center text-success">{{ $stat['completed'] }}</td>
                                <td class="text-center fw-semibold text-primary">{{ number_format($stat['total_weight'], 3) }}</td>
                                <td class="text-center fw-semibold text-danger">₹{{ number_format($stat['total_amount'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No data found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Most Selling Products Modal -->
<div class="modal fade" id="mostSellingProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">MOST SELLING PRODUCTS (Top 15)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">{{ __('messages.product') }}</th>
                                <th class="border-0 text-center">{{ __('messages.design_code_label') }}</th>
                                <th class="border-0 text-center">{{ __('messages.total_usage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mostSellingProductsFull as $key => $stat)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $stat['design_code'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success fs-6">{{ $stat['count'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Selling Products Modal -->
<div class="modal fade" id="leastSellingProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">LEAST SELLING PRODUCTS (Top 15)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">{{ __('messages.product') }}</th>
                                <th class="border-0 text-center">{{ __('messages.design_code_label') }}</th>
                                <th class="border-0 text-center">{{ __('messages.total_usage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leastSellingProductsFull as $key => $stat)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $stat['design_code'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger fs-6">{{ $stat['count'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Enhancements */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .modal-header .btn-close {
        background-color: #f8fafc;
        opacity: 1;
        border-radius: 50%;
        padding: 8px;
        margin: 0;
    }

    .table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .bg-success-subtle {
        background-color: #e6f7ef !important;
    }

    .bg-danger-subtle {
        background-color: #fbe9e9 !important;
    }

    /* ── Magenta Light Dashboard Theme ── */
    :root {
        --primary-magenta: #97144d;
        --primary-hover: #b51a5d;
        --bg-light: #f0f2f5;
        --card-bg: #FFFFFF;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --magenta-shadow: rgba(151, 20, 77, 0.1);
    }

    /* Dashboard Container */
    .dashboard-container {
        background-color: var(--bg-light);
        min-height: 100vh;
        padding-bottom: 40px;
    }

    /* Dashboard Stat Cards */
    .dashboard-stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .dashboard-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px var(--magenta-shadow);
        border-color: var(--primary-magenta);
    }

    .stat-number {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-magenta);
        background: #fdf2f8;
        /* Very light magenta background */
        font-size: 1.4rem;
        transition: var(--transition);
    }

    .dashboard-stat-card:hover .stat-icon-wrapper {
        background: var(--primary-magenta);
        color: #ffffff;
    }

    /* Analytics Cards */
    .analytics-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .analytics-card:hover {
        border-color: var(--primary-magenta);
        box-shadow: 0 4px 12px var(--magenta-shadow);
        transform: translateY(-2px);
    }

    .analytics-number {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .analytics-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* Quick Links */
    .quick-links-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
    }

    .card-header-custom {
        background: #ffffff;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .quick-link-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .quick-link-item:hover {
        background: var(--primary-magenta);
        color: #ffffff;
        border-color: var(--primary-magenta);
        transform: translateY(-2px);
    }

    /* Calendar: Redesigned for Magenta */
    .calendar-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .calendar-header {
        background: var(--primary-magenta);
        color: #ffffff;
        padding: 20px;
    }

    .calendar-month-title {
        color: #ffffff;
        font-weight: 700;
    }

    .btn-calendar-nav {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
    }

    .btn-calendar-nav:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    .calendar-table th {
        color: var(--text-muted);
        font-weight: 700;
        padding: 12px 0;
    }

    .calendar-table td.today {
        background: var(--primary-magenta) !important;
        color: #ffffff !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 10px var(--magenta-shadow);
    }

    .calendar-table td.selected {
        background: #fdf2f8;
        color: var(--primary-magenta);
        border-radius: 8px;
    }

    .btn-add-meeting {
        background: var(--primary-magenta);
        border: none;
        font-weight: 700;
        padding: 10px 16px;
        border-radius: 8px;
    }

    .btn-add-meeting:hover {
        background: var(--primary-hover);
        box-shadow: 0 4px 12px var(--magenta-shadow);
    }

    /* Fix Stat Number Color in links */
    .dashboard-stat-card a {
        text-decoration: none;
        display: block;
    }

    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.5rem;
        }
    }
</style>


<script>
    // Calendar JavaScript
    // Load events from localStorage
    let events = JSON.parse(localStorage.getItem('calendarEvents')) || {};

    // Save events to localStorage
    function saveEvents() {
        localStorage.setItem('calendarEvents', JSON.stringify(events));
    }

    // Sample initial events (can be removed after implementation)
    if (Object.keys(events).length === 0) {
        events['2026-01-28'] = [{
                title: 'Team Meeting',
                time: '10:00 AM',
                type: 'meeting'
            },
            {
                title: 'Product Review',
                time: '2:00 PM',
                type: 'review'
            }
        ];
        events['2026-01-29'] = [{
            title: 'Client Call',
            time: '11:00 AM',
            type: 'call'
        }];
        events['2026-01-30'] = [{
            title: 'Project Deadline',
            time: '5:00 PM',
            type: 'deadline'
        }];
        saveEvents();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const calendarBody = document.getElementById('calendarBody');
        const currentMonthElement = document.getElementById('currentMonth');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        function generateCalendar(month, year) {
            calendarBody.innerHTML = '';
            currentMonthElement.textContent = `${months[month]} ${year}`;

            // Get first day of month
            let firstDay = new Date(year, month, 1);
            let startingDayOfWeek = firstDay.getDay();
            // Adjust for Monday start (0 = Monday, 6 = Sunday)
            startingDayOfWeek = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1;

            // Get number of days in month
            let monthLength = new Date(year, month + 1, 0).getDate();

            // Get previous month details
            let prevMonthLength = new Date(year, month, 0).getDate();

            let day = 1;
            let nextMonthDay = 1;

            // Create calendar rows
            for (let i = 0; i < 6; i++) {
                let row = document.createElement('tr');

                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement('td');

                    if (i === 0 && j < startingDayOfWeek) {
                        // Previous month days
                        cell.textContent = prevMonthLength - startingDayOfWeek + j + 1;
                        cell.classList.add('other-month');
                    } else if (day > monthLength) {
                        // Next month days
                        cell.textContent = nextMonthDay;
                        cell.classList.add('other-month');
                        nextMonthDay++;
                    } else {
                        // Current month days
                        cell.textContent = day;

                        // Highlight today
                        let today = new Date();
                        if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                            cell.classList.add('today');
                        }

                        // Add click event for date selection
                        cell.addEventListener('click', function() {
                            // Remove previous selection
                            document.querySelectorAll('.calendar-table td.selected').forEach(td => {
                                td.classList.remove('selected');
                            });
                            // Add selection to clicked date (if not today)
                            if (!this.classList.contains('today')) {
                                this.classList.add('selected');
                            }

                            // Show events for selected date
                            const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                            showEventsForDate(dateStr);
                        });

                        day++;
                    }

                    row.appendChild(cell);
                }

                calendarBody.appendChild(row);

                if (day > monthLength) {
                    break;
                }
            }
        }

        prevMonthBtn.addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar(currentMonth, currentYear);
        });

        nextMonthBtn.addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar(currentMonth, currentYear);
        });

        // Initialize calendar
        generateCalendar(currentMonth, currentYear);

        // Add Meeting Button
        document.getElementById('addMeeting').addEventListener('click', function() {
            // You can integrate with your meeting creation modal/form here
            alert('Add Meeting functionality - Connect to your meeting system');
        });
    });

    // Function to show events for a selected date
    function showEventsForDate(dateStr) {
        const meetingsSection = document.getElementById('noMeetings').parentElement;

        if (events[dateStr] && events[dateStr].length > 0) {
            let html = '<div class="d-flex justify-content-between align-items-center mb-3">';
            html += '<h6 class="mb-0 fw-semibold">Events for ' + dateStr + '</h6>';
            html += '<button class="btn btn-primary btn-sm btn-add-meeting" id="addMeeting">';
            html += '<i class="bi bi-plus-lg"></i> Add Event';
            html += '</button>';
            html += '</div>';

            html += '<div class="meetings-list">';
            events[dateStr].forEach((event, index) => {
                let icon = 'bi-calendar-event';
                let color = 'text-primary';

                if (event.type === 'meeting') {
                    icon = 'bi-people';
                    color = 'text-primary';
                } else if (event.type === 'review') {
                    icon = 'bi-eye';
                    color = 'text-success';
                } else if (event.type === 'call') {
                    icon = 'bi-telephone';
                    color = 'text-info';
                } else if (event.type === 'deadline') {
                    icon = 'bi-alarm';
                    color = 'text-danger';
                }

                html += '<div class="meeting-item d-flex align-items-center p-2 mb-2 rounded" style="background-color: #f8f9fa; position: relative;">';
                html += '<i class="bi ' + icon + ' ' + color + ' fs-5 me-2"></i>';
                html += '<div class="flex-grow-1">';
                html += '<div class="fw-semibold">' + event.title + '</div>';
                html += '<div class="small text-muted">' + event.time + '</div>';
                html += '</div>';
                html += '<button class="btn btn-sm btn-outline-danger delete-event-btn" data-date="' + dateStr + '" data-index="' + index + '"><i class="bi bi-trash"></i></button>';
                html += '</div>';
            });
            html += '</div>';

            meetingsSection.innerHTML = html;

            // Attach event listeners for delete buttons
            document.querySelectorAll('.delete-event-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const date = this.getAttribute('data-date');
                    const index = parseInt(this.getAttribute('data-index'));
                    deleteEvent(date, index);
                });
            });

            // Reattach event listener for add button
            document.getElementById('addMeeting').addEventListener('click', function() {
                addEventToDay(dateStr);
            });
        } else {
            meetingsSection.innerHTML = '<div class="d-flex justify-content-between align-items-center mb-3">' +
                '<h6 class="mb-0 fw-semibold">Events for ' + dateStr + '</h6>' +
                '<button class="btn btn-primary btn-sm btn-add-meeting" id="addMeeting">' +
                '<i class="bi bi-plus-lg"></i> Add Event' +
                '</button>' +
                '</div>' +
                '<div class="text-center text-muted py-4" id="noMeetings">' +
                '<i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>' +
                '<p class="mb-0 small">No events scheduled</p>' +
                '</div>';

            // Reattach event listener for add button
            document.getElementById('addMeeting').addEventListener('click', function() {
                addEventToDay(dateStr);
            });
        }
    }

    // Function to add event to a specific day
    function addEventToDay(dateStr) {
        const title = prompt('Enter event title:');
        if (!title) return;

        const time = prompt('Enter time (e.g., 10:00 AM):', '10:00 AM');
        if (!time) return;

        const type = prompt('Enter event type (meeting/call/review/deadline):', 'meeting');

        if (!events[dateStr]) {
            events[dateStr] = [];
        }

        events[dateStr].push({
            title: title,
            time: time,
            type: type.toLowerCase()
        });

        saveEvents();
        showEventsForDate(dateStr);
    }

    // Function to delete an event
    function deleteEvent(dateStr, index) {
        if (events[dateStr] && events[dateStr][index]) {
            if (confirm('Are you sure you want to delete this event?')) {
                events[dateStr].splice(index, 1);

                // Clean up empty arrays
                if (events[dateStr].length === 0) {
                    delete events[dateStr];
                }

                saveEvents();
                showEventsForDate(dateStr);
            }
        }
    }

    // Highlight today's date and show events
    const today = new Date();
    const todayStr = today.getFullYear() + '-' +
        String(today.getMonth() + 1).padStart(2, '0') + '-' +
        String(today.getDate()).padStart(2, '0');

    showEventsForDate(todayStr);

    // Add click event to calendar dates
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'TD' && !e.target.classList.contains('other-month')) {
            const day = e.target.textContent;
            const month = String(currentMonth + 1).padStart(2, '0');
            const year = currentYear;
            const dateStr = year + '-' + month + '-' + String(day).padStart(2, '0');

            showEventsForDate(dateStr);
        }
    });
</script>
<!-- Top Picks Clients Modal -->
<div class="modal fade" id="topPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">{{ __('messages.top_picks_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">{{ __('messages.business_partner') }}</th>
                                <th class="border-0 text-center">{{ __('messages.bp_code') }}</th>
                                <th class="border-0 text-center">Orders</th>
                                <th class="border-0 text-center">Process</th>
                                <th class="border-0 text-center">Approval</th>
                                <th class="border-0 text-center">Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksClientsFull as $code => $stat)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark">{{ $code }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success fs-6">{{ $stat['orders'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fs-6">{{ $stat['in_process']['count'] }} | {{ number_format($stat['in_process']['weight'], 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info fs-6">{{ $stat['for_approval']['count'] }} | {{ number_format($stat['for_approval']['weight'], 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger fs-6">{{ $stat['overdue']['count'] }} | {{ number_format($stat['overdue']['weight'], 2) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">{{ __('messages.no_data_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Clients Modal -->
<div class="modal fade" id="leastPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">{{ __('messages.least_pick_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">{{ __('messages.business_partner') }}</th>
                                <th class="border-0 text-center">{{ __('messages.bp_code') }}</th>
                                <th class="border-0 text-center">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leastPicksClientsFull as $code => $stat)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $stat['name'] }}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark">{{ $code }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary fs-6">{{ $stat['orders'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4">{{ __('messages.no_data_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection