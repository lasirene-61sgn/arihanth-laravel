<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Key User Panel')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Add Bootstrap CSS for Modal support -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Reset some bootstrap styles that conflict with tailwind or desired look */
        .modal {
            z-index: 1060;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
            shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }

        .btn-close {
            filter: grayscale(1) invert(1);
        }

        /* ── Key User Amber – Bootstrap 5 overrides ── */
        :root {
            --bs-primary: #b45309;
            --bs-primary-rgb: 180, 83, 9;
            --bs-link-color: #b45309;
            --bs-link-hover-color: #78350f;
        }

        .btn-primary {
            background-color: #b45309 !important;
            border-color: #b45309 !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #78350f !important;
            border-color: #78350f !important;
        }

        .btn-outline-primary {
            color: #b45309 !important;
            border-color: #b45309 !important;
        }

        .btn-outline-primary:hover {
            background-color: #b45309 !important;
            color: #fff !important;
        }

        .nav-tabs .nav-link.active {
            color: #b45309 !important;
            border-bottom-color: #b45309 !important;
        }

        .badge.bg-primary {
            background-color: #b45309 !important;
        }

        .page-item.active .page-link {
            background-color: #b45309 !important;
            border-color: #b45309 !important;
        }

        .page-link {
            color: #b45309 !important;
        }

        a.text-primary {
            color: #b45309 !important;
        }

        .table-primary {
            --bs-table-bg: #fef3c7;
            color: #451a03;
        }
    </style>
</head>

