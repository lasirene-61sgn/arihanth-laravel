@extends('super-admin.layouts.app')

@section('title', __('messages.view') . ' ' . __('messages.contact_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.contact_details') }}</h1>
                <a href="{{ route('super-admin.company-contacts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                </a>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4>{{ \App\Models\CompanyContact::getTypes()[$companyContact->type] ?? $companyContact->type }}</h4>
                    <div>
                        @if($companyContact->is_active)
                            <span class="badge bg-success">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            @if($companyContact->type == 'bank')
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ __('messages.bank_name') }}</th>
                                        <td>{{ $companyContact->data['bank_name'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.account_holder_name') }}</th>
                                        <td>{{ $companyContact->data['account_holder_name'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.account_number') }}</th>
                                        <td>{{ $companyContact->data['account_number'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.ifsc_code') }}</th>
                                        <td>{{ $companyContact->data['ifsc_code'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.branch') }}</th>
                                        <td>{{ $companyContact->data['branch'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.bank_city') }}</th>
                                        <td>{{ $companyContact->data['bank_city'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.bank_state') }}</th>
                                        <td>{{ $companyContact->data['bank_state'] ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            @else
                                <div class="mb-4">
                                    <label class="form-label fw-bold">{{ \App\Models\CompanyContact::getTypes()[$companyContact->type] ?? 'Value' }}</label>
                                    <div class="p-3 bg-light border rounded">
                                        {{ $companyContact->data['value'] ?? 'N/A' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('super-admin.company-contacts.edit', $companyContact) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> {{ __('messages.edit') }}
                        </a>
                        <form action="{{ route('super-admin.company-contacts.destroy', $companyContact) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this contact detail?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> {{ __('messages.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
