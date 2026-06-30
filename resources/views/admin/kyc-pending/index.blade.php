@extends('admin.layouts.app')

@section('title', 'KYC Compliance Dashboard')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">KYC Completed</p>
                        <h3 class="text-2xl font-bold text-emerald-600">
                            {{ max(0, \App\Models\Buyer::count() + \App\Models\Craftman::count() - ($pendingBuyers->count() + $pendingCraftsmen->count())) }}
                        </h3>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-500 rounded-lg">
                        <i class="bi bi-patch-check-fill text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pending</p>
                        <h3 class="text-2xl font-bold text-amber-600">{{ $pendingBuyers->count() + $pendingCraftsmen->count() }}</h3>
                    </div>
                    <div class="p-3 bg-amber-50 text-amber-500 rounded-lg">
                        <i class="bi bi-hourglass-split text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Buyers Pending</p>
                        <h3 class="text-2xl font-bold text-blue-600">{{ $pendingBuyers->count() }}</h3>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-500 rounded-lg">
                        <i class="bi bi-person-fill text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Craftsmen Pending</p>
                        <h3 class="text-2xl font-bold text-purple-600">{{ $pendingCraftsmen->count() }}</h3>
                    </div>
                    <div class="p-3 bg-purple-50 text-purple-500 rounded-lg">
                        <i class="bi bi-tools text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h4 class="font-bold text-gray-800 flex items-center">
                    <i class="bi bi-list-ul mr-2 text-indigo-500"></i>
                    Pending Verification List
                </h4>
                
                <div class="flex flex-wrap items-center gap-4">
                    <form action="{{ route('admin.kyc-pending.index') }}" method="GET" class="flex items-center">
                        <div class="relative group">
                            <input type="text" name="searchText" value="{{ request('searchText') }}" 
                                   placeholder="Search by Name, BP/CP Code..." 
                                   class="pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none w-64 transition-all">
                            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            @if(request('searchText'))
                                <a href="{{ route('admin.kyc-pending.index') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="flex bg-gray-100 p-1 rounded-lg">
                        <button onclick="switchTab('buyers')" id="btn-buyers" class="tab-btn px-4 py-2 text-xs font-bold rounded-md transition-all bg-white shadow-sm text-indigo-600">
                            Buyers ({{ $pendingBuyers->count() }})
                        </button>
                        <button onclick="switchTab('craftsmen')" id="btn-craftsmen" class="tab-btn px-4 py-2 text-xs font-bold rounded-md transition-all text-gray-500 hover:text-gray-700">
                            Craftsmen ({{ $pendingCraftsmen->count() }})
                        </button>
                    </div>
                </div>
            </div>

            <div id="tab-buyers" class="tab-content block">
                @if($pendingBuyers->isEmpty())
                    <div class="p-12 text-center text-gray-400 italic font-medium">No Buyers pending KYC review.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-400 text-[10px] uppercase font-bold tracking-widest border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4">Partner</th>
                                    <th class="px-6 py-4">Contact</th>
                                    <th class="px-6 py-4 text-center">Missing Documents</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($pendingBuyers as $buyer)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-800 block leading-none">{{ $buyer->business_name }}</span>
                                            <span class="text-[10px] font-mono text-gray-400">{{ str_pad($buyer->bp_code,STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            <p class="text-gray-700 font-medium">{{ $buyer->name }}</p>
                                            <p class="text-gray-400">{{ $buyer->email }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center gap-1">
                                                @if(!$buyer->aadharDetails->count()) <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[9px] font-bold rounded uppercase">Aadhar</span> @endif
                                                @if(!$buyer->panDetails->count()) <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[9px] font-bold rounded uppercase">PAN</span> @endif
                                                @if(!$buyer->bankDetails->count()) <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[9px] font-bold rounded uppercase">Bank</span> @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            <a href="{{ route('admin.business-partner.buyer.show', $buyer) }}" class="text-blue-500 hover:text-blue-700 inline-block"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('admin.business-partner.buyer.edit', $buyer) }}" class="text-gray-400 hover:text-gray-600 inline-block"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div id="tab-craftsmen" class="tab-content hidden">
                @if($pendingCraftsmen->isEmpty())
                    <div class="p-12 text-center text-gray-400 italic font-medium">No Craftsmen pending KYC review.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-400 text-[10px] uppercase font-bold tracking-widest border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4">Name</th>
                                    <th class="px-6 py-4 text-center">Documentation</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($pendingCraftsmen as $craftsman)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-800 block leading-none">{{ $craftsman->name }}</span>
                                            <span class="text-[10px] font-mono text-gray-400">{{ str_pad($craftsman->craftman_code, STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded-full uppercase border border-amber-200">Needs Review</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.business-partner.craftman.show', $craftsman) }}" class="text-blue-500 hover:text-blue-700"><i class="bi bi-eye"></i></a>
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

<script>
function switchTab(tab) {
    // Content Toggle
    document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    
    // Button Style Toggle
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-white', 'shadow-sm', 'text-indigo-600');
        b.classList.add('text-gray-500');
    });
    
    const activeBtn = document.getElementById('btn-' + tab);
    activeBtn.classList.add('bg-white', 'shadow-sm', 'text-indigo-600');
    activeBtn.classList.remove('text-gray-500');
}
</script>
@endsection