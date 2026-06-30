@extends('super-admin.layouts.app')

@section('title', __('messages.edit_contact_detail'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ __('messages.edit_contact_detail') }}</h1>
                <a href="{{ route('super-admin.company-contacts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('super-admin.company-contacts.update', $companyContact) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">{{ __('messages.contact_type') }} *</label>
                                <select name="type" id="type" class="form-select" required onchange="toggleFields()">
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ $companyContact->type == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">{{ __('messages.status') }}</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="active" value="1" {{ $companyContact->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">{{ __('messages.active') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0" {{ !$companyContact->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inactive">{{ __('messages.inactive') }}</label>
                                </div>
                            </div>
                        </div>

                        <div id="simple-field" class="row mb-3" {!! $companyContact->type == 'bank' ? 'style="display: none;"' : '' !!}>
                            <div class="col-12">
                                <label for="value" id="value-label" class="form-label">{{ $types[$companyContact->type] ?? 'Value' }} *</label>
                                <input type="text" name="value" id="value" class="form-control" value="{{ $companyContact->data['value'] ?? '' }}">
                            </div>
                        </div>

                        <div id="bank-fields" {!! $companyContact->type == 'bank' ? '' : 'style="display: none;"' !!}>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bank_name" class="form-label">{{ __('messages.bank_name') }} *</label>
                                    <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ $companyContact->data['bank_name'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="account_holder_name" class="form-label">{{ __('messages.account_holder_name') }} *</label>
                                    <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" value="{{ $companyContact->data['account_holder_name'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="account_number" class="form-label">{{ __('messages.account_number') }} *</label>
                                    <input type="text" name="account_number" id="account_number" class="form-control" value="{{ $companyContact->data['account_number'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="ifsc_code" class="form-label">{{ __('messages.ifsc_code') }} *</label>
                                    <input type="text" name="ifsc_code" id="ifsc_code" class="form-control" value="{{ $companyContact->data['ifsc_code'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="branch" class="form-label">{{ __('messages.branch') }}</label>
                                    <input type="text" name="branch" id="branch" class="form-control" value="{{ $companyContact->data['branch'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="bank_city" class="form-label">{{ __('messages.bank_city') }}</label>
                                    <input type="text" name="bank_city" id="bank_city" class="form-control" value="{{ $companyContact->data['bank_city'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="bank_state" class="form-label">{{ __('messages.bank_state') }}</label>
                                    <input type="text" name="bank_state" id="bank_state" class="form-control" value="{{ $companyContact->data['bank_state'] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
                            <a href="{{ route('super-admin.company-contacts.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('type').value;
        const simpleField = document.getElementById('simple-field');
        const bankFields = document.getElementById('bank-fields');
        const valueLabel = document.getElementById('value-label');
        const valueInput = document.getElementById('value');

        if (type === 'bank') {
            simpleField.style.display = 'none';
            bankFields.style.display = 'block';
            valueInput.removeAttribute('required');
            
            // Add required to bank fields
            document.getElementById('bank_name').setAttribute('required', 'required');
            document.getElementById('account_holder_name').setAttribute('required', 'required');
            document.getElementById('account_number').setAttribute('required', 'required');
            document.getElementById('ifsc_code').setAttribute('required', 'required');
        } else {
            simpleField.style.display = 'block';
            bankFields.style.display = 'none';
            valueInput.setAttribute('required', 'required');
            
            // Remove required from bank fields
            document.getElementById('bank_name').removeAttribute('required');
            document.getElementById('account_holder_name').removeAttribute('required');
            document.getElementById('account_number').removeAttribute('required');
            document.getElementById('ifsc_code').removeAttribute('required');

            // Update label based on type
            const labels = {!! json_encode(\App\Models\CompanyContact::getTypes()) !!};
            valueLabel.innerText = (labels[type] || 'Value') + ' *';
        }
    }
    
    // Call on load to set initial state correctly
    document.addEventListener('DOMContentLoaded', toggleFields);
</script>
@endsection
