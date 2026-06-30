@extends('super-admin.layouts.app')

@section('title', __('messages.business_partner_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.business_partner_management') }}</h1>
                <form action="{{ route('super-admin.business-partner.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('super-admin.business-partner.index') }}" class="btn btn-outline-secondary" title="Clear Search"><i class="bi bi-x-lg"></i></a>
                    @endif
                </form>
            </div>
            
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Buyers Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('messages.buyers') }}</h4>
                            <span class="badge bg-primary">{{ $buyers->count() }}</span>
                        </div>
                        <div class="card-body">
                            @if($buyers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('messages.bp_code') }}</th>
                                                <th>{{ __('messages.business_name') }}</th>
                                                <th>{{ __('messages.contact_person') }}</th>
                                                <th>{{ __('messages.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($buyers->take(5) as $buyer)
                                            <tr>
                                                <td>{{ $buyer->bp_code }}</td>
                                                <td>{{ $buyer->business_name }}</td>
                                                <td>{{ $buyer->name }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('super-admin.business-partner.buyer.show', $buyer) }}" 
                                                           class="btn btn-outline-info" title="View">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <!-- Removed edit button for super admin view only -->
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($buyers->count() > 5)
                                    <div class="text-center mt-3">
                                        <a href="{{ route('super-admin.business-partner.buyer') }}" class="btn btn-outline-primary">
                                            {{ __('messages.view_all') }} {{ __('messages.buyers') }} ({{ $buyers->count() }} total)
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-person-x" style="font-size: 2rem;"></i>
                                    <p class="mt-2">{{ __('messages.no_buyers_found') }}</p>
                                    <!-- Removed creation button for super admin view only -->
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Craftmen Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>{{ __('messages.craftsmen') }}</h4>
                            <span class="badge bg-success">{{ $craftmen->count() }}</span>
                        </div>
                        <div class="card-body">
                            @if($craftmen->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('messages.craftman_code') }}</th>
                                                <th>{{ __('messages.business_name') }}</th>
                                                <th>{{ __('messages.contact_person') }}</th>
                                                <th>{{ __('messages.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($craftmen->take(5) as $craftman)
                                            <tr>
                                                <td>{{ $craftman->craftman_code }}</td>
                                                <td>{{ $craftman->business_name }}</td>
                                                <td>{{ $craftman->name }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('super-admin.business-partner.craftman.show', $craftman) }}" 
                                                           class="btn btn-outline-info" title="View">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <!-- Removed edit button for super admin view only -->
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($craftmen->count() > 5)
                                    <div class="text-center mt-3">
                                        <a href="{{ route('super-admin.business-partner.craftman') }}" class="btn btn-outline-success">
                                            {{ __('messages.view_all') }} {{ __('messages.craftsmen') }} ({{ $craftmen->count() }} total)
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-person-workspace" style="font-size: 2rem;"></i>
                                    <p class="mt-2">{{ __('messages.no_craftsmen_found') }}</p>
                                    <!-- Removed creation button for super admin view only -->
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection