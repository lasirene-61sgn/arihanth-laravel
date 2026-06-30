@extends('buyer.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-slate-900">My Profile</h1>
        <p class="text-slate-500">Manage your personal and business information.</p>
    </div>

    <!-- Alerts Section -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center justify-between animate-in fade-in slide-in-from-top-2">
            <div class="flex items-center">
                <i class="bi bi-check-circle-fill mr-3 text-emerald-500"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center justify-between animate-in fade-in slide-in-from-top-2">
            <div class="flex items-center">
                <i class="bi bi-exclamation-triangle-fill mr-3 text-red-500"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg animate-in fade-in slide-in-from-top-2">
            <div class="flex items-start">
                <i class="bi bi-exclamation-circle-fill mr-3 mt-0.5 text-red-500"></i>
                <ul class="text-sm font-medium list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($isReadOnly)
        <div class="p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg flex items-start animate-in fade-in">
            <i class="bi bi-info-circle-fill mr-3 mt-0.5 text-blue-500 text-lg"></i>
            <p class="text-sm font-medium leading-relaxed">
                Your profile has been approved by the administrator and is now read-only. To make changes, please contact support.
            </p>
        </div>
    @endif

    <form action="{{ route('buyer.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Profile Card -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="font-bold text-slate-800">Profile Picture</h3>
                        @if($buyer->kyc_status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <i class="bi bi-check-circle mr-1"></i> VERIFIED
                            </span>
                        @elseif($buyer->kyc_status === 'rejected')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100">
                                <i class="bi bi-x-circle mr-1"></i> REJECTED
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                <i class="bi bi-hourglass-split mr-1"></i> PENDING
                            </span>
                        @endif
                    </div>
                    <div class="p-8 text-center">
                        <div class="relative inline-block mb-6 group">
                            <img class="w-32 h-32 rounded-full object-cover border-4 border-slate-50 shadow-md transition-transform duration-300 group-hover:scale-105" 
                                 src="{{ $buyer->image ? asset('storage/' . $buyer->image) : 'https://ui-avatars.com/api/?name=' . urlencode($buyer->name) . '&background=random' }}" 
                                 id="preview-image" alt="Profile Image">
                            
                            @if(!$isReadOnly)
                            <label for="image" class="absolute bottom-0 right-0 p-2 bg-blue-600 text-white rounded-full cursor-pointer shadow-lg hover:bg-blue-700 transition-colors">
                                <i class="bi bi-camera-fill text-sm"></i>
                                <input type="file" class="hidden" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            </label>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mb-1">{{ $buyer->name }}</h2>
                        <p class="text-slate-500 text-sm mb-6">{{ $buyer->business_name }}</p>
                        
                        <div class="space-y-4 text-left border-t border-slate-100 pt-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Buyer ID</label>
                                <p class="font-semibold text-slate-800">{{ $buyer->bp_code }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Email</label>
                                <p class="text-slate-600 text-sm truncate">{{ $buyer->email }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Mobile</label>
                                <p class="text-slate-600 text-sm">{{ $buyer->mobile }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details Tabs -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col min-h-[600px]">
                    <div class="border-b border-slate-200 bg-slate-50/30">
                        <nav class="flex px-6 overflow-x-auto" id="profileTabs">
                            @php
                                $tabs = [
                                    'basic' => 'Basic Info',
                                    'address' => 'Address',
                                    'kyc' => 'KYC Documents',
                                    'bank' => 'Bank Details'
                                ];
                            @endphp
                            @foreach($tabs as $id => $label)
                                <button type="button" 
                                    data-tab-target="{{ $id }}"
                                    class="tab-btn whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-all duration-200 {{ $id === 'basic' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    <div class="p-6 md:p-8 flex-1">
                        <div id="profileTabsContent">
                            <!-- Basic Info Tab -->
                            <div id="basic" class="tab-pane animate-in fade-in">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="name" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Contact Person Name <span class="text-red-500">*</span></label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="name" name="name" value="{{ old('name', $buyer->name) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="business_name" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Business Name <span class="text-red-500">*</span></label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="business_name" name="business_name" value="{{ old('business_name', $buyer->business_name) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="mobile" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Mobile Number <span class="text-red-500">*</span></label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="mobile" name="mobile" value="{{ old('mobile', $buyer->mobile) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="email" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Email Address <span class="text-red-500">*</span></label>
                                        <input type="email" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="email" name="email" value="{{ old('email', $buyer->email) }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="landline" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Landline</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="landline" name="landline" value="{{ old('landline', $buyer->landline) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="business_email" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Business Email</label>
                                        <input type="email" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="business_email" name="business_email" value="{{ old('business_email', $buyer->business_email) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-span-full space-y-2">
                                        <label for="more" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Additional Information</label>
                                        <textarea class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="more" name="more" rows="3" {{ $isReadOnly ? 'disabled' : '' }}>{{ old('more', $buyer->more) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Tab -->
                            <div id="address" class="tab-pane hidden animate-in fade-in">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="door_no" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Door No</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="door_no" name="door_no" value="{{ old('door_no', $buyer->door_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="shop_no" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Shop No</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="shop_no" name="shop_no" value="{{ old('shop_no', $buyer->shop_no) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="complex_name" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Complex Name</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="complex_name" name="complex_name" value="{{ old('complex_name', $buyer->complex_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="building_name" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Building Name</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="building_name" name="building_name" value="{{ old('building_name', $buyer->building_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="street_name" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Street Name</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="street_name" name="street_name" value="{{ old('street_name', $buyer->street_name) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="area" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Area</label>
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="area" name="area" value="{{ old('area', $buyer->area) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 col-span-full">
                                        <div class="space-y-2">
                                            <label for="city" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">City</label>
                                            <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="city" name="city" value="{{ old('city', $buyer->city) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="state" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">State</label>
                                            <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="state" name="state" value="{{ old('state', $buyer->state) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="pincode" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Pincode</label>
                                            <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="pincode" name="pincode" value="{{ old('pincode', $buyer->pincode) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-span-full space-y-2">
                                        <label for="map_location" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Map Location (URL)</label>
                                        <div class="flex gap-2">
                                            <div class="relative flex-1">
                                                <i class="bi bi-geo-alt-fill absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                <input type="text" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="map_location" name="map_location" value="{{ old('map_location', $buyer->map_location) }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                            </div>
                                            @if($buyer->map_location)
                                                <a href="{{ $buyer->map_location }}" target="_blank" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors flex items-center">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-span-full space-y-2">
                                        <label for="location_guide" class="block text-sm font-semibold text-slate-700 uppercase tracking-wider">Location Guide</label>
                                        <textarea class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ $isReadOnly ? 'bg-slate-50 text-slate-500' : 'text-slate-900' }}" id="location_guide" name="location_guide" rows="2" {{ $isReadOnly ? 'disabled' : '' }}>{{ old('location_guide', $buyer->location_guide) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- KYC Documents Tab -->
                            <div id="kyc" class="tab-pane hidden animate-in fade-in">
                                <div class="space-y-8">
                                    <!-- Aadhar Section -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                            <h4 class="font-bold text-slate-800 flex items-center">
                                                <i class="bi bi-card-text mr-2 text-blue-500"></i> Aadhar Details
                                            </h4>
                                            @if(!$isReadOnly)
                                            <button type="button" onclick="addAadharField()" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center bg-blue-50 px-2 py-1 rounded">
                                                <i class="bi bi-plus-lg mr-1"></i> ADD NEW
                                            </button>
                                            @endif
                                        </div>
                                        <div id="aadhar-container" class="space-y-4">
                                            @if($buyer->aadharDetails && $buyer->aadharDetails->count() > 0)
                                                @foreach($buyer->aadharDetails as $index => $aadhar)
                                                <div class="aadhar-entry p-4 bg-slate-50 rounded-xl border border-slate-200 relative group">
                                                    @if(!$isReadOnly && $index > 0)
                                                    <button type="button" onclick="removeField(this)" class="absolute top-2 right-2 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all opacity-0 group-hover:opacity-100">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    @endif
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Name on Aadhar</label>
                                                            <input type="text" name="aadhar_name[]" value="{{ $aadhar->aadhar_name }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aadhar Number</label>
                                                            <input type="text" name="aadhar_number[]" value="{{ $aadhar->aadhar_number }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="col-span-full space-y-2">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aadhar Image</label>
                                                            <div class="flex flex-wrap items-center gap-3">
                                                                @if($aadhar->aadhar_image)
                                                                    <a href="{{ asset('storage/' . $aadhar->aadhar_image) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                                                                        <i class="bi bi-file-earmark-image mr-2"></i> VIEW CURRENT
                                                                    </a>
                                                                    <input type="hidden" name="existing_aadhar_image[]" value="{{ $aadhar->aadhar_image }}">
                                                                @endif
                                                                @if(!$isReadOnly)
                                                                <input type="file" name="aadhar_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @else
                                                <div class="aadhar-entry p-4 bg-slate-50 rounded-xl border border-slate-200 relative">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Name on Aadhar</label>
                                                            <input type="text" name="aadhar_name[]" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aadhar Number</label>
                                                            <input type="text" name="aadhar_number[]" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="col-span-full space-y-2">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aadhar Image</label>
                                                            <input type="file" name="aadhar_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- PAN Section -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                            <h4 class="font-bold text-slate-800 flex items-center">
                                                <i class="bi bi-credit-card-2-front mr-2 text-indigo-500"></i> PAN Details
                                            </h4>
                                            @if(!$isReadOnly)
                                            <button type="button" onclick="addPanField()" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center bg-indigo-50 px-2 py-1 rounded">
                                                <i class="bi bi-plus-lg mr-1"></i> ADD NEW
                                            </button>
                                            @endif
                                        </div>
                                        <div id="pan-container" class="space-y-4">
                                            @if($buyer->panDetails && $buyer->panDetails->count() > 0)
                                                @foreach($buyer->panDetails as $index => $pan)
                                                <div class="pan-entry p-4 bg-slate-50 rounded-xl border border-slate-200 relative group">
                                                    @if(!$isReadOnly && $index > 0)
                                                    <button type="button" onclick="removeField(this)" class="absolute top-2 right-2 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all opacity-0 group-hover:opacity-100">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    @endif
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PAN Number</label>
                                                            <input type="text" name="pan_number[]" value="{{ $pan->pan_number }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PAN Image</label>
                                                            <div class="flex flex-wrap items-center gap-3">
                                                                @if($pan->pan_image)
                                                                    <a href="{{ asset('storage/' . $pan->pan_image) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100 hover:bg-indigo-100 transition-colors">
                                                                        <i class="bi bi-file-earmark-image mr-2"></i> VIEW CURRENT
                                                                    </a>
                                                                    <input type="hidden" name="existing_pan_image[]" value="{{ $pan->pan_image }}">
                                                                @endif
                                                                @if(!$isReadOnly)
                                                                <input type="file" name="pan_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @else
                                                <div class="pan-entry p-4 bg-slate-50 rounded-xl border border-slate-200">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PAN Number</label>
                                                            <input type="text" name="pan_number[]" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm" {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="space-y-1.5">
                                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PAN Image</label>
                                                            <input type="file" name="pan_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all" {{ $isReadOnly ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Business Documents Section -->
                                    <div class="space-y-4">
                                        <h4 class="font-bold text-slate-800 flex items-center border-b border-slate-100 pb-2">
                                            <i class="bi bi-briefcase mr-2 text-emerald-500"></i> Business Certificates
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                            <!-- GST -->
                                            <div class="space-y-2">
                                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">GST Number</label>
                                                <div class="relative group">
                                                    <i class="bi bi-lock-fill absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                                    <input type="text" class="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 text-sm font-medium italic" value="{{ $buyer->gst_no }}" readonly>
                                                    <div class="absolute inset-y-0 right-3 flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <span class="text-[10px] text-slate-400 font-bold uppercase">Locked</span>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    @if($buyer->gst_attachment)
                                                        <a href="{{ asset('storage/' . $buyer->gst_attachment) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 w-full justify-center transition-colors">
                                                            <i class="bi bi-file-earmark-pdf mr-2"></i> VIEW GST CERTIFICATE
                                                        </a>
                                                    @endif
                                                    @if(!$isReadOnly)
                                                    <div class="mt-2">
                                                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Update Certificate</label>
                                                        <input type="file" name="gst_attachment" accept="image/*,.pdf" class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition-all">
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- BIS -->
                                            <div class="space-y-2 text-center md:text-left">
                                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">BIS Certificate</label>
                                                <div class="space-y-3">
                                                    @if($buyer->bis_attachment)
                                                        <a href="{{ asset('storage/' . $buyer->bis_attachment) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 w-full justify-center transition-colors">
                                                            <i class="bi bi-file-earmark-check mr-2"></i> VIEW BIS CERTIFICATE
                                                        </a>
                                                    @endif
                                                    <div class="mt-auto">
                                                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Update BIS</label>
                                                        <input type="file" name="bis_attachment" accept="image/*,.pdf" class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition-all" {{ $isReadOnly ? 'disabled' : '' }}>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- MSME -->
                                            <div class="space-y-2 text-center md:text-left">
                                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">MSME Certificate</label>
                                                <div class="space-y-3">
                                                    @if($buyer->msme_attachment)
                                                        <a href="{{ asset('storage/' . $buyer->msme_attachment) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 w-full justify-center transition-colors">
                                                            <i class="bi bi-file-earmark-text mr-2"></i> VIEW MSME CERTIFICATE
                                                        </a>
                                                    @endif
                                                    <div class="mt-auto">
                                                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Update MSME</label>
                                                        <input type="file" name="msme_attachment" accept="image/*,.pdf" class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition-all" {{ $isReadOnly ? 'disabled' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Details Tab -->
                            <div id="bank" class="tab-pane hidden animate-in fade-in">
                                <div id="bank-container" class="space-y-6">
                                    @if($buyer->bankDetails && $buyer->bankDetails->count() > 0)
                                        @foreach($buyer->bankDetails as $index => $bank)
                                        <div class="bank-entry p-6 bg-slate-50 rounded-xl border border-slate-200 relative group">
                                            @if(!$isReadOnly && $index > 0)
                                            <button type="button" onclick="removeField(this)" class="absolute top-3 right-3 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all opacity-0 group-hover:opacity-100">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bank Name</label>
                                                    <input type="text" name="bank_name_detail[]" value="{{ $bank->bank_name }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account Holder Name</label>
                                                    <input type="text" name="account_holder_name[]" value="{{ $bank->account_holder_name }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account Number</label>
                                                    <input type="text" name="account_number[]" value="{{ $bank->account_number }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">IFSC Code</label>
                                                    <input type="text" name="ifsc_code_detail[]" value="{{ $bank->ifsc_code }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Branch Name</label>
                                                    <input type="text" name="branch_name[]" value="{{ $bank->branch_name }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm {{ $isReadOnly ? 'bg-white' : '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Passbook Image</label>
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        @if($bank->passbook_image)
                                                            <a href="{{ asset('storage/' . $bank->passbook_image) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                                                                <i class="bi bi-file-earmark-image mr-2"></i> VIEW CURRENT
                                                            </a>
                                                            <input type="hidden" name="existing_passbook_image[]" value="{{ $bank->passbook_image }}">
                                                        @endif
                                                        @if(!$isReadOnly)
                                                        <input type="file" name="passbook_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="bank-entry p-6 bg-slate-50 rounded-xl border border-slate-200">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bank Name</label>
                                                    <input type="text" name="bank_name_detail[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account Holder Name</label>
                                                    <input type="text" name="account_holder_name[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account Number</label>
                                                    <input type="text" name="account_number[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">IFSC Code</label>
                                                    <input type="text" name="ifsc_code_detail[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Branch Name</label>
                                                    <input type="text" name="branch_name[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Passbook Image</label>
                                                    <input type="file" name="passbook_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" {{ $isReadOnly ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if(!$isReadOnly)
                                <div class="mt-6">
                                    <button type="button" onclick="addBankField()" class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                        <i class="bi bi-plus-lg mr-2"></i> ADD ANOTHER BANK ACCOUNT
                                    </button>
                                </div>
                                @endif
                            </div>

                        </div>
                    </div>
                    
                     @if(!$isReadOnly)
                    <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                        <button type="reset" class="px-6 py-2.5 text-slate-600 font-bold text-sm hover:text-slate-800 transition-colors">
                            RESET CHANGES
                        </button>
                        <button type="submit" class="inline-flex items-center px-8 py-2.5 bg-blue-600 text-white font-bold rounded-lg shadow-md shadow-blue-200 hover:bg-blue-700 transition-all active:scale-[0.98]">
                            <i class="bi bi-save mr-2"></i> SAVE & UPDATE PROFILE
                        </button>
                    </div>
                    @else
                     <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 text-center animate-pulse">
                        <span class="text-red-600 font-bold flex items-center justify-center text-sm tracking-wide">
                            <i class="bi bi-lock-fill mr-2"></i> PROFILE UPDATES DISABLED (KYC APPROVED)
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const isReadOnly = {{ $isReadOnly ? 'true' : 'false' }};

    // Tab Switching Logic
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-tab-target');

                // Update buttons
                tabBtns.forEach(b => {
                    b.classList.remove('border-blue-600', 'text-blue-600');
                    b.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');
                });
                btn.classList.add('border-blue-600', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');

                // Update panes
                tabPanes.forEach(pane => {
                    if (pane.id === target) {
                        pane.classList.remove('hidden');
                    } else {
                        pane.classList.add('hidden');
                    }
                });
            });
        });
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function addAadharField() {
        if(isReadOnly) return;
        const container = document.getElementById('aadhar-container');
        const entry = document.createElement('div');
        entry.className = 'aadhar-entry p-4 bg-slate-50 rounded-xl border border-slate-200 relative group animate-in zoom-in-95 duration-200';
        entry.innerHTML = `
            <button type="button" onclick="removeField(this)" class="absolute top-2 right-2 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all group-hover:opacity-100">
                <i class="bi bi-trash"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Name on Aadhar</label>
                    <input type="text" name="aadhar_name[]" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aadhar Number</label>
                    <input type="text" name="aadhar_number[]" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required>
                </div>
                <div class="col-span-full space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aadhar Image</label>
                    <input type="file" name="aadhar_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                </div>
            </div>
        `;
        container.appendChild(entry);
    }

    function addPanField() {
        if(isReadOnly) return;
        const container = document.getElementById('pan-container');
        const entry = document.createElement('div');
        entry.className = 'pan-entry p-4 bg-slate-50 rounded-xl border border-slate-200 relative group animate-in zoom-in-95 duration-200';
        entry.innerHTML = `
            <button type="button" onclick="removeField(this)" class="absolute top-2 right-2 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all group-hover:opacity-100">
                <i class="bi bi-trash"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PAN Number</label>
                    <input type="text" name="pan_number[]" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PAN Image</label>
                    <input type="file" name="pan_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
                </div>
            </div>
        `;
        container.appendChild(entry);
    }

    function addBankField() {
        if(isReadOnly) return;
        const container = document.getElementById('bank-container');
        const entry = document.createElement('div');
        entry.className = 'bank-entry p-6 bg-slate-50 rounded-xl border border-slate-200 relative group animate-in zoom-in-95 duration-200';
        entry.innerHTML = `
            <button type="button" onclick="removeField(this)" class="absolute top-3 right-3 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all group-hover:opacity-100">
                <i class="bi bi-trash"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bank Name</label>
                    <input type="text" name="bank_name_detail[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account Holder Name</label>
                    <input type="text" name="account_holder_name[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account Number</label>
                    <input type="text" name="account_number[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">IFSC Code</label>
                    <input type="text" name="ifsc_code_detail[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Branch Name</label>
                    <input type="text" name="branch_name[]" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Passbook Image</label>
                    <input type="file" name="passbook_image[]" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                </div>
            </div>
        `;
        container.appendChild(entry);
    }

    function removeField(button) {
        if(isReadOnly) return;
        const entry = button.closest('.aadhar-entry, .pan-entry, .bank-entry');
        if (entry) {
            entry.classList.add('zoom-out-95', 'opacity-0');
            setTimeout(() => entry.remove(), 200);
        }
    }
</script>
@endsection
