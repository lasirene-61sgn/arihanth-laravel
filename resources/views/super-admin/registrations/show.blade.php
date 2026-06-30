@extends('super-admin.layouts.app')

@section('content')
<div class="tw-container tw-mx-auto">
    <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
        <a href="{{ route('super-admin.registrations.index') }}" class="tw-p-2 tw-bg-white dark:tw-bg-slate-900 tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-100 dark:tw-border-slate-800 tw-text-gray-400 hover:tw-text-indigo-600 tw-transition-all">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="tw-text-2xl tw-font-black tw-text-gray-900 dark:tw-text-white tw-uppercase tw-tracking-tight">
                Registration Details
            </h2>
            <p class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Request from {{ $registration->business_name }}</p>
        </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
        <!-- Details Section -->
        <div class="lg:tw-col-span-2 tw-space-y-8">
            <div class="tw-bg-white dark:tw-bg-slate-900 tw-p-8 tw-rounded-3xl tw-shadow-xl tw-border tw-border-gray-100 dark:tw-border-slate-800">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
                    <!-- Contact Information -->
                    <div class="tw-space-y-6">
                        <h3 class="tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-border-b dark:tw-border-slate-800 tw-pb-2">Contact Details</h3>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Full Name</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->name }}</p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Email Address</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->email }}</p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Mobile Number</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->mobile }}</p>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="tw-space-y-6">
                        <h3 class="tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-border-b dark:tw-border-slate-800 tw-pb-2">Business Details</h3>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Business Name</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->business_name }}</p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Location</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->city }}, {{ $registration->state }} - {{ $registration->pincode }}</p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Full Address</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->address ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="tw-mt-10 tw-pt-10 tw-border-t dark:tw-border-slate-800">
                    <h3 class="tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-mb-6">Security & Documents</h3>
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-w-8 tw-h-8 tw-bg-emerald-100 tw-text-emerald-600 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Password Status</label>
                                <p class="tw-text-sm tw-font-bold tw-text-emerald-600">User Defined & Secured</p>
                            </div>
                        </div>
                        <div>
                            <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">GST Number</label>
                            <p class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $registration->gst_no ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Form Section -->
        <div class="tw-space-y-8">
            <div class="tw-bg-white dark:tw-bg-slate-900 tw-p-8 tw-rounded-3xl tw-shadow-xl tw-border tw-border-gray-100 dark:tw-border-slate-800">
                <h3 class="tw-text-lg tw-font-black tw-text-gray-900 dark:tw-text-white tw-uppercase tw-tracking-tight tw-mb-6">Action Required</h3>
                
                @if($registration->status == 'Pending')
                <form action="{{ route('super-admin.registrations.approve', $registration->id) }}" method="POST" class="tw-space-y-6">
                    @csrf
                    <div>
                        <label class="tw-block tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-mb-3">Assign Role</label>
                        <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                            <label class="tw-relative tw-cursor-pointer">
                                <input type="radio" name="type" value="Buyer" class="tw-peer tw-sr-only" checked>
                                <div class="tw-p-4 tw-rounded-2xl tw-border-2 tw-border-gray-100 dark:tw-border-slate-800 tw-text-center peer-checked:tw-border-indigo-600 peer-checked:tw-bg-indigo-50/50 dark:peer-checked:tw-bg-indigo-900/20 tw-transition-all">
                                    <i class="bi bi-person-check tw-text-xl tw-block tw-mb-1"></i>
                                    <span class="tw-text-xs tw-font-black tw-uppercase tw-tracking-widest">Buyer</span>
                                </div>
                            </label>
                            <label class="tw-relative tw-cursor-pointer">
                                <input type="radio" name="type" value="Craftsman" class="tw-peer tw-sr-only">
                                <div class="tw-p-4 tw-rounded-2xl tw-border-2 tw-border-gray-100 dark:tw-border-slate-800 tw-text-center peer-checked:tw-border-amber-600 peer-checked:tw-bg-amber-50/50 dark:peer-checked:tw-bg-amber-900/20 tw-transition-all">
                                    <i class="bi bi-hammer tw-text-xl tw-block tw-mb-1"></i>
                                    <span class="tw-text-xs tw-font-black tw-uppercase tw-tracking-widest">Craftsman</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="custom_code" class="tw-block tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-mb-1">Custom Code (Optional)</label>
                        <input id="custom_code" name="custom_code" type="text" class="tw-block tw-w-full tw-px-4 tw-py-3 tw-bg-gray-50 dark:tw-bg-slate-800 tw-border tw-border-gray-100 dark:tw-border-slate-800 tw-rounded-xl focus:tw-ring-2 focus:tw-ring-indigo-500 tw-text-sm" placeholder="Leave empty for auto">
                    </div>

                    <div>
                        <label for="admin_notes" class="tw-block tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-mb-1">Admin Notes</label>
                        <textarea id="admin_notes" name="admin_notes" rows="3" class="tw-block tw-w-full tw-px-4 tw-py-3 tw-bg-gray-50 dark:tw-bg-slate-800 tw-border tw-border-gray-100 dark:tw-border-slate-800 tw-rounded-xl focus:tw-ring-2 focus:tw-ring-indigo-500 tw-text-sm"></textarea>
                    </div>

                    <button type="submit" class="tw-w-full tw-py-4 tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-text-sm tw-font-black tw-uppercase tw-tracking-widest tw-rounded-2xl tw-shadow-lg tw-shadow-indigo-600/20 tw-transition-all">
                        Approve & Create Account
                    </button>
                </form>

                <form action="{{ route('super-admin.registrations.reject', $registration->id) }}" method="POST" class="tw-mt-4">
                    @csrf
                    <button type="submit" class="tw-w-full tw-py-3 tw-text-rose-600 hover:tw-bg-rose-50 tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest tw-rounded-xl tw-transition-all" onclick="return confirm('Are you sure you want to reject this request?')">
                        Reject Request
                    </button>
                </form>
                @else
                <div class="tw-space-y-4">
                    <div class="tw-p-4 tw-rounded-2xl @if($registration->status == 'Approved') tw-bg-emerald-50 tw-text-emerald-700 @else tw-bg-rose-50 tw-text-rose-700 @endif tw-flex tw-items-center tw-gap-3">
                        <i class="bi @if($registration->status == 'Approved') bi-check-circle-fill @else bi-x-circle-fill @endif tw-text-xl"></i>
                        <span class="tw-text-sm tw-font-bold uppercase tracking-widest">Request already {{ $registration->status }}</span>
                    </div>
                    @if($registration->admin_notes)
                    <div class="tw-p-4 tw-bg-gray-50 dark:tw-bg-slate-800 tw-rounded-2xl">
                        <label class="tw-block tw-text-[9px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest tw-mb-1">Admin Notes</label>
                        <p class="tw-text-sm tw-text-gray-600 dark:tw-text-gray-300">{{ $registration->admin_notes }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
