@extends('admin.layouts.app')

@section('title', 'Account Freeze/Unfreeze Management')

@section('content')
<div class="p-6 space-y-8 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                <span class="p-2 bg-magenta-800 text-white rounded-xl shadow-lg shadow-magenta-200">
                    <i class="bi bi-lock-fill"></i>
                </span>
                Account Access Control
            </h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">Manage security and account visibility across the platform.</p>
        </div>
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-100 px-4 py-2 rounded-2xl">
            <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
            <span class="text-rose-900 font-bold text-sm">
                {{ $frozenBuyers->count() + $frozenCraftsmen->count() + $frozenCraftsmanStaff->count() + $frozenAdmins->count() + $frozenKeyUsers->count() + $frozenUsers->count() }} 
                Accounts Currently Frozen
            </span>
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
        <form action="{{ route('admin.freeze-account.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" placeholder="Search by BP Code, User Code, or Name..." 
                       value="{{ request('search') }}"
                       class="w-full pl-11 pr-4 py-3 rounded-2xl bg-gray-50 border-none focus:ring-2 focus:ring-magenta-800 transition-all font-medium text-gray-700">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-8 py-3 bg-magenta-800 text-white rounded-2xl font-bold hover:bg-magenta-900 transition-all shadow-lg shadow-magenta-100 flex items-center gap-2">
                    <i class="bi bi-filter"></i> Search
                </button>
                <a href="{{ route('admin.freeze-account.index') }}" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-2xl font-bold hover:bg-gray-200 transition-all flex items-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Main Content Tabs -->
    <div x-data="{ tab: 'buyers' }" class="space-y-6">
        <div class="flex flex-wrap items-center gap-2 p-1.5 bg-gray-50 w-fit rounded-2xl border border-gray-100">
            <button @click="tab = 'buyers'" :class="tab === 'buyers' ? 'bg-white text-magenta-800 shadow-sm' : 'text-gray-500 hover:text-gray-900'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                <i class="bi bi-person"></i> Buyers ({{ $allBuyers->total() }})
            </button>
            <button @click="tab = 'craftsmen'" :class="tab === 'craftsmen' ? 'bg-white text-magenta-800 shadow-sm' : 'text-gray-500 hover:text-gray-900'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                <i class="bi bi-person-workspace"></i> Craftsmen ({{ $allCraftsmen->total() }})
            </button>
            <button @click="tab = 'key-users'" :class="tab === 'key-users' ? 'bg-white text-magenta-800 shadow-sm' : 'text-gray-500 hover:text-gray-900'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                <i class="bi bi-key"></i> Key Users ({{ $allKeyUsers->total() }})
            </button>
            <button @click="tab = 'users'" :class="tab === 'users' ? 'bg-white text-magenta-800 shadow-sm' : 'text-gray-500 hover:text-gray-900'" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                <i class="bi bi-person-circle"></i> Users ({{ $allUsers->total() }})
            </button>
        </div>

        <!-- Table Containers -->
        <div class="bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-xl shadow-gray-100/50">
            
            <!-- Buyers Tab -->
            <div x-show="tab === 'buyers'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Identify</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Business Detail</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Contact</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($allBuyers as $buyer)
                        <tr class="hover:bg-gray-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-black text-magenta-800">{{ $buyer->bp_code ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-extrabold text-gray-900 uppercase tracking-tight">{{ $buyer->business_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-400 font-medium">{{ $buyer->email ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-700">{{ $buyer->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter"><i class="bi bi-telephone text-[8px]"></i> {{ $buyer->mobile ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="status-badge-container">
                                    @if($buyer->is_frozen)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                            <i class="bi bi-snow2 animate-pulse"></i> Frozen
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100/50">
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($buyer->is_frozen)
                                    <button class="unfreeze-btn px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="buyer" data-model-id="{{ $buyer->id }}">
                                        <i class="bi bi-unlock-fill"></i> Unfreeze
                                    </button>
                                @else
                                    <button class="freeze-btn px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="buyer" data-model-id="{{ $buyer->id }}">
                                        <i class="bi bi-lock-fill"></i> Freeze
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <i class="bi bi-inbox text-4xl"></i>
                                    <span class="text-sm font-bold uppercase tracking-widest">No buyers match your search</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Craftsmen Tab -->
            <div x-show="tab === 'craftsmen'" class="overflow-x-auto" x-cloak>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Code</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Provider Detail</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Specialization</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($allCraftsmen as $craftsman)
                        <tr class="hover:bg-gray-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-black text-magenta-800">{{ $craftsman->craftman_code ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-extrabold text-gray-900 uppercase tracking-tight">{{ $craftsman->name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-400 font-medium">{{ $craftsman->email ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">{{ $craftsman->specialization ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="status-badge-container">
                                    @if($craftsman->is_frozen)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                            <i class="bi bi-snow2 animate-pulse"></i> Frozen
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100/50">
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($craftsman->is_frozen)
                                    <button class="unfreeze-btn px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="craftsman" data-model-id="{{ $craftsman->id }}">
                                        <i class="bi bi-unlock-fill"></i> Unfreeze
                                    </button>
                                @else
                                    <button class="freeze-btn px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="craftsman" data-model-id="{{ $craftsman->id }}">
                                        <i class="bi bi-lock-fill"></i> Freeze
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 font-bold uppercase tracking-widest">No craftsmen found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Key Users Tab -->
            <div x-show="tab === 'key-users'" class="overflow-x-auto" x-cloak>
                 <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">User Code</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Account Info</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">BP Link</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($allKeyUsers as $keyUser)
                        <tr class="hover:bg-gray-50/30 transition-colors group">
                            <td class="px-6 py-4"><span class="text-sm font-black text-magenta-800">{{ $keyUser->user_code ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-extrabold text-gray-900 uppercase tracking-tight">{{ $keyUser->full_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-400 font-medium">{{ $keyUser->email_id ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-lg">{{ $keyUser->bp_code ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                <div class="status-badge-container">
                                    @if($keyUser->is_frozen)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                            <i class="bi bi-snow2 animate-pulse"></i> Frozen
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100/50">
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($keyUser->is_frozen)
                                    <button class="unfreeze-btn px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="key_user" data-model-id="{{ $keyUser->id }}">
                                        <i class="bi bi-unlock-fill"></i> Unfreeze
                                    </button>
                                @else
                                    <button class="freeze-btn px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="key_user" data-model-id="{{ $keyUser->id }}">
                                        <i class="bi bi-lock-fill"></i> Freeze
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 font-bold uppercase tracking-widest">No Key Users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Users Tab -->
            <div x-show="tab === 'users'" class="overflow-x-auto" x-cloak>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">User Code</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Account Info</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">BP Link</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($allUsers as $user)
                        <tr class="hover:bg-gray-50/30 transition-colors group">
                            <td class="px-6 py-4"><span class="text-sm font-black text-magenta-800">{{ $user->user_code ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-extrabold text-gray-900 uppercase tracking-tight">{{ $user->full_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-400 font-medium">{{ $user->email_id ?? 'N/A' }}</span>
                                </div>
                            </td>
                             <td class="px-6 py-4"><span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-lg">{{ $user->bp_code ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                <div class="status-badge-container">
                                    @if($user->is_frozen)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                            <i class="bi bi-snow2 animate-pulse"></i> Frozen
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100/50">
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($user->is_frozen)
                                    <button class="unfreeze-btn px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="user" data-model-id="{{ $user->id }}">
                                        <i class="bi bi-unlock-fill"></i> Unfreeze
                                    </button>
                                @else
                                    <button class="freeze-btn px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-100 flex items-center gap-2 ml-auto" 
                                            data-model-type="user" data-model-id="{{ $user->id }}">
                                        <i class="bi bi-lock-fill"></i> Freeze
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 font-bold uppercase tracking-widest">No Users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Analytics Summary Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Frozen -->
        <div class="lg:col-span-2 bg-rose-900 rounded-3xl p-6 text-white shadow-xl shadow-rose-200 border border-rose-800 flex items-center justify-between overflow-hidden relative group">
            <div class="relative z-10">
                <span class="text-[10px] font-bold text-rose-300 uppercase tracking-widest">Platform Health</span>
                <h3 class="text-3xl font-black mt-1">{{ $frozenBuyers->count() + $frozenCraftsmen->count() + $frozenCraftsmanStaff->count() + $frozenAdmins->count() + $frozenKeyUsers->count() + $frozenUsers->count() }}</h3>
                <p class="text-xs font-bold text-rose-100 uppercase tracking-tight mt-1">Total Frozen Accounts</p>
            </div>
            <i class="bi bi-shield-lock-fill text-6xl text-white/10 absolute -right-2 transform group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Buyers Card -->
        <div class="bg-indigo-50/50 rounded-3xl p-5 border border-indigo-100/50 flex flex-col justify-between hover:border-indigo-200 transition-colors">
            <div class="flex justify-between items-start">
                <span class="p-2 bg-indigo-600 text-white rounded-xl"><i class="bi bi-people-fill"></i></span>
                <span class="text-[10px] font-extrabold text-indigo-400 uppercase tracking-tighter">Buyers</span>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-indigo-900">{{ $allBuyers->total() }}</h4>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ $frozenBuyers->count() }} Frozen</span>
                </div>
            </div>
        </div>

        <!-- Craftsmen Card -->
        <div class="bg-emerald-50/50 rounded-3xl p-5 border border-emerald-100/50 flex flex-col justify-between hover:border-emerald-200 transition-colors">
            <div class="flex justify-between items-start">
                <span class="p-2 bg-emerald-600 text-white rounded-xl"><i class="bi bi-person-workspace"></i></span>
                <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-tighter">Craftsmen</span>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-emerald-900">{{ $allCraftsmen->total() }}</h4>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ $frozenCraftsmen->count() + $frozenCraftsmanStaff->count() }} Frozen</span>
                </div>
            </div>
        </div>

        <!-- Key Users Card -->
        <div class="bg-amber-50/50 rounded-3xl p-5 border border-amber-100/50 flex flex-col justify-between hover:border-amber-200 transition-colors">
            <div class="flex justify-between items-start">
                <span class="p-2 bg-amber-600 text-white rounded-xl"><i class="bi bi-key-fill"></i></span>
                <span class="text-[10px] font-extrabold text-amber-400 uppercase tracking-tighter">Key Users</span>
            </div>
            <div class="mt-4">
                <h4 class="text-2xl font-black text-amber-900">{{ $allKeyUsers->total() }}</h4>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ $frozenKeyUsers->count() }} Frozen</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token for fetch
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle freeze button clicks (delegated or direct)
    function attachListeners() {
        document.querySelectorAll('.freeze-btn, .unfreeze-btn').forEach(button => {
            // Remove old listener to avoid doubles if we re-trigger
            button.replaceWith(button.cloneNode(true));
        });

        document.querySelectorAll('.freeze-btn').forEach(button => {
            button.addEventListener('click', function() {
                toggleAccountFreeze(this, 'freeze');
            });
        });

        document.querySelectorAll('.unfreeze-btn').forEach(button => {
            button.addEventListener('click', function() {
                toggleAccountFreeze(this, 'unfreeze');
            });
        });
    }

    function toggleAccountFreeze(button, action) {
        const modelType = button.getAttribute('data-model-type');
        const modelId = button.getAttribute('data-model-id');
        const confirmMsg = action === 'freeze' 
            ? 'Are you sure you want to freeze this account? The user will not be able to login and their intellectual property will be hidden.' 
            : 'Are you sure you want to restore access to this account?';

        if (!confirm(confirmMsg)) return;

        button.disabled = true;
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> ...';

        fetch('{{ route("admin.freeze-account.toggle-freeze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ model_type: modelType, model_id: modelId, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Badge
                const statusCell = button.closest('tr').querySelector('.status-badge-container');
                if (action === 'freeze') {
                    statusCell.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                            <i class="bi bi-snow2 animate-pulse"></i> Frozen
                        </span>`;
                    button.className = 'unfreeze-btn px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-100 flex items-center gap-2 ml-auto';
                    button.innerHTML = '<i class="bi bi-unlock-fill"></i> Unfreeze';
                } else {
                    statusCell.innerHTML = `
                         <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100/50">
                            <i class="bi bi-check-circle-fill"></i> Active
                        </span>`;
                    button.className = 'freeze-btn px-4 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-100 flex items-center gap-2 ml-auto';
                    button.innerHTML = '<i class="bi bi-lock-fill"></i> Freeze';
                }
                
                // Re-attach listener because we replaced the button's behavior
                button.replaceWith(button.cloneNode(true));
                attachListeners();
                
                // Optional: Update global counter or summary cards if needed.
                // For now, simple alert or auto-update is enough.
            } else {
                alert('Process failed: ' + data.message);
                button.innerHTML = originalContent;
            }
            button.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('A critical error occurred.');
            button.innerHTML = originalContent;
            button.disabled = false;
        });
    }

    attachListeners();
});
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
