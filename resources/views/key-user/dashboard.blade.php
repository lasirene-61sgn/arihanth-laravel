@extends('key-user.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    @if(Auth::guard('key_user')->check())
                        Key User Dashboard
                    @elseif(Auth::guard('buyer')->check())
                        Buyer Dashboard - {{ Auth::guard('buyer')->user()->name ?? Auth::guard('buyer')->user()->business_name }} ({{ Auth::guard('buyer')->user()->bp_code }})
                    @else
                        Dashboard
                    @endif
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $activeGuard = Auth::guard('key_user')->check() ? 'key_user' : (Auth::guard('buyer')->check() ? 'buyer' : null);
        $user = $activeGuard ? Auth::guard($activeGuard)->user() : null;
    @endphp
    <div class="row">
        @if($user && $user->hasPermission('product'))
        <!-- Products Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #78350f 0%, #b45309 100%); border:none; box-shadow:0 4px 15px rgba(120,53,15,0.3);">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Products</p>
                            <h4 class="mb-0 fw-bold">{{ $productsCount }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24" style="background:rgba(255,255,255,0.2);">
                                <i class="bi bi-box"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user && $user->hasPermission('work_order'))
        <!-- Work Orders Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #92400e 0%, #d97706 100%); border:none; box-shadow:0 4px 15px rgba(146,64,14,0.3);">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Work Orders</p>
                            <h4 class="mb-0 fw-bold">{{ $workOrdersCount }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24" style="background:rgba(255,255,255,0.2);">
                                <i class="bi bi-clipboard-check"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user && $user->hasPermission('design'))
        <!-- Designs Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #a16207 0%, #eab308 100%); border:none; box-shadow:0 4px 15px rgba(161,98,7,0.3);">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Designs</p>
                            <h4 class="mb-0 fw-bold">{{ $designsCount }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24" style="background:rgba(255,255,255,0.2);">
                                <i class="bi bi-pencil-square"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user && $user->hasPermission('catalogue'))
        <!-- Catalogue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #78350f 0%, #f59e0b 100%); border:none; box-shadow:0 4px 15px rgba(245,158,11,0.3);">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2 opacity-75">Total Catalogue</p>
                            <h4 class="mb-0 fw-bold">{{ $cataloguesCount }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle font-size-24" style="background:rgba(255,255,255,0.2);">
                                <i class="bi bi-journal-text"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection