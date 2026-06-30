@extends('super-admin.layouts.app')

@section('content')
<div class="tw-container tw-mx-auto">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
        <div>
            <h2 class="tw-text-2xl tw-font-black tw-text-gray-900 dark:tw-text-white tw-uppercase tw-tracking-tight">
                {{ __('messages.registrations') }}
            </h2>
            <p class="tw-text-sm tw-text-gray-500 dark:tw-text-gray-400">Review and approve new partner registration requests</p>
        </div>
    </div>

    <div class="tw-bg-white dark:tw-bg-slate-900 tw-rounded-3xl tw-shadow-xl tw-overflow-hidden tw-border tw-border-gray-100 dark:tw-border-slate-800">
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-border-collapse">
                <thead>
                    <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Date</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Business Name</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Contact Person</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Mobile</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Status</th>
                        <th class="tw-px-6 tw-py-4 tw-text-[10px] tw-font-black tw-text-gray-400 tw-uppercase tw-tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-100 dark:tw-divide-slate-800">
                    @forelse($requests as $req)
                    <tr class="hover:tw-bg-gray-50/50 dark:hover:tw-bg-slate-800/30 tw-transition-colors">
                        <td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-600 dark:tw-text-gray-300">
                            {{ $req->created_at->format('d M Y') }}
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <span class="tw-text-sm tw-font-bold tw-text-gray-900 dark:tw-text-white">{{ $req->business_name }}</span>
                            <div class="tw-text-[11px] tw-text-gray-400">{{ $req->city }}, {{ $req->state }}</div>
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-600 dark:tw-text-gray-300">
                            {{ $req->name }}
                        </td>
                        <td class="tw-px-6 tw-py-4 tw-text-sm tw-text-gray-600 dark:tw-text-gray-300">
                            {{ $req->mobile }}
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <span class="tw-px-3 tw-py-1 tw-rounded-full tw-text-[10px] tw-font-black tw-uppercase tw-tracking-widest 
                                @if($req->status == 'Pending') tw-bg-amber-100 tw-text-amber-700 
                                @elseif($req->status == 'Approved') tw-bg-emerald-100 tw-text-emerald-700
                                @else tw-bg-rose-100 tw-text-rose-700 @endif">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="tw-px-6 tw-py-4">
                            <a href="{{ route('super-admin.registrations.show', $req->id) }}" class="tw-p-2 tw-text-indigo-600 hover:tw-bg-indigo-50 tw-rounded-xl tw-transition-all">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="tw-px-6 tw-py-12 tw-text-center">
                            <div class="tw-flex tw-flex-col tw-items-center">
                                <i class="bi bi-inbox tw-text-4xl tw-text-gray-200 tw-mb-2"></i>
                                <p class="tw-text-sm tw-text-gray-400">No registration requests found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-100 dark:tw-border-slate-800">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
