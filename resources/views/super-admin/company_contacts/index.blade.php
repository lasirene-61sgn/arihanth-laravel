@extends('super-admin.layouts.app')

@section('title', __('messages.company_contact_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.company_contact_management') }}</h1>
                <a href="{{ route('super-admin.company-contacts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> {{ __('messages.add_contact_detail') }}
                </a>
            </div>
            
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.contact_type') }}</th>
                                    <th>{{ __('messages.details') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contacts as $contact)
                                <tr>
                                    <td>{{ \App\Models\CompanyContact::getTypes()[$contact->type] ?? $contact->type }}</td>
                                    <td>
                                        @if($contact->type == 'bank')
                                            <strong>{{ $contact->data['bank_name'] ?? '' }}</strong><br>
                                            <small>{{ $contact->data['account_number'] ?? '' }} | {{ $contact->data['ifsc_code'] ?? '' }}</small>
                                        @else
                                            {{ $contact->data['value'] ?? '' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($contact->is_active)
                                            <span class="badge bg-success">{{ __('messages.active') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('super-admin.company-contacts.show', $contact) }}" class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('super-admin.company-contacts.edit', $contact) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('super-admin.company-contacts.destroy', $contact) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this contact detail?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('messages.no_records_found') }}</td>
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
