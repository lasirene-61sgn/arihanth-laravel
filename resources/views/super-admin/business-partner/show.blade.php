@extends('super-admin.layouts.app')

@section('title', __('messages.view_buyer'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.view_buyer') }}</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-info" onclick="window.print();">
                        <i class="bi bi-printer"></i> {{ __('messages.print') }}
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.buyer_details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('messages.basic_information') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('messages.bp_code') }}</th>
                                    <td>{{ $buyer->bp_code }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.business_name') }}</th>
                                    <td>{{ $buyer->business_name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.contact_person') }}</th>
                                    <td>{{ $buyer->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.mobile') }}</th>
                                    <td>{{ $buyer->mobile }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.landline') }}</th>
                                    <td>{{ $buyer->landline ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.email') }}</th>
                                    <td>{{ $buyer->email }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.business_email') }}</th>
                                    <td>{{ $buyer->business_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.referred_by') }}</th>
                                    <td>{{ $buyer->refered_by ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.additional_information') }}</th>
                                    <td>{{ $buyer->more ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>{{ __('messages.address_information') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('messages.door_number') }}</th>
                                    <td>{{ $buyer->door_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.shop_number') }}</th>
                                    <td>{{ $buyer->shop_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.complex_name') }}</th>
                                    <td>{{ $buyer->complex_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.building_name') }}</th>
                                    <td>{{ $buyer->building_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.street_name') }}</th>
                                    <td>{{ $buyer->street_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.area') }}</th>
                                    <td>{{ $buyer->area ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.pincode') }}</th>
                                    <td>{{ $buyer->pincode ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.city') }}</th>
                                    <td>{{ $buyer->city ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.state') }}</th>
                                    <td>{{ $buyer->state ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.map_location') }}</th>
                                    <td>{{ $buyer->map_location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.location_guide') }}</th>
                                    <td>{{ $buyer->location_guide ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>{{ __('messages.kyc_details') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('messages.aadhar_number_required') }}</th>
                                    <td>{{ $buyer->aadhar_no ?? 'N/A' }}</td>
                                    <th>{{ __('messages.gst_number') }}</th>
                                    <td>
                                        {{ $buyer->gst_no ?? 'N/A' }}
                                        @if($buyer->gst_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                            <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.pan_number') }}</th>
                                    <td>{{ $buyer->pan_no ?? 'N/A' }}</td>
                                    <th>{{ __('messages.bis_number') }}</th>
                                    <td>
                                        {{ $buyer->bis_no ?? 'N/A' }}
                                        @if($buyer->bis_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                            <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.msme_number') }}</th>
                                    <td>
                                        {{ $buyer->msme_no ?? 'N/A' }}
                                        @if($buyer->msme_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                            <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                        @endif
                                    </td>
                                    <th>{{ __('messages.tan_number') }}</th>
                                    <td>
                                        {{ $buyer->tan_no ?? 'N/A' }}
                                        @if($buyer->tan_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->tan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                            <a href="{{ asset('storage/' . $buyer->tan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.cin_number') }}</th>
                                    <td>
                                        {{ $buyer->cin_no ?? 'N/A' }}
                                        @if($buyer->cin_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $buyer->cin_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                            <a href="{{ asset('storage/' . $buyer->cin_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">Download</a>
                                        @endif
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Aadhar Details Section -->
                    @if($buyer->aadharDetails && $buyer->aadharDetails->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>{{ __('messages.aadhar_details') }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.number') }}</th>
                                        <th>{{ __('messages.image') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($buyer->aadharDetails as $aadharDetail)
                                    <tr>
                                        <td>{{ $aadharDetail->aadhar_name }}</td>
                                        <td>{{ $aadharDetail->aadhar_number }}</td>
                                        <td>
                                            @if($aadharDetail->aadhar_image)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $aadharDetail->aadhar_image) }}" alt="Aadhar Image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                </div>
                                                <a href="{{ asset('storage/' . $aadharDetail->aadhar_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                <a href="{{ asset('storage/' . $aadharDetail->aadhar_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <!-- PAN Details Section -->
                    @if($buyer->panDetails && $buyer->panDetails->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>{{ __('messages.pan_details') }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.pan_number') }}</th>
                                        <th>{{ __('messages.image') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($buyer->panDetails as $panDetail)
                                    <tr>
                                        <td>{{ $panDetail->pan_number }}</td>
                                        <td>
                                            @if($panDetail->pan_image)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $panDetail->pan_image) }}" alt="PAN Image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                </div>
                                                <a href="{{ asset('storage/' . $panDetail->pan_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                <a href="{{ asset('storage/' . $panDetail->pan_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Bank Details Section -->
                    @if($buyer->bankDetails && $buyer->bankDetails->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>{{ __('messages.bank_details') }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.bank_name') }}</th>
                                        <th>{{ __('messages.account_holder_name') }}</th>
                                        <th>{{ __('messages.account_number') }}</th>
                                        <th>{{ __('messages.ifsc_code') }}</th>
                                        <th>{{ __('messages.branch') }}</th>
                                        <th>{{ __('messages.passbook_cheque_image') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($buyer->bankDetails as $bankDetail)
                                    <tr>
                                        <td>{{ $bankDetail->bank_name }}</td>
                                        <td>{{ $bankDetail->account_holder_name }}</td>
                                        <td>{{ $bankDetail->account_number }}</td>
                                        <td>{{ $bankDetail->ifsc_code }}</td>
                                        <td>{{ $bankDetail->branch }}</td>
                                        <td>
                                            @if($bankDetail->passbook_image)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $bankDetail->passbook_image) }}" alt="Passbook Image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                </div>
                                                <a href="{{ asset('storage/' . $bankDetail->passbook_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                <a href="{{ asset('storage/' . $bankDetail->passbook_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('messages.notes') }}</h5>
                            <p>{{ $buyer->note ?? __('messages.no_notes') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('super-admin.business-partner.buyer.edit', $buyer) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> {{ __('messages.edit_buyer') }}
                        </a>
                        <a href="{{ route('super-admin.business-partner.buyer.print', $buyer) }}" class="btn btn-info" target="_blank">
                            <i class="bi bi-printer"></i> {{ __('messages.print_view') }}
                        </a>

                        @if($buyer->kyc_status !== 'approved')
                            <form action="{{ route('super-admin.business-partner.buyer.approve', $buyer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_approve_buyer') }}')">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> {{ __('messages.approve_kyc') }}
                                </button>
                            </form>
                        @else
                            <button class="btn btn-success" disabled>
                                <i class="bi bi-check-circle-fill"></i> {{ __('messages.kyc_approved') }}
                            </button>
                            
                            <form action="{{ route('super-admin.business-partner.buyer.unlock', $buyer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_unlock_profile') }}')">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-unlock"></i> {{ __('messages.unlock_profile') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('super-admin.business-partner.buyer') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>{{ __('messages.kyc_status') }}: </strong> 
                    @if($buyer->kyc_status === 'approved')
                        <span class="badge bg-success">{{ __('messages.approved') }}</span>
                    @elseif($buyer->kyc_status === 'rejected')
                        <span class="badge bg-danger">{{ __('messages.rejected') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">{{ __('messages.pending') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .container-fluid,
    .container-fluid * {
        visibility: visible;
    }
    .container-fluid {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn-toolbar,
    .btn {
        display: none;
    }
    .table {
        font-size: 12px;
    }
    img {
        max-width: 80px;
        max-height: 80px;
    }
}
</style>
@endsection