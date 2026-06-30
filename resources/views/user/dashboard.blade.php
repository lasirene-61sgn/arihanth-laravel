@extends('user.layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h4 class="text-2xl font-bold text-slate-800 tracking-tight">User Dashboard</h4>
            <p class="text-sm text-slate-500 font-medium">Welcome back to your control center.</p>
        </div>
        
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="javascript: void(0);" class="text-sm font-medium text-slate-400 hover:text-indigo-600 transition-colors">
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-slate-300 text-xs mx-2"></i>
                        <span class="text-sm font-semibold text-indigo-600">Dashboard</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        
        <div class="relative overflow-hidden rounded-2xl p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl group" 
             style="background: linear-gradient(135deg, #1c1c2e 0%, #2d2d44 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Total Products</p>
                    <h4 class="text-3xl font-black text-white">{{ $totalProducts }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-white/10 text-white transition-colors group-hover:bg-white/20">
                    <i class="bi bi-box text-2xl"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-white/5 rounded-full blur-2xl"></div>
        </div>

        <div class="relative overflow-hidden rounded-2xl p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl group" 
             style="background: linear-gradient(135deg, #374151 0%, #4b5563 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-1">Total Work Orders</p>
                    <h4 class="text-3xl font-black text-white">{{ $totalWorkOrders }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-white/10 text-white transition-colors group-hover:bg-white/20">
                    <i class="bi bi-clipboard-check text-2xl"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-white/5 rounded-full blur-2xl"></div>
        </div>

        <div class="relative overflow-hidden rounded-2xl p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl group" 
             style="background: linear-gradient(135deg, #3f3f5b 0%, #52526e 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-1">Total Designs</p>
                    <h4 class="text-3xl font-black text-white">{{ $totalDesigns }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-white/10 text-white transition-colors group-hover:bg-white/20">
                    <i class="bi bi-pencil-square text-2xl"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-white/5 rounded-full blur-2xl"></div>
        </div>

        <div class="relative overflow-hidden rounded-2xl p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl group" 
             style="background: linear-gradient(135deg, #2d2d44 0%, #444460 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-1">Total Catalogue</p>
                    <h4 class="text-3xl font-black text-white">{{ $acceptedProducts }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-white/10 text-white transition-colors group-hover:bg-white/20">
                    <i class="bi bi-journal-text text-2xl"></i>
                </div>
            </div>
            <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-white/5 rounded-full blur-2xl"></div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <h5 class="text-lg font-bold text-slate-800 mb-4">Quick Overview</h5>
            <div class="flex items-center justify-center h-48 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                <p class="text-slate-400 text-sm italic text-center px-4">
                    Graph or recent activity lists can be placed here to provide a quick look at your system metrics.
                </p>
            </div>
        </div>
        
        <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <h5 class="text-lg font-bold mb-2">Need Help?</h5>
            <p class="text-indigo-200 text-sm mb-4 leading-relaxed">Check our documentation or reach out to support if you're facing any issues with your orders.</p>
            <a href="#" class="inline-flex items-center text-sm font-bold bg-white text-indigo-900 px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                View Guide <i class="bi bi-arrow-right ms-2"></i>
            </a>
            <i class="bi bi-question-circle absolute -bottom-6 -right-4 text-white/10 text-8xl"></i>
        </div>
    </div>
</div>
@endsection