<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }" style="background-color:#fffcf0;">

    <div class="flex h-screen overflow-hidden">

        <div x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden transition-opacity">
        </div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 border-r border-orange-900 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex-shrink-0" style="background: linear-gradient(180deg, #78350f 0%, #b45309 100%); box-shadow: 4px 0 20px rgba(0,0,0,0.3);">
            <div class="flex flex-col h-full">
                <div class="p-6 border-b border-white/10 text-center">
                    <div class="logo-header">
                        <img src="{{ asset('images/tara.png') }}" style="height: 70px;width:70px" alt="AJ Logo">
                        <!-- <div class="logo-box">
            
        </div> -->

                        <!-- <div class="title-box">
            <h1>ARIHANTH JEWELLERS</h1>
            <b><p>KEY USER</p></b>
        </div> -->
                    </div>
                </div>
                <style>
                    .logo-header {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }

                    .logo-box {
                        background: rgba(255, 255, 255, 0.1);
                        padding: 4px;
                        width: 50px;
                        height: 50px;
                        border-radius: 6px;
                        flex-shrink: 0;
                    }

                    .logo-box img {
                        height: 50px;
                        /* reduced logo size */
                        width: 50px;
                        object-fit: contain;
                    }

                    .title-box h1 {
                        font-size: 14px;
                        font-weight: 700;
                        color: #fff;
                        margin: 0;
                        line-height: 1.1;
                        white-space: nowrap;
                        /* prevents next line */
                    }

                    .title-box p {
                        font-size: 14px;
                        color: rgba(255, 255, 255, 0.6);
                        margin: 0;
                        line-height: 1;
                        letter-spacing: 1px;
                    }
                </style>

                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto custom-scrollbar">

                    <a href="{{ route('key-user.dashboard') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.dashboard') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}" style="{{ request()->routeIs('key-user.dashboard') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                        <span>Dashboard</span>
                    </a>

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('product')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('product')))
                    <a href="{{ route('key-user.product.index') }}"
                        class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.product.*') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <span class="flex items-center">
                            <i class="bi bi-box-seam mr-3 text-lg"></i>
                            <span>Products</span>
                        </span>
                        <!-- <span class="inline-flex items-center justify-center px-2 py-0.5 ml-3 text-xs font-semibold rounded-full" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);">
                        <!--    {{ $sidebarCounts['productsCount'] }}-->
                        <!--</span> -->-->
                    </a>
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('design')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('design')))
                    <a href="{{ route('key-user.design.index') }}"
                        class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.design.*') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <span class="flex items-center">
                            <i class="bi bi-palette mr-3 text-lg"></i>
                            <span>Design</span>
                        </span>
                        <!-- <span class="inline-flex items-center justify-center px-2 py-0.5 ml-3 text-xs font-semibold rounded-full" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);">
                            {{ $sidebarCounts['designsCount'] }}
                        </span> -->
                    </a>
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('catalogue')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('catalogue')))
                    <a href="{{ route('key-user.catalogue.index') }}"
                        class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.catalogue.*') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <span class="flex items-center">
                            <i class="bi bi-book mr-3 text-lg"></i>
                            <span>Catalogue</span>
                        </span>
                        <!-- <span class="inline-flex items-center justify-center px-2 py-0.5 ml-3 text-xs font-semibold rounded-full" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);">
                            {{ $sidebarCounts['cataloguesCount'] }}
                        </span> -->
                    </a>
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('work_order')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('work_order')))
                    <a href="{{ route('key-user.work-order.index') }}"
                        class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.work-order.*') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <span class="flex items-center">
                            <i class="bi bi-clipboard mr-3 text-lg"></i>
                            <span>Work Orders</span>
                        </span>
                        <!-- <span class="inline-flex items-center justify-center px-2 py-0.5 ml-3 text-xs font-semibold rounded-full" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);">
                            {{ $sidebarCounts['workOrdersCount'] }}
                        </span> -->
                    </a>
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('user_management')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('user_management')))
                    <a href="{{ route('key-user.user.index') }}"
                        class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.user.*') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <span class="flex items-center">
                            <i class="bi bi-people mr-3 text-lg"></i>
                            <span>Users</span>
                        </span>
                        <!-- <span class="inline-flex items-center justify-center px-2 py-0.5 ml-3 text-xs font-semibold rounded-full" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);">
                            {{ $sidebarCounts['usersCount'] }}
                        </span> -->
                    </a>

                    @php
                    $user = Auth::guard('key_user')->user();
                    $isActualKeyUser = $user && isset($user->user_code);
                    $hasKeyUserPermission = false;

                    if ($user && !$isActualKeyUser) {
                    $buyer = $user->buyer;
                    if ($buyer) {
                    $hasKeyUserPermission = $buyer->hasPermission('key_user');
                    }
                    }
                    @endphp

                    @if($hasKeyUserPermission)
                    <a href="{{ route('key-user.key-user-management.index') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.key-user-management.*') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <i class="bi bi-person-badge mr-3 text-lg"></i>
                        <span>Key Users</span>
                    </a>
                    @endif
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('reports')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('reports')))
                    <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-900">
                        <i class="bi bi-bar-chart mr-3 text-lg"></i>
                        <span>Reports</span>
                    </a>
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('settings')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('settings')))
                    <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-900">
                        <i class="bi bi-gear mr-3 text-lg"></i>
                        <span>Settings</span>
                    </a>
                    @endif

                    @if((Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->hasPermission('finance')) || (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('finance')))
                    <a href="{{ route('key-user.finance.index') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('key-user.finance.index') ? 'text-white font-semibold' : 'text-amber-100 hover:text-white' }}">
                        <i class="bi bi-currency-dollar mr-3 text-lg"></i>
                        <span>Finance</span>
                    </a>
                    @endif

                    <div class="pt-4 pb-2">
                        <span class="px-3 text-[0.65rem] font-bold uppercase tracking-widest text-white/40">Support</span>
                    </div>
                    <a href="#"
                        data-bs-toggle="modal" data-bs-target="#contactSupportModal"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors text-amber-100 hover:text-white">
                        <i class="bi bi-headset mr-3 text-lg"></i>
                        <span>Contact Us</span>
                    </a>

                </nav>
            </div>
        </aside>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            <header class="flex items-center justify-between h-16 px-4 lg:px-8 sticky top-0 z-30" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-bottom: 2px solid #fcd34d; box-shadow: 0 2px 12px rgba(120,53,15,0.08);">
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="p-2 text-gray-600 lg:hidden hover:bg-gray-100 rounded-md">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-sm font-bold text-gray-800">
                            @if(Auth::guard('key_user')->check())
                            {{ Auth::guard('key_user')->user()->full_name ?? 'Key User' }}
                            @elseif(Auth::guard('buyer')->check())
                            {{ Auth::guard('buyer')->user()->name ?? Auth::guard('buyer')->user()->business_name ?? 'Buyer' }}
                            @else
                            User
                            @endif
                        </span>
                        <span class="text-xs text-gray-500 uppercase">
                            @if(Auth::guard('key_user')->check())
                            {{ Auth::guard('key_user')->user()->user_code ?? 'KU0000' }}
                            @elseif(Auth::guard('buyer')->check())
                            {{ Auth::guard('buyer')->user()->bp_code ?? 'BP0000' }}
                            @else
                            XXXXXX
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full" style="background: linear-gradient(135deg, #b45309, #d97706);">
                        @if(Auth::guard('key_user')->check())
                        {{ substr(Auth::guard('key_user')->user()->full_name ?? 'KU', 0, 2) }}
                        @elseif(Auth::guard('buyer')->check())
                        {{ substr(Auth::guard('buyer')->user()->name ?? Auth::guard('buyer')->user()->business_name ?? 'BP', 0, 2) }}
                        @else
                        U
                        @endif
                    </div>

                    <form method="POST" action="{{ route('key-user.logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" class="flex items-center justify-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <i class="bi bi-box-arrow-right md:mr-2"></i>
                            <span class="hidden md:block">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 focus:outline-none p-4 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>

            <!-- Floating Help & Support Button (WhatsApp Style) -->
            <a href="https://wa.me/919169164949?text=Hello,%20I%20need%20support%20with%20my%20work%20order."
                target="_blank"
                class="fixed bottom-8 right-8 w-14 h-14 bg-[#25d366] text-white rounded-full text-center text-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] z-[1000] flex items-center justify-center hover:scale-110 transition-all duration-300 border-none group pulse-green decoration-none"
                title="Chat on WhatsApp">

                <i class="bi bi-whatsapp"></i>

                <span class="absolute right-full mr-4 px-3 py-2 bg-emerald-900 text-white text-[11px] font-bold rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none backdrop-blur-sm shadow-xl">
                    CHAT ON WHATSAPP
                </span>
            </a>

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

            <footer class="px-6 py-4 bg-white border-t border-gray-200">
                <p class="text-sm text-center text-gray-500 md:text-left">
                    &copy; {{ date('Y') }}
                    @if(Auth::guard('key_user')->check()) Key User Panel @elseif(Auth::guard('buyer')->check()) Buyer Panel @else Panel @endif.
                    All rights reserved.
                </p>
            </footer>
        </div>
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

                <div class="modal-body p-0 text-start">
                    <div class="grid grid-cols-1 md:grid-cols-2 border-b border-gray-100">

                        <div class="p-8 bg-gray-50">
                            <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-6 flex items-center gap-2">
                                <i class="bi bi-geo-alt-fill"></i> Office Address
                            </h6>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
                                <p class="font-bold text-gray-900 mb-2 text-sm">Arihanth Jewellers Pvt Ltd</p>
                                <p class="text-gray-600 text-xs leading-relaxed mb-0">
                                    7th Floor, Prashanth Gold, 1/21,<br>
                                    (39-40/21), North Usman Road,<br>
                                    T.Nagar, Chennai - 600017.
                                </p>
                            </div>

                            <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-4 flex items-center gap-2">
                                <i class="bi bi-telephone-fill"></i> General Helpline
                            </h6>
                            <div class="space-y-3 bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                <a href="tel:04428142588" class="flex items-center gap-3 text-xs text-gray-700 !no-underline hover:text-[#800000] transition-colors">
                                    <span class="w-7 h-7 bg-[#800000]/5 rounded-full flex items-center justify-center text-[#800000]"><i class="bi bi-phone"></i></span>
                                    044-2814 2588
                                </a>
                                <a href="tel:04442122588" class="flex items-center gap-3 text-xs text-gray-700 !no-underline hover:text-[#800000] transition-colors">
                                    <span class="w-7 h-7 bg-[#800000]/5 rounded-full flex items-center justify-center text-[#800000]"><i class="bi bi-phone"></i></span>
                                    044-4212 2588
                                </a>
                                <a href="tel:04428144949" class="flex items-center gap-3 text-xs text-gray-700 !no-underline hover:text-[#800000] transition-colors">
                                    <span class="w-7 h-7 bg-[#800000]/5 rounded-full flex items-center justify-center text-[#800000]"><i class="bi bi-phone"></i></span>
                                    044-2814 4949
                                </a>
                                <div class="flex items-center gap-3 text-[0.65rem] text-gray-500 mt-2 pt-2 border-t border-gray-100 uppercase font-bold tracking-tighter">
                                    <span class="text-gray-900">CENTRAX:</span> 4949 / 9494
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-white">
                            <h6 class="text-[#800000] font-bold uppercase tracking-widest text-[0.65rem] mb-6">Direct Channels</h6>
                            <div class="space-y-6">

                                <div class="group/channel">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[0.6rem] font-bold text-gray-400 uppercase tracking-widest">Order Department</span>
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[0.6rem] font-bold rounded flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> ONLINE
                                        </span>
                                    </div>
                                    <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 group-hover/channel:border-[#800000]/30 transition-all">
                                        <a href="https://wa.me/919169164949" target="_blank" class="flex items-center gap-3 mb-3 !no-underline">
                                            <div class="w-9 h-9 bg-[#25d366] rounded-full flex items-center justify-center text-white shadow-sm">
                                                <i class="bi bi-whatsapp text-lg"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.6rem] text-gray-500 mb-0 uppercase font-bold">WhatsApp / Call</p>
                                                <p class="font-bold text-gray-900 text-sm mb-0">+91 9169164949</p>
                                            </div>
                                        </a>
                                        <a href="mailto:contactajpl@gmail.com" class="flex items-center gap-3 !no-underline">
                                            <div class="w-9 h-9 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                                                <i class="bi bi-envelope-fill text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.6rem] text-gray-500 mb-0 uppercase font-bold">Official Email</p>
                                                <p class="font-medium text-[0.7rem] text-gray-700 mb-0">contactajpl@gmail.com</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="group/channel">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[0.6rem] font-bold text-gray-400 uppercase tracking-widest">Accounts Department</span>
                                    </div>
                                    <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 group-hover/channel:border-[#800000]/30 transition-all">
                                        <a href="tel:+919884111111" class="flex items-center gap-3 mb-3 !no-underline">
                                            <div class="w-9 h-9 bg-[#25d366] rounded-full flex items-center justify-center text-white shadow-sm">
                                                <i class="bi bi-whatsapp text-lg"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.6rem] text-gray-500 mb-0 uppercase font-bold">WhatsApp / Call</p>
                                                <p class="font-bold text-gray-900 text-sm mb-0">+91 9884111111</p>
                                            </div>
                                        </a>
                                        <a href="mailto:arihanthjewellers@gmail.com" class="flex items-center gap-3 !no-underline">
                                            <div class="w-9 h-9 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                                                <i class="bi bi-envelope-fill text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-[0.6rem] text-gray-500 mb-0 uppercase font-bold">Official Email</p>
                                                <p class="font-medium text-[0.7rem] text-gray-700 mb-0">arihanthjewellers@gmail.com</p>
                                            </div>
                                        </a>
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
    <div class="modal fade" id="universalPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-2xl">
                <div class="modal-header bg-gray-900 border-0 py-3 px-4">
                    <h5 class="modal-title text-white text-sm font-bold tracking-wider uppercase">Design Preview</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0 bg-gray-100" style="overflow: auto; max-height: 85vh;" id="modalPreviewContainer">
                    <!-- Content will be injected here by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        // PDF.js Worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        // Global function to render PDF thumbnails
        window.renderPdfThumbnails = function() {
            const canvases = document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');
            if (canvases.length === 0) return;

            canvases.forEach(canvas => {
                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 100;
                renderPdfToCanvas(canvas, url, desiredWidth).then(numPages => {
                    if (numPages > 1) {
                        const container = canvas.parentElement;
                        if (container && !container.querySelector('.pdf-page-count-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'pdf-page-count-badge absolute bottom-0 right-0 bg-gray-900 bg-opacity-70 text-white font-bold leading-none px-1.5 py-1 rounded-tl-md';
                            badge.style.fontSize = '9px';
                            badge.innerText = '+' + (numPages - 1);
                            container.appendChild(badge);
                        }
                    }
                });
            });
        }

        // Helper to render a PDF to a specific canvas
        window.renderPdfToCanvas = function(canvas, url, desiredWidth) {
            canvas.dataset.rendered = 'true';
            return pdfjsLib.getDocument(url).promise.then(pdf => {
                const numPages = pdf.numPages;
                return pdf.getPage(1).then(page => {
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

                    return page.render(renderContext).promise.then(() => {
                        // REDACTION
                        context.fillStyle = "#FFFFFF";
                        context.fillRect(0, 0, canvas.width * 0.40, canvas.height * 0.50);
                        return numPages;
                    });
                });
            }).catch(error => {
                console.error('Error rendering PDF to canvas:', error);
                const ctx = canvas.getContext('2d');
                ctx.font = '10px Arial';
                ctx.fillText('PDF Error', 10, 25);
                return 0;
            });
        }

        // Function to open Preview in Modal
        window.openUniversalPreview = function(url, type) {
            const modalEl = document.getElementById('universalPreviewModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            const container = document.getElementById('modalPreviewContainer');
            container.innerHTML = '<div class="flex items-center justify-center p-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div></div>';

            if (type === 'pdf') {
                pdfjsLib.getDocument(url).promise.then(async pdf => {
                    container.innerHTML = '';
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const canvas = document.createElement('canvas');
                        canvas.className = 'max-w-full mx-auto my-4 shadow-lg bg-white rounded-lg';
                        container.appendChild(canvas);

                        const page = await pdf.getPage(pageNum);
                        const viewport_raw = page.getViewport({
                            scale: 1.0
                        });
                        const scale = 2.0;
                        const viewport = page.getViewport({
                            scale: scale
                        });

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        const context = canvas.getContext('2d');
                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };

                        await page.render(renderContext).promise;

                        // REDACTION: Applied to EVERY page
                        context.fillStyle = "#FFFFFF";
                        context.fillRect(0, 0, canvas.width * 0.22, canvas.height * 0.20);
                    }
                }).catch(err => {
                    console.error("PDF Render Error:", err);
                    container.innerHTML = '<div class="p-8 text-red-500 font-bold uppercase tracking-wider">Error rendering PDF</div>';
                });
            } else {
                const img = new Image();
                img.crossOrigin = "Anonymous";
                img.src = url;
                img.onload = function() {
                    container.innerHTML = '';
                    const canvas = document.createElement('canvas');
                    canvas.className = 'max-w-full mx-auto shadow-2xl';
                    container.appendChild(canvas);
                    const context = canvas.getContext('2d');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    context.drawImage(img, 0, 0);

                    // Optional image redaction logic here if needed
                };
                img.onerror = function() {
                    container.innerHTML = '<div class="p-8 text-red-500 font-bold uppercase tracking-wider">Error loading image</div>';
                };
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            renderPdfThumbnails();
        });
    </script>

    @yield('scripts')
</body>

</html>