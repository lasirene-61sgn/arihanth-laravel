@extends('super-admin.layouts.app')

@section('title', __('messages.view_craftman'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.view_craftman') }}</h1>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.craftman_details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('messages.basic_information') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('messages.craftman_code') }}</th>
                                    <td>{{ $craftman->craftman_code }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.business_name') }}</th>
                                    <td>{{ $craftman->business_name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.contact_person') }}</th>
                                    <td>{{ $craftman->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.mobile') }}</th>
                                    <td>{{ $craftman->mobile }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.landline') }}</th>
                                    <td>{{ $craftman->landline ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.email') }}</th>
                                    <td>{{ $craftman->email }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.business_email') }}</th>
                                    <td>{{ $craftman->business_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.referred_by') }}</th>
                                    <td>{{ $craftman->refered_by ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.additional_info') }}</th>
                                    <td>{{ $craftman->more ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>{{ __('messages.address_information') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('messages.door_number') }}</th>
                                    <td>{{ $craftman->door_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.shop_number') }}</th>
                                    <td>{{ $craftman->shop_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.complex_name') }}</th>
                                    <td>{{ $craftman->complex_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.building_name') }}</th>
                                    <td>{{ $craftman->building_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.street_name') }}</th>
                                    <td>{{ $craftman->street_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.area') }}</th>
                                    <td>{{ $craftman->area ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.pincode') }}</th>
                                    <td>{{ $craftman->pincode ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.city') }}</th>
                                    <td>{{ $craftman->city ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.state') }}</th>
                                    <td>{{ $craftman->state ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.map_location') }}</th>
                                    <td>{{ $craftman->map_location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.location_guide') }}</th>
                                    <td>{{ $craftman->location_guide ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>{{ __('messages.kyc_details') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('messages.aadhar_number') }}</th>
                                    <td>{{ $craftman->aadhar_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.gst_number') }}</th>
                                    <td>
                                        {{ $craftman->gst_no ?? 'N/A' }}
                                        @if($craftman->gst_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">{{ __('messages.view') }}</a>
                                            <a href="{{ asset('storage/' . $craftman->gst_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">{{ __('messages.download') }}</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.pan_number') }}</th>
                                    <td>
                                        {{ $craftman->pan_no ?? 'N/A' }}
                                        @if($craftman->pan_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->pan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">{{ __('messages.view') }}</a>
                                            <a href="{{ asset('storage/' . $craftman->pan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">{{ __('messages.download') }}</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.bis_number') }}</th>
                                    <td>
                                        {{ $craftman->bis_no ?? 'N/A' }}
                                        @if($craftman->bis_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->bis_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">{{ __('messages.view') }}</a>
                                            <a href="{{ asset('storage/' . $craftman->bis_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">{{ __('messages.download') }}</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.msme_number') }}</th>
                                    <td>
                                        {{ $craftman->msme_no ?? 'N/A' }}
                                        @if($craftman->msme_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->msme_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">{{ __('messages.view') }}</a>
                                            <a href="{{ asset('storage/' . $craftman->msme_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">{{ __('messages.download') }}</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.tan_number') }}</th>
                                    <td>
                                        {{ $craftman->tan_no ?? 'N/A' }}
                                        @if($craftman->tan_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->tan_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">{{ __('messages.view') }}</a>
                                            <a href="{{ asset('storage/' . $craftman->tan_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">{{ __('messages.download') }}</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.cin_number') }}</th>
                                    <td>
                                        {{ $craftman->cin_no ?? 'N/A' }}
                                        @if($craftman->cin_attachment)
                                            <br>
                                            <a href="{{ asset('storage/' . $craftman->cin_attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">{{ __('messages.view') }}</a>
                                            <a href="{{ asset('storage/' . $craftman->cin_attachment) }}" download class="btn btn-sm btn-outline-success mt-1">{{ __('messages.download') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
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
                                    @forelse($craftman->aadharDetails as $aadhar)
                                        <tr>
                                            <td>{{ $aadhar->aadhar_name }}</td>
                                            <td>{{ $aadhar->aadhar_number }}</td>
                                            <td>
                                                @if($aadhar->aadhar_image)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $aadhar->aadhar_image) }}" alt="Aadhar Image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                    </div>
                                                    <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                    <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">{{ __('messages.no_aadhar_details') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>{{ __('messages.pan_details') }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.number') }}</th>
                                        <th>{{ __('messages.image') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($craftman->panDetails as $pan)
                                        <tr>
                                            <td>{{ $pan->pan_number }}</td>
                                            <td>
                                                @if($pan->pan_image)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $pan->pan_image) }}" alt="PAN Image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                    </div>
                                                    <a href="{{ asset('storage/' . $pan->pan_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                    <a href="{{ asset('storage/' . $pan->pan_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">{{ __('messages.no_pan_details') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>{{ __('messages.bank_details') }}</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.bank_name') }}</th>
                                        <th>{{ __('messages.account_info') }}</th>
                                        <th>{{ __('messages.location') }}</th>
                                        <th>{{ __('messages.passbook_image') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($craftman->bankDetails as $bank)
                                        <tr>
                                            <td>{{ $bank->bank_name }}</td>
                                            <td>
                                                <strong>Name:</strong> {{ $bank->account_holder_name }}<br>
                                                <strong>Acc No:</strong> {{ $bank->account_number }}<br>
                                                <strong>IFSC:</strong> {{ $bank->ifsc_code }}
                                            </td>
                                            <td>
                                                <strong>Branch:</strong> {{ $bank->branch }}<br>
                                                <strong>City:</strong> {{ $bank->bank_city }}<br>
                                                <strong>State:</strong> {{ $bank->bank_state }}
                                            </td>
                                            <td>
                                                @if($bank->passbook_image)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $bank->passbook_image) }}" alt="Passbook Image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                    </div>
                                                    <a href="{{ asset('storage/' . $bank->passbook_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                    <a href="{{ asset('storage/' . $bank->passbook_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ __('messages.no_bank_details') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('messages.worker_details') }}</h5>
                            @if($craftman->workers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ __('messages.worker_name') }}</th>
                                                <th>{{ __('messages.worker_number') }}</th>
                                                <th>{{ __('messages.worker_image') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($craftman->workers as $worker)
                                                <tr>
                                                    <td>{{ $worker->worker_name }}</td>
                                                    <td>{{ $worker->worker_number ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($worker->worker_image)
                                                            <div class="mb-2">
                                                                <img src="{{ asset('storage/' . $worker->worker_image) }}" alt="Worker Image" style="max-height: 50px;" class="img-thumbnail">
                                                            </div>
                                                            <a href="{{ asset('storage/' . $worker->worker_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('messages.view') }}</a>
                                                            <a href="{{ asset('storage/' . $worker->worker_image) }}" download class="btn btn-sm btn-outline-success">{{ __('messages.download') }}</a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p>{{ __('messages.no_notes') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('messages.notes') }}</h5>
                            <p>{{ $craftman->note ?? 'No notes available.' }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('super-admin.business-partner.craftman.edit', $craftman) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> {{ __('messages.edit_craftman') }}
                        </a>

                        @if($craftman->kyc_status === 'approved')
                            <form action="{{ route('super-admin.business-partner.craftman.unlock', $craftman) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_unlock_craftman') }}')">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-unlock"></i> {{ __('messages.unlock_craftman') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('super-admin.business-partner.craftman.approve', $craftman) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_approve_craftman') }}')">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> {{ __('messages.approve_craftman') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('super-admin.business-partner.craftman') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection