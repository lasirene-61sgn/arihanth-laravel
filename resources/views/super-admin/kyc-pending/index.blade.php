@extends('super-admin.layouts.app')

@section('title', __('messages.kyc_pending_title'))

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Fixed: Converted @apply to standard CSS so it works with CDN */
    .erp-card { 
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #f3f4f6;
        overflow: hidden;
    }
    .stat-card { 
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #f3f4f6;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-0.25rem);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>

<div class="p-4">
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card p-5">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center mb-3">
                    <i class="bi bi-person-fill text-2xl"></i>
                </div>
                <div class="text-3xl font-extrabold text-amber-600">{{ $pendingBuyers->count() }}</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('messages.buyers_pending') }}</div>
            </div>
        </div>

        <div class="stat-card p-5">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center mb-3">
                    <i class="bi bi-person-workspace text-2xl"></i>
                </div>
                <div class="text-3xl font-extrabold text-amber-600">{{ $pendingCraftsmen->count() }}</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('messages.craftsmen_pending') }}</div>
            </div>
        </div>

        <div class="stat-card p-5">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center mb-3">
                    <i class="bi bi-people-fill text-2xl"></i>
                </div>
                <div class="text-3xl font-extrabold text-blue-600">{{ $pendingBuyers->count() + $pendingCraftsmen->count() }}</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('messages.total_pending') }}</div>
            </div>
        </div>

        <div class="stat-card p-5">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-green-50 text-green-500 rounded-xl flex items-center justify-center mb-3">
                    <i class="bi bi-patch-check-fill text-2xl"></i>
                </div>
                <div class="text-3xl font-extrabold text-green-600">
                    {{ max(0, \App\Models\Buyer::count() + \App\Models\Craftman::count() - ($pendingBuyers->count() + $pendingCraftsmen->count())) }}
                </div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('messages.kyc_completed') }}</div>
            </div>
        </div>
    </div>

    <div class="erp-card">
        <div class="px-6 py-5 bg-white border-b border-gray-100 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center">
                <div class="w-2 h-8 bg-blue-600 rounded-full mr-3"></div>
                <h4 class="text-xl font-bold text-gray-800">{{ __('messages.kyc_verification_queue') }}</h4>
            </div>
            <div class="flex items-center gap-4">
                <form action="{{ route('super-admin.kyc-pending.index') }}" method="GET" class="flex items-center">
                    <div class="relative group">
                        <input type="text" name="searchText" value="{{ request('searchText') }}" 
                               placeholder="Search partners..." 
                               class="pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none w-64 transition-all">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                        @if(request('searchText'))
                            <a href="{{ route('super-admin.kyc-pending.index') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-red-50 text-red-700 border border-red-100">
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse mr-2"></span>
                    {{ $pendingBuyers->count() + $pendingCraftsmen->count() }} {{ __('messages.urgent_reviews') }}
                </span>
            </div>
        </div>
        
        <div class="p-6">
            <ul class="flex border-b border-gray-200 mb-6 space-x-6" id="kycTabs" role="tablist">
                <li role="presentation">
                    <button class="active-tab-btn pb-3 px-2 text-blue-600 border-b-2 border-blue-600 font-bold text-sm flex items-center transition-all duration-200" 
                            id="buyers-tab" data-bs-toggle="tab" data-bs-target="#buyers" type="button" role="tab">
                        <i class="bi bi-shop me-2"></i> {{ __('messages.buyers') }} ({{ $pendingBuyers->count() }})
                    </button>
                </li>
                <li role="presentation">
                    <button class="inactive-tab-btn pb-3 px-2 text-gray-400 border-b-2 border-transparent font-medium text-sm flex items-center transition-all duration-200 hover:text-gray-600" 
                            id="craftsmen-tab" data-bs-toggle="tab" data-bs-target="#craftsmen" type="button" role="tab">
                        <i class="bi bi-tools me-2"></i> {{ __('messages.craftsmen') }} ({{ $pendingCraftsmen->count() }})
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="buyers" role="tabpanel">
                    @if($pendingBuyers->isEmpty())
                        <div class="text-center py-16">
                            <i class="bi bi-cloud-check text-5xl text-gray-200"></i>
                            <h4 class="text-lg font-semibold text-gray-500 mt-4">{{ __('messages.no_pending_buyers') }}</h4>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50">
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.business_info') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.contact') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.missing_documents') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.status') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($pendingBuyers as $buyer)
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="p-4">
                                                <div class="font-bold text-gray-800">{{ $buyer->business_name ?? 'N/A' }}</div>
                                                <div class="text-[10px] font-mono text-gray-400 tracking-tighter uppercase">{{ str_pad($buyer->bp_code, STR_PAD_LEFT) }}</div>
                                            </td>
                                            <td class="p-4">
                                                <div class="text-sm text-gray-700">{{ $buyer->name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $buyer->email ?? 'N/A' }}</div>
                                            </td>
                                            <td class="p-4">
                                                @php
                                                    $missingDocs = [];
                                                    if(empty($buyer->business_name) || empty($buyer->name)) $missingDocs[] = 'Info';
                                                    if(!$buyer->aadharDetails || $buyer->aadharDetails->count() == 0) $missingDocs[] = 'Aadhar';
                                                    if(!$buyer->panDetails || $buyer->panDetails->count() == 0) $missingDocs[] = 'PAN';
                                                    if(!$buyer->bankDetails || $buyer->bankDetails->count() == 0) $missingDocs[] = 'Bank';
                                                @endphp
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($missingDocs as $doc)
                                                        <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[10px] font-bold rounded border border-red-100">{{ $doc }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full bg-amber-100 text-amber-700">Pending</span>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex items-center space-x-3">
                                                    <a href="{{ route('super-admin.business-partner.buyer.show', $buyer) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">{{ __('messages.review') }}</a>
                                                    <a href="{{ route('super-admin.business-partner.buyer.edit', $buyer) }}" class="text-gray-400 hover:text-gray-600 text-sm"><i class="bi bi-pencil"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="craftsmen" role="tabpanel">
                    @if($pendingCraftsmen->isEmpty())
                        <div class="text-center py-16">
                            <i class="bi bi-cloud-check text-5xl text-gray-200"></i>
                            <h4 class="text-lg font-semibold text-gray-500 mt-4">{{ __('messages.no_pending_craftsmen') }}</h4>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50">
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.professional_info') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.specialization') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.missing_documents') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.status') }}</th>
                                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($pendingCraftsmen as $craftsman)
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="p-4">
                                                <div class="font-bold text-gray-800">{{ $craftsman->name ?? 'N/A' }}</div>
                                                <div class="text-[10px] font-mono text-gray-400 tracking-tighter uppercase">{{ str_pad($craftsman->craftman_code, STR_PAD_LEFT) }}</div>
                                            </td>
                                            <td class="p-4">
                                                <div class="text-sm text-gray-700">{{ $craftsman->specialization ?? 'General' }}</div>
                                                <div class="text-xs text-gray-400">{{ $craftsman->phone_number ?? 'N/A' }}</div>
                                            </td>
                                            <td class="p-4">
                                                @php
                                                    $missingDocs = [];
                                                    if(empty($craftsman->name)) $missingDocs[] = 'Info';
                                                    if(!$craftsman->aadharDetails || $craftsman->aadharDetails->count() == 0) $missingDocs[] = 'Aadhar';
                                                    if(!$craftsman->panDetails || $craftsman->panDetails->count() == 0) $missingDocs[] = 'PAN';
                                                    if(!$craftsman->bankDetails || $craftsman->bankDetails->count() == 0) $missingDocs[] = 'Bank';
                                                @endphp
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($missingDocs as $doc)
                                                        <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[10px] font-bold rounded border border-red-100">{{ $doc }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full bg-amber-100 text-amber-700">Pending</span>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex items-center space-x-3">
                                                    <a href="{{ route('super-admin.business-partner.craftman.show', $craftsman) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">{{ __('messages.review') }}</a>
                                                    <a href="{{ route('super-admin.business-partner.craftman.edit', $craftsman) }}" class="text-gray-400 hover:text-gray-600 text-sm"><i class="bi bi-pencil"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh page every 5 mins
    setTimeout(() => { location.reload(); }, 300000);

    // Tab UI switcher logic
    const tabButtons = document.querySelectorAll('#kycTabs button');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => {
                b.classList.remove('text-blue-600', 'border-blue-600', 'font-bold');
                b.classList.add('text-gray-400', 'border-transparent', 'font-medium');
            });
            this.classList.remove('text-gray-400', 'border-transparent', 'font-medium');
            this.classList.add('text-blue-600', 'border-blue-600', 'font-bold');
        });
    });
});
</script>
@endsection