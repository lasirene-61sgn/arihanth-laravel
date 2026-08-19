<!DOCTYPE html>
<html lang="en" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Check for dark mode preference before Tailwind loads
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        magenta: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            300: '#f9a8d4',
                            400: '#f472b6',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                            800: '#97144d', // Primary
                            900: '#831843',
                            DEFAULT: '#97144d',
                        },
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .nav-link-active {
                @apply bg-white/20 text-white font-bold;
            }
            .sidebar-transition {
                @apply transition-all duration-300 ease-in-out;
            }
            /* Force submenu visibility */
            .collapse.show {
                display: block !important;
                visibility: visible !important;
                height: auto !important;
                opacity: 1 !important;
            }
            .submenu-item {
                @apply flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-colors text-sm;
            }
            .submenu-active {
                @apply bg-white/10 font-bold;
            }
        }
        
        /* Custom scrollbar for sidebar */
        #sidebar::-webkit-scrollbar {
            width: 5px;
        }
        #sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        /* Dark Mode Transitions */
        html.dark body { 
            background-color: #111827; 
            color: #f3f4f6; 
        }
        html.dark #content {
            background-color: #111827 !important;
        }
        html.dark .bg-white, 
        html.dark .card,
        html.dark main { 
            background-color: #1f2937 !important; 
            color: #f3f4f6 !important; 
        }
        html.dark .text-gray-800 { color: #f3f4f6 !important; }
        html.dark .text-gray-600 { color: #d1d5db !important; }
        html.dark .border-gray-100,
        html.dark .border-gray-200,
        html.dark .card-header,
        html.dark .card-footer,
        html.dark .table,
        html.dark .table th,
        html.dark .table td { 
            border-color: #374151 !important; 
            color: #f3f4f6 !important;
        }
        html.dark .bg-gray-50 { background-color: #374151 !important; }
        html.dark .modal-content { background-color: #1f2937 !important; color: #f3f4f6 !important; }
        html.dark .form-control, 
        html.dark .form-select { 
            background-color: #374151 !important; 
            border-color: #4b5563 !important; 
            color: #f3f4f6 !important; 
        }
        html.dark .form-control::placeholder { color: #9ca3af !important; }
        html.dark .dropdown-menu { background-color: #1f2937 !important; border-color: #374151 !important; }
        html.dark .dropdown-item { color: #f3f4f6 !important; }
        html.dark .dropdown-item:hover { background-color: #374151 !important; }
        html.dark .alert-success { background-color: #064e3b !important; color: #a7f3d0 !important; border-color: #065f46 !important; }
        html.dark .alert-danger { background-color: #7f1d1d !important; color: #fecaca !important; border-color: #991b1b !important; }
        html.dark .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>
    @yield('styles')
</head>

<body>
    <div class="flex min-h-screen bg-white">
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 flex flex-col w-[260px] bg-magenta-800 text-white z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto">
            <div class="p-4 border-b border-white/10 text-center">
                <img src="{{ asset('images/tara.png') }}" alt="AJ Logo" class="mx-auto" style="height: 65px; width: 65px; object-fit: contain;">
            </div>

            <nav class="py-2 overflow-x-hidden">
                <ul class="space-y-1">
                    <li class="px-3">
                        <a class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 font-bold' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 text-lg"></i>
                            <span class="text-sm font-medium">Dashboard</span>
                        </a>
                    </li>

                    @if(Auth::guard('admin')->user()->hasPermission('business_partner'))
                    <li class="px-3 pt-2">
                        <button class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.business-partner.*') ? 'bg-white/20 text-white font-bold' : 'text-white/70 hover:text-white' }}"
                            data-bs-toggle="collapse" data-bs-target="#businessPartnerSubmenu" aria-expanded="{{ request()->routeIs('admin.business-partner.*') ? 'true' : 'false' }}" aria-controls="businessPartnerSubmenu">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-people text-lg"></i>
                                <span class="text-sm font-medium">Business Partner</span>
                            </div>
                            <i class="bi bi-chevron-down text-[0.7rem] transition-transform duration-300 {{ request()->routeIs('admin.business-partner.*') ? 'rotate-180' : '' }}"></i>
                        </button>
                        <div class="collapse {{ request()->routeIs('admin.business-partner.*') ? 'show' : '' }}" id="businessPartnerSubmenu">
                            <ul class="mt-2 ml-4 border-l border-white/20 pl-2 space-y-1 overflow-visible">
                                <li>
                                    <a class="submenu-item {{ request()->routeIs('admin.business-partner.index') ? 'submenu-active' : '' }}"
                                        href="{{ route('admin.business-partner.index') }}">
                                        <i class="bi bi-circle text-[0.4rem]"></i>
                                        <span>Overview</span>
                                    </a>
                                
                                <li>
                                    <a class="submenu-item justify-between {{ request()->routeIs('admin.business-partner.buyer') ? 'submenu-active' : '' }}"
                                        href="{{ route('admin.business-partner.buyer') }}">
                                        <div class="flex items-center gap-3">
                                            <i class="bi bi-circle text-[0.4rem]"></i>
                                            <span>Buyer</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-white/20 text-white rounded-full text-[0.6rem] font-bold">{{ $sidebarCounts['buyersCount'] ?? 0 }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="submenu-item justify-between {{ request()->routeIs('admin.business-partner.craftman') ? 'submenu-active' : '' }}"
                                        href="{{ route('admin.business-partner.craftman') }}">
                                        <div class="flex items-center gap-3">
                                            <i class="bi bi-circle text-[0.4rem]"></i>
                                            <span>Craftman</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-white/20 text-white rounded-full text-[0.6rem] font-bold">{{ $sidebarCounts['craftsmenCount'] ?? 0 }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('key_user_management'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.key-user.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.key-user.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-key text-lg"></i>
                                <span class="text-sm font-medium">Key User</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['keyUsersCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('user_management'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.user.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.user.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-person-badge text-lg"></i>
                                <span class="text-sm font-medium">User</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['usersCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('can_create_staff'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.business-partner.craftsman-staff*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.business-partner.craftsman-staff') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-person-badge text-lg"></i>
                                <span class="text-sm font-medium">Craftsman Staff</span>
                            </div>
                            <!-- <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['usersCount'] }}</span> -->
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('work_order'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.work-order.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.work-order.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-journal-text text-lg"></i>
                                <span class="text-sm font-medium">Work Order</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['workOrdersCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('work_order'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.repairs.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.repairs.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-tools text-lg"></i>
                                <span class="text-sm font-medium">Repairs</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['repairsCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('product'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.product.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.product.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-box-seam text-lg"></i>
                                <span class="text-sm font-medium">Product</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['productsCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('design'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.design.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.design.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-palette text-lg"></i>
                                <span class="text-sm font-medium">Design</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['designsCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('catalogue'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.catalogue.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.catalogue.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-book text-lg"></i>
                                <span class="text-sm font-medium">Catalogue</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['cataloguesCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.craftsman-production.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.craftsman-production.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-person-gear text-lg"></i>
                                <span class="text-sm font-medium">Craftsman Production</span>
                            </div>
                        </a>
                    </li>

                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.favorites.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.favorites.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-heart text-lg"></i>
                                <span class="text-sm font-medium">Favorites</span>
                            </div>
                        </a>
                    </li>

                    @if(Auth::guard('admin')->user()->hasPermission('purchase_order'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.purchase-order.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.purchase-order.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-receipt text-lg"></i> <!-- Updated: bi-receipt instead of bi-cart -->
                                <span class="text-sm font-medium">Purchase Order</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['purchaseOrdersCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('stock_order'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.stock-order.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.stock-order.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-qr-code-scan text-lg"></i>
                                <span class="text-sm font-medium">Live Stock Order</span>
                            </div>
                            <span class="px-2 py-0.5 bg-white/10 text-white rounded-full text-[0.65rem] font-bold">{{ $sidebarCounts['stockOrdersCount'] }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('meetings'))
                    <li class="px-3">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.meetings.*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.meetings.index') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-camera-video text-lg"></i>
                                <span class="text-sm font-medium">Meetings</span>
                            </div>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('kyc_pending'))
                    <li class="px-3">
                        <a class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.kyc-pending.index') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.kyc-pending.index') }}">
                            <i class="bi bi-person-check text-lg"></i> <!-- Updated: bi-person-check instead of earmark -->
                            <span class="text-sm font-medium">KYC Pending</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('freeze_account'))
                    <li class="px-3">
                        <a class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.freeze-account.index') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.freeze-account.index') }}">
                            <i class="bi bi-shield-lock text-lg"></i> <!-- Updated: bi-shield-lock for security -->
                            <span class="text-sm font-medium">Freeze Accounts</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::guard('admin')->user()->hasPermission('finance'))
                    <li class="px-3">
                        <a class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.finance.index') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('admin.finance.index') }}">
                            <i class="bi bi-currency-dollar text-lg"></i>
                            <span class="text-sm font-medium">Finance</span>
                        </a>
                    </li>
                    @endif

                    <!-- Support Section -->
                    <li class="px-3 pt-4 pb-2">
                        <span class="px-4 text-[0.65rem] font-bold uppercase tracking-widest text-white/40">Support</span>
                    </li>
                    <li class="px-3">
                        <a href="{{ route('admin.chat.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.chat.*') ? 'bg-white/20 font-bold' : 'text-white/70 hover:text-white' }}">
                            <i class="bi bi-chat-dots text-lg"></i>
                            <span class="text-sm font-medium">Messages</span>
                        </a>
                    </li>
                    <li class="px-3">
                        <a href="{{ route('admin.stock-order.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('admin.stock-order.*') ? 'bg-white/20 font-bold' : 'text-white/70 hover:text-white' }}">
                            <i class="bi bi-graph-up-arrow text-lg"></i> <!-- Updated: bi-graph-up-arrow instead of chat-dots -->
                            <span class="text-sm font-medium">Live Stock</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main id="content" class="flex-1 flex flex-col lg:ml-[260px] min-h-screen transition-all duration-300 bg-white">
            <!-- Top Navigation -->
            <header class="sticky top-0 z-30 flex h-[70px] items-center bg-magenta-800 px-4 md:px-8 shadow-md">
                <div class="flex flex-1 items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button id="hamburgerMenu" class="lg:hidden text-white text-2xl p-1 hover:bg-white/10 rounded-md transition-colors">
                            <i class="bi bi-list"></i>
                        </button>
                        <button id="sidebarToggle" class="hidden lg:flex text-white text-2xl p-1 hover:bg-white/10 rounded-md transition-colors">
                            <i class="bi bi-list"></i>
                        </button>
                        <span class="hidden sm:block text-white font-semibold text-lg ml-2">
                            Welcome, {{ Auth::guard('admin')->user()->full_name }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3 md:gap-6">
                        <!-- Desktop Stats/Info -->
                        <div class="hidden xl:flex items-center gap-4">
                            <!-- Real-Time Clock -->
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-lg text-white/90 text-[0.85rem] font-bold">
                                <i class="bi bi-clock-history text-white/60"></i>
                                <span id="realTimeClock">00:00:00</span>
                            </div>

                            <!-- Chat/Messages Icon -->
                            <a href="{{ route('admin.chat.index') }}" class="flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-lg text-white/90 text-[0.85rem] font-bold hover:bg-white/20 transition-colors relative" title="Messages">
                                <i class="bi bi-chat-dots"></i>
                            </a>

                            <!-- Meetings Icon Dropdown -->
                            <div class="dropdown">
                                <button class="flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-lg text-white/90 text-[0.85rem] font-bold hover:bg-white/20 transition-colors" type="button" id="meetingsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Meetings">
                                    <i class="bi bi-camera-video"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-xl mt-2" aria-labelledby="meetingsDropdown">
                                    <li class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 border-b border-gray-100">Latest Meetings</li>
                                    @forelse($latestMeetings as $meeting)
                                        <li>
                                            <a class="dropdown-item flex flex-col gap-1 py-2 px-4 hover:bg-gray-50 transition-colors" href="{{ route('admin.meetings.index') }}">
                                                <span class="font-bold text-sm text-gray-800">
                                                    {{ $meeting->participant ? ($meeting->participant->name ?? $meeting->participant->full_name) : 'N/A' }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('d M, h:i A') }}
                                                </span>
                                                @if($meeting->started_at)
                                                    <span class="text-xs text-green-600 font-bold flex items-center gap-1">
                                                        <span class="w-2 h-2 bg-green-600 rounded-full inline-block"></span> Active
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @empty
                                        <li><span class="dropdown-item px-4 py-3 text-sm text-gray-500">No recent meetings</span></li>
                                    @endforelse
                                    <li><hr class="dropdown-divider my-0"></li>
                                    <li>
                                        <a class="dropdown-item text-center py-2 text-sm font-bold text-magenta-800 hover:bg-magenta-50 transition-colors" href="{{ route('admin.meetings.index') }}">
                                            View All Meetings
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <button id="darkModeToggle" class="flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-lg text-white/90 text-[0.85rem] font-bold hover:bg-white/20 transition-colors" title="Toggle Dark Mode">
                                <i id="darkIcon" class="bi bi-moon-stars"></i>
                                <i id="lightIcon" class="bi bi-sun hidden"></i>
                            </button>
                        </div>

                        <!-- Logout Form -->
                        <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 border border-white/20 text-white rounded-lg text-sm font-bold hover:bg-white/10 hover:border-white/40 transition-all active:scale-95">
                                <i class="bi bi-box-arrow-right"></i>
                                <span class="hidden xs:block">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Yield Content -->
            <div class="p-4 md:p-8">
                @yield('content')
            </div>



            <style>
                @keyframes pulse-green {
                    0% {
                        transform: scale(1);
                        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
                    }

                    70% {
                        transform: scale(1.05);
                        box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
                    }

                    100% {
                        transform: scale(1);
                        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
                    }
                }

                .pulse-green {
                    animation: pulse-green 2s infinite;
                }
            </style>
        </main>
    </div>

    <!-- Contact Support Modal -->
    <div class="modal fade" id="contactSupportModal" tabindex="-1" aria-labelledby="contactSupportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content !border-none !rounded-2xl overflow-hidden shadow-2xl">
                <div class="modal-header bg-[#800000] text-white border-none py-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="bi bi-telephone-outbound text-2xl"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-bold text-xl" id="contactSupportModalLabel">Contact Us</h5>
                            <p class="text-white/70 text-xs mb-0">Arihanth Jewellers Pvt Ltd</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <!-- Address section -->
                        <div class="p-8 bg-gray-50">
                            <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-6 flex items-center gap-2">
                                <i class="bi bi-geo-alt-fill"></i> Office Address
                            </h6>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                                <p class="font-bold text-gray-900 mb-2">Arihanth Jewellers Pvt Ltd</p>
                                <p class="text-gray-600 text-sm leading-relaxed mb-0">
                                    7th Floor, Prashanth Gold, 1/21,<br>
                                    (39-40/21), North Usman Road,<br>
                                    T.Nagar, Chennai - 600017.
                                </p>
                            </div>

                            <div class="mt-8">
                                <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-4 flex items-center gap-2">
                                    <i class="bi bi-telephone-fill"></i> General Helpline
                                </h6>
                                <div class="space-y-3">
                                    <a href="tel:04428142588" class="flex items-center gap-3 text-sm text-gray-700 !no-underline hover:text-[#800000] transition-colors">
                                        <span class="w-8 h-8 bg-[#800000]/5 rounded-full flex items-center justify-center text-[#800000]"><i class="bi bi-phone"></i></span>
                                        044-2814 2588
                                    </a>
                                    <a href="tel:04442122588" class="flex items-center gap-3 text-sm text-gray-700 !no-underline hover:text-[#800000] transition-colors">
                                        <span class="w-8 h-8 bg-[#800000]/5 rounded-full flex items-center justify-center text-[#800000]"><i class="bi bi-phone"></i></span>
                                        044-4212 2588
                                    </a>
                                    <a href="tel:04428144949" class="flex items-center gap-3 text-sm text-gray-700 !no-underline hover:text-[#800000] transition-colors">
                                        <span class="w-8 h-8 bg-[#800000]/5 rounded-full flex items-center justify-center text-[#800000]"><i class="bi bi-phone"></i></span>
                                        044-2814 4949
                                    </a>
                                    <div class="flex items-center gap-3 text-sm text-gray-500 mt-2 pt-2 border-t border-gray-100">
                                        <span class="font-bold text-gray-900">CENTRAX:</span> 4949 / 9494
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Contact section -->
                        <div class="p-8 bg-white">
                            <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-6">Direct Channels</h6>

                            <div class="space-y-6">
                                <!-- Order Department -->
                                <div class="group/channel">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[0.6rem] font-bold text-gray-400 uppercase tracking-widest">Order Department</span>
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[0.6rem] font-bold rounded">ONLINE</span>
                                    </div>
                                    <div class="p-4 rounded-xl border border-gray-100 bg-white group-hover/channel:border-[#800000]/30 transition-all">
                                        <a href="https://wa.me/919169164949" target="_blank" class="flex items-center gap-3 mb-3 !no-underline group-hover/channel:scale-[1.02] transition-transform">
                                            <div class="w-10 h-10 bg-[#25d366] rounded-full flex items-center justify-center text-white shadow-sm">
                                                <i class="bi bi-whatsapp text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.65rem] text-gray-500 mb-0">WhatsApp / Call</p>
                                                <p class="font-bold text-gray-900 mb-0">+91 9169164949</p>
                                            </div>
                                        </a>
                                        <div class="flex items-center gap-2 mb-3">
                                            <a href="mailto:contactajpl@gmail.com" class="flex-1 flex items-center gap-3 !no-underline">
                                                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                                                    <i class="bi bi-envelope-fill"></i>
                                                </div>
                                                <div>
                                                    <p class="text-[0.65rem] text-gray-500 mb-0">Official Email</p>
                                                    <p class="font-medium text-sm text-gray-700 mb-0">contactajpl@gmail.com</p>
                                                </div>
                                            </a>
                                        </div>

                                    </div>
                                </div>

                                <!-- Accounts Department -->
                                <div class="group/channel">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[0.6rem] font-bold text-gray-400 uppercase tracking-widest">Accounts Department</span>
                                    </div>
                                    <div class="p-4 rounded-xl border border-gray-100 bg-white group-hover/channel:border-[#800000]/30 transition-all">
                                        <a href="tel:+919884111111" class="flex items-center gap-3 mb-3 !no-underline group-hover/channel:scale-[1.02] transition-transform">
                                            <div class="w-10 h-10 bg-[#25d366] rounded-full flex items-center justify-center text-white shadow-sm">
                                                <i class="bi bi-whatsapp text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.65rem] text-gray-500 mb-0">Direct Line</p>
                                                <p class="font-bold text-gray-900 mb-0">+91 9884111111</p>
                                            </div>
                                        </a>
                                        <a href="mailto:arihanthjewellers@gmail.com" class="flex items-center gap-3 !no-underline">
                                            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                                                <i class="bi bi-envelope-fill"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.65rem] text-gray-500 mb-0">Official Email</p>
                                                <p class="font-medium text-sm text-gray-700 mb-0">arihanthjewellers@gmail.com</p>
                                            </div>
                                        </a>
                                    </div>
                                    <br><button type="button" data-bs-toggle="modal" data-bs-target="#viewSizesModal" class="w-full py-2 px-4 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold hover:bg-amber-100 transition-colors flex items-center justify-center gap-2">
                                        <i class="bi bi-rulers"></i> SIZE CHART
                                    </button>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-white text-center">
                    <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-6 flex items-center justify-center gap-2">
                        <i class="bi bi-bank2 text-sm"></i> Official Bank Details
                    </h6>
                    <div class="flex justify-center">
                        <div class="bg-white p-2 rounded-2xl shadow-xl border border-gray-100 inline-block overflow-hidden">
                            <img src="{{ asset('images/image.png') }}"
                                class="rounded-xl w-full max-w-[550px] h-auto object-contain block mx-auto transition-transform duration-500 hover:scale-[1.01]"
                                alt="Axis Bank Details">
                        </div>
                    </div>
                    <p class="mt-4 text-[0.6rem] text-gray-400 font-medium tracking-wide">PLEASE VERIFY ALL DETAILS BEFORE INITIATING BANK TRANSFERS</p>
                </div>
                <div class="modal-footer bg-gray-50 border-t border-gray-100 flex justify-center py-4">
                    <p class="text-[0.65rem] text-gray-400 uppercase tracking-[0.2em] mb-0">Excellence in Gold Since Generations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- View Sizes Modal -->
    <div class="modal fade" id="viewSizesModal" tabindex="-1" aria-labelledby="viewSizesModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-none rounded-2xl overflow-hidden shadow-2xl">
                <div class="modal-header bg-amber-600 text-white border-none py-4">
                    <h5 class="modal-title font-bold" id="viewSizesModalLabel">
                        <i class="bi bi-rulers me-2"></i> Size Chart
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-gray-100 text-center">
                    <img src="{{ asset('images/AJSizes.jpg') }}" alt="Size Chart" class="max-w-full h-auto mx-auto shadow-inner">
                </div>
                <div class="modal-footer bg-white py-3">
                    <button type="button" class="btn btn-secondary rounded-lg px-6" data-bs-toggle="modal" data-bs-target="#contactSupportModal">Back to Contact</button>
                    <button type="button" class="btn btn-dark rounded-lg px-6" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Preview Modal -->
    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Design Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0" style="overflow: auto; max-height: 80vh;" id="modalPreviewContainer">
                    <!-- Content will be injected here by JS -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const hamburgerMenu = document.getElementById('hamburgerMenu');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const overlay = document.getElementById('sidebarOverlay');

            // Toggle functions
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300);
                document.body.classList.remove('overflow-hidden');
            }

            // Hamburger Menu (Mobile)
            if (hamburgerMenu) {
                hamburgerMenu.addEventListener('click', openSidebar);
            }

            // Overlay click to close
            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            // Desktop Sidebar Toggle (Collapse/Expand)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('lg:translate-x-0');
                    sidebar.classList.toggle('lg:-translate-x-full');
                    content.classList.toggle('lg:ml-[260px]');
                });
            }

            // Dark Mode Logic
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkIcon = document.getElementById('darkIcon');
            const lightIcon = document.getElementById('lightIcon');

            function updateIcons() {
                if (document.documentElement.classList.contains('dark')) {
                    darkIcon.classList.add('hidden');
                    lightIcon.classList.remove('hidden');
                } else {
                    darkIcon.classList.remove('hidden');
                    lightIcon.classList.add('hidden');
                }
            }

            // Initial icon state
            updateIcons();

            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                if (document.documentElement.classList.contains('dark')) {
                    localStorage.setItem('theme', 'dark');
                } else {
                    localStorage.setItem('theme', 'light');
                }
                updateIcons();
            });

            // Close sidebar when clicking on a link (mobile only)
            const sidebarLinks = document.querySelectorAll('#sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });

            // Real-time clock
            function updateClock() {
                const now = new Date();
                const clock = document.getElementById('realTimeClock');
                if (clock) {
                    clock.innerText = now.toLocaleTimeString('en-IN', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });
                }
            }
            setInterval(updateClock, 1000);
            updateClock();

            // Live Gold & Silver Rates logic (Admin)
            async function getLiveRates() {
                try {
                    const response = await fetch('/api/live-gold-rates');
                    const data = await response.json();

                    const gold24 = data.gold24;
                    const silver1g = data.silver;

                    // Update Gold display (22K)
                    const gold22 = gold24 * 0.916;
                    const goldFormatter = new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    const goldDisplay = document.getElementById('gold-rate-display');
                    if (goldDisplay) {
                        goldDisplay.innerText = goldFormatter.format(gold22);
                    }

                    // Update Silver display
                    const silverFormatter = new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR',
                        maximumFractionDigits: 2
                    });
                    
                    const silverDisplay = document.getElementById('silver-rate-display');
                    if (silverDisplay) {
                        silverDisplay.innerText = silverFormatter.format(silver1g);
                    }

                } catch (e) {
                    console.error("Live Rate Error:", e);
                    const goldDisplay = document.getElementById('gold-rate-display');
                    if (goldDisplay) {
                        goldDisplay.innerText = "error";
                    }
                }
            }
            getLiveRates();
            setInterval(getLiveRates, 300000); // Update every 5 minutes
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        // PDF Worker
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';


        // ===============================
        // ERP VALUE HIDE FUNCTION
        // ===============================
        async function redactSensitiveText(page, context, viewport) {
            const textContent = await page.getTextContent();
            const items = textContent.items;

            const fieldsToHide = [
                "order id",
                "design",
                "qty",
                "weight",
                "size",
                "product",
                "tolerance from",
                "tolerance to",
                "net from",
                "net to"
            ];

            for (let i = 0; i < items.length; i++) {
                let current = items[i].str.toLowerCase();
                let next = items[i + 1] ? items[i + 1].str.toLowerCase() : "";
                let combined = current + " " + next;

                fieldsToHide.forEach(field => {

                    if (current.includes(field) || combined.includes(field)) {
                        // 🔥 GET LABEL POSITION
                        const labelTx = pdfjsLib.Util.transform(
                            viewport.transform,
                            items[i].transform
                        );

                        const labelY = labelTx[5];

                        // 🔥 FIND VALUE ON SAME LINE
                        for (let k = 0; k < items.length; k++) {
                            const valueTx = pdfjsLib.Util.transform(
                                viewport.transform,
                                items[k].transform
                            );

                            const valueY = valueTx[5];

                            // SAME LINE MATCH
                            if (Math.abs(labelY - valueY) < 3) {
                                const x = valueTx[4];
                                const y = valueTx[5];

                                const width = items[k].width * viewport.scale;
                                const height = items[k].height * viewport.scale;

                                context.fillStyle = "#FFFFFF";

                                context.fillRect(
                                    x - 2,
                                    y - height,
                                    width + 20,
                                    height + 4
                                );
                            }
                        }
                    }

                });
            }

            // SCANNED PDF
            if (items.length == 0) {
                context.fillStyle = "#FFFFFF";

                context.fillRect(
                    viewport.width * 0.04,
                    viewport.height * 0.03,
                    viewport.width * 0.50,
                    viewport.height * 0.20
                );
            }
        }

        // ===============================
        // THUMBNAIL RENDER
        // ===============================
        window.renderPdfThumbnails = function() {
            const canvases = document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');

            canvases.forEach(async canvas => {
                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 100;

                canvas.dataset.rendered = 'true';

                const pdf = await pdfjsLib.getDocument(url).promise;
                const numPages = pdf.numPages;

                // 🔥 MULTI IMAGE BADGE
                if (numPages > 1) {
                    const container = canvas.parentElement;

                    if (container && !container.querySelector('.pdf-page-count-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'pdf-page-count-badge position-absolute bottom-0 end-0 badge rounded-pill bg-dark bg-opacity-75';
                        badge.style.fontSize = '0.6rem';
                        badge.style.padding = '2px 4px';
                        badge.innerText = '+' + (numPages - 1);
                        container.appendChild(badge);
                    }
                }

                const page = await pdf.getPage(1);

                const viewport_raw = page.getViewport({
                    scale: 1.0
                });
                const scale = desiredWidth / viewport_raw.width;
                const viewport = page.getViewport({
                    scale: scale
                });

                const context = canvas.getContext('2d');

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                await page.render(renderContext).promise;

                // 🔥 VALUE REDACTION
                await redactSensitiveText(page, context, viewport);
            });
        };



        // ===============================
        // MODAL PREVIEW
        // ===============================
        window.openUniversalPreview = async function(url, type) {
            const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
            modal.show();

            const container = document.getElementById('modalPreviewContainer');
            container.innerHTML = '';

            if (type === 'pdf') {
                const pdf = await pdfjsLib.getDocument(url).promise;

                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    const canvas = document.createElement('canvas');
                    canvas.className = 'img-fluid mb-2 border-bottom';

                    container.appendChild(canvas);

                    const page = await pdf.getPage(pageNum);
                    const viewport = page.getViewport({
                        scale: 2.0
                    });

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const context = canvas.getContext('2d');

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    await page.render(renderContext).promise;

                    // 🔥 VALUE REDACTION
                    await redactSensitiveText(page, context, viewport);
                }
            } else {
                const img = document.createElement('img');
                img.src = url;
                img.className = 'img-fluid';
                container.appendChild(img);
            }
        };


        // INITIAL LOAD
        document.addEventListener('DOMContentLoaded', function() {
            renderPdfThumbnails();
        });
    </script>

    <!-- Incoming Call Modal -->
    <div class="modal fade" id="incomingCallModal" tabindex="-1" aria-labelledby="incomingCallModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="pulse-green mb-4 d-inline-block rounded-circle" style="background: rgba(37, 211, 102, 0.1); padding: 20px;">
                    <i class="bi bi-camera-video text-success" style="font-size: 3rem;"></i>
                </div>
                <h4 class="font-bold mb-2">Incoming Video Call</h4>
                <p class="text-muted mb-4">You have an incoming consultation from <strong id="callerNameDisplay" class="text-dark">User</strong>.</p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-danger rounded-pill px-4" onclick="declineCall()"><i class="bi bi-telephone-x me-2"></i>Decline</button>
                    <button type="button" id="acceptCallBtn" class="btn btn-success rounded-pill px-4" onclick="acceptCall()"><i class="bi bi-telephone-inbound me-2"></i>Accept</button>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <audio id="ringtoneAudio" loop>
        <source src="https://assets.mixkit.co/active_storage/sfx/2870/2870-preview.mp3" type="audio/mpeg">
    </audio>

    <script type="module">
  // 1. Import the official Firebase version 12 modular modules
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.15.0/firebase-app.js";
  import { getDatabase, ref, onValue, remove } from "https://www.gstatic.com/firebasejs/12.15.0/firebase-database.js";

  // 2. Your authentic web configuration parameters
  const firebaseConfig = {
    apiKey: "AIzaSyD6y5IeHzbDKJJhBoWRaB8q-GsulGVARS4",
    authDomain: "arihanth-1938c.firebaseapp.com",
    databaseURL: "https://arihanth-1938c-default-rtdb.firebaseio.com",
    projectId: "arihanth-1938c",
    storageBucket: "arihanth-1938c.firebasestorage.app",
    messagingSenderId: "601146486892",
    appId: "1:601146486892:web:47c5b8e9a8d84b491aa849",
    measurementId: "G-EXNNGKG84G"
  };

  // 3. Initialize services
  const app = initializeApp(firebaseConfig);
  const database = getDatabase(app);

  const myAdminId = {{ Auth::guard('admin')->id() ?? 'null' }};
  const myCategory = "{{ Auth::guard('admin')->user()->category ?? '' }}".toLowerCase();
  let incomingCallModalInstance = null;
  let currentMeetingId = null;
  let currentRoomId = null;

  // 4. Active real-time sync listener engine
  const activeCallsRef = ref(database, 'active_calls');
  onValue(activeCallsRef, (snapshot) => {
      const calls = snapshot.val();
      if (!calls) return;

      Object.keys(calls).forEach((meetingId) => {
          const callData = calls[meetingId];

          // Scenario A: A call is ringing
          if (callData.status === 'ringing') {
              
              // Category filter: If call has a category and it doesn't match this admin's category, ignore it
              if (callData.category && myCategory && callData.category.toLowerCase() !== myCategory) {
                  return; // Skip this call
              }

              currentMeetingId = callData.meeting_id;
              currentRoomId = callData.room_id;

              const displayElem = document.getElementById('callerNameDisplay');
              if (displayElem) displayElem.innerText = callData.caller_name;
              
              if (!incomingCallModalInstance) {
                  incomingCallModalInstance = new bootstrap.Modal(document.getElementById('incomingCallModal'));
              }
              
              incomingCallModalInstance.show();
              
              const ringtone = document.getElementById('ringtoneAudio');
              if (ringtone) {
                  ringtone.play().catch(err => console.log('Audio waiting for user click gesture first.'));
              }
          }

          // Scenario B: Call picked up by someone else -> Instantly dismiss modal for everyone else!
          if (callData.status === 'answered' && String(callData.meeting_id) === String(currentMeetingId)) {
              if (Number(callData.answered_by) !== myAdminId) {
                  if (incomingCallModalInstance) {
                      incomingCallModalInstance.hide();
                  }
                  
                  const ringtone = document.getElementById('ringtoneAudio');
                  if (ringtone) {
                      ringtone.pause();
                      ringtone.currentTime = 0;
                  }
                  
                  alert('This jewelry consultation has been answered by another administrator.');
                  
                  // Clean up database reference
                  const clearRef = ref(database, 'active_calls/' + meetingId);
                  remove(clearRef);
              }
          }
      });
  });

  // 5. Expose trigger actions globally out of the module wrapper shell boundaries
  window.acceptCall = function() {
      if (!currentMeetingId || !currentRoomId) return;

      fetch(`/admin/meetings/${currentMeetingId}/answer`, {
          method: 'POST',
          headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json',
              'Accept': 'application/json'
          }
      })
      .then(response => response.json())
      .then(data => {
          const clearRef = ref(database, 'active_calls/' + currentMeetingId);
          remove(clearRef);
          window.location.href = `/video-call/` + currentRoomId;
      })
      .catch(err => {
          window.location.href = `/video-call/` + currentRoomId;
      });
  }

  window.declineCall = function() {
      if (incomingCallModalInstance) incomingCallModalInstance.hide();
      const ringtone = document.getElementById('ringtoneAudio');
      if (ringtone) {
          ringtone.pause();
          ringtone.currentTime = 0;
      }
      if (currentMeetingId) {
          const clearRef = ref(database, 'active_calls/' + currentMeetingId);
          remove(clearRef);
      }
  }

  // ============================================================
  // FCM WEB PUSH — Registers browser for push notifications
  // Requires FIREBASE_VAPID_KEY in .env (from Firebase Console
  // → Project Settings → Cloud Messaging → Web Push certificates)
  // ============================================================
  import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.15.0/firebase-messaging.js";

  const VAPID_KEY = '{{ env("FIREBASE_VAPID_KEY", "") }}';

  async function registerAdminFcmToken() {
      // Skip silently if VAPID key not configured yet
      if (!VAPID_KEY || VAPID_KEY === 'your_vapid_key_here' || VAPID_KEY.length < 20) {
          console.info('FCM: VAPID key not configured — skipping web push registration.');
          console.info('FCM: To enable: Firebase Console → Project Settings → Cloud Messaging → Web Push certificates → copy Key pair → set FIREBASE_VAPID_KEY in .env');
          return;
      }

      try {
          if (!('serviceWorker' in navigator)) {
              console.warn('FCM: Service workers not supported.');
              return;
          }

          const messaging = getMessaging(app);
          const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

          const permission = await Notification.requestPermission();
          if (permission !== 'granted') {
              console.warn('FCM: Notification permission denied.');
              return;
          }

          const token = await getToken(messaging, {
              serviceWorkerRegistration: registration,
              vapidKey: VAPID_KEY
          });

          if (!token) {
              console.warn('FCM: Could not get registration token.');
              return;
          }

          await fetch('{{ route("admin.fcm-token.save") }}', {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              },
              body: JSON.stringify({ token })
          });

          console.log('FCM: Admin browser token registered ✓');

          onMessage(messaging, (payload) => {
              console.log('FCM foreground message:', payload);
          });

      } catch (err) {
          // Non-fatal — admin panel uses RTDB active_calls listener as primary notification channel
          console.warn('FCM web push registration skipped:', err.message);
      }
  }

  window.addEventListener('load', registerAdminFcmToken);
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>