<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" id="html-root" class="tw-h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Panel')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- PDF & Custom JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="{{ asset('js/superadminlayout.js') }}"></script>

    <!-- Tailwind Configuration for Dark Mode -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            DEFAULT: '#800000',
                            dark: '#5a0000',
                        }
                    }
                }
            }
        }
    </script>
    @yield('styles')
</head>

<body class="tw-text-gray-800 tw-h-screen tw-flex tw-overflow-hidden tw-bg-gray-50 tw-font-sans tw-transition-colors tw-duration-300 dark:tw-bg-slate-950 dark:tw-text-gray-200">

    <!-- Sidebar -->
    <aside id="sidebar-wrapper" class="tw-w-72 tw-flex-shrink-0 tw-flex tw-flex-col tw-h-full tw-shadow-2xl tw-fixed md:tw-relative tw-z-50 tw-transition-all tw-duration-300 -tw-ml-72 md:tw-ml-0 tw-bg-maroon dark:tw-bg-slate-900 tw-border-r tw-border-white/10">
        <!-- Logo Area -->
        <div class="tw-h-16 tw-flex tw-items-center tw-px-6 tw-border-b tw-border-white/15">
            <div class="tw-flex tw-items-center tw-gap-3">
                <div class="tw-text-white tw-p-1">
                    <img src="{{ asset('images/taralogo.png') }}" class="tw-h-17 tw-w-auto" alt="Logo">
                </div>
            </div>
            <button id="sidebar-close" class="md:tw-hidden tw-ml-auto tw-text-white/60">
                <i class="bi bi-x-lg tw-text-xl"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <div class="tw-flex-1 tw-overflow-y-auto tw-py-4 tw-px-3">
            <ul class="tw-space-y-1">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('super-admin.dashboard') }}"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-duration-200 tw-no-underline hover:tw-bg-white/10 hover:tw-text-white {{ request()->routeIs('super-admin.dashboard') ? 'tw-bg-white/20 tw-text-white' : 'tw-text-white/80' }}">
                        <i class="bi bi-speedometer2 tw-text-lg"></i>
                        <span>{{ __('messages.dashboard') }}</span>
                    </a>
                </li>
                
                <!-- Global Search -->
                <li>
                    <a href="{{ route('super-admin.global-search') }}"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-duration-200 tw-no-underline hover:tw-bg-white/10 hover:tw-text-white {{ request()->routeIs('super-admin.global-search') ? 'tw-bg-white/20 tw-text-white' : 'tw-text-white/80' }}">
                        <i class="bi bi-search tw-text-lg"></i>
                        <span>Search</span>
                    </a>
                </li>

                <!-- Business Partner Dropdown -->
                @if(Auth::guard('super_admin')->user()?->hasPermission('business_partner'))
                <li>
                    <button data-bs-toggle="collapse" data-bs-target="#businessPartnerSubmenu"
                        class="tw-w-full tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-duration-200 tw-text-white/80 hover:tw-bg-white/10 hover:tw-text-white">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <i class="bi bi-people tw-text-lg"></i>
                            <span>{{ __('messages.business_partner') }}</span>
                        </div>
                        <i class="bi bi-chevron-down tw-text-xs"></i>
                    </button>

                    <div class="collapse {{ request()->routeIs('super-admin.business-partner.*') ? 'show' : '' }} tw-mt-1" id="businessPartnerSubmenu">
                        <ul class="tw-space-y-1 tw-pl-9 tw-mt-1">
                            <li>
                                <a href="{{ route('super-admin.business-partner.index') }}"
                                    class="tw-block tw-py-2 tw-text-[13px] tw-no-underline tw-transition-all hover:tw-text-white {{ request()->routeIs('super-admin.business-partner.index') ? 'tw-text-white tw-font-bold' : 'tw-text-white/60' }}">
                                    {{ __('messages.overview') }}
                                </a>
                            </li>
                    
                            <li>
                                <a href="{{ route('super-admin.business-partner.buyer') }}"
                                    class="tw-flex tw-items-center tw-justify-between tw-py-2 tw-text-[13px] tw-no-underline tw-transition-all hover:tw-text-white {{ request()->routeIs('super-admin.business-partner.buyer') ? 'tw-text-white tw-font-bold' : 'tw-text-white/60' }}">
                                    <span>{{ __('messages.buyer') }}</span>
                                    <span class="tw-bg-white/10 tw-px-2 tw-py-0.5 tw-rounded tw-text-[10px]">{{ $sidebarCounts['buyersCount'] }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('super-admin.business-partner.craftman') }}"
                                    class="tw-flex tw-items-center tw-justify-between tw-py-2 tw-text-[13px] tw-no-underline tw-transition-all hover:tw-text-white {{ request()->routeIs('super-admin.business-partner.craftman') ? 'tw-text-white tw-font-bold' : 'tw-text-white/60' }}">
                                    <span>{{ __('messages.craftsman') }}</span>
                                    <span class="tw-bg-white/10 tw-px-2 tw-py-0.5 tw-rounded tw-text-[10px]">{{ $sidebarCounts['craftsmenCount'] }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                <li class="tw-px-4 tw-mt-6 tw-mb-2">
                    <span class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-text-white/40">{{ __('messages.management') }}</span>
                </li>

                <!-- Admin & Users -->
                @php
                $navItems = [
                ['perm' => 'admin_management', 'route' => 'super-admin.admin.index', 'icon' => 'bi-person-badge', 'label' => __('messages.admin_users'), 'count' => $sidebarCounts['adminsCount']],
                ['perm' => 'admin_management', 'route' => 'super-admin.registrations.index', 'icon' => 'bi-person-plus', 'label' => __('messages.registrations'), 'count' => $sidebarCounts['pendingRegistrationsCount']],
                ['perm' => 'key_user_management', 'route' => 'super-admin.key-user.index', 'icon' => 'bi-key', 'label' => __('messages.key_users'), 'count' => $sidebarCounts['keyUsersCount']],
                ['perm' => 'can_create_staff', 'route' => 'super-admin.business-partner.craftsman-staff', 'icon' => 'bi-people', 'label' => 'Craftsman Staff', 'count' => null],
                ['perm' => 'user_management', 'route' => 'super-admin.user.index', 'icon' => 'bi-person-circle', 'label' => 'Users', 'count' => null],
                ['perm' => 'user_management', 'route' => 'super-admin.user-credentials.index', 'icon' => 'bi-shield-lock', 'label' => 'Credentials', 'count' => null],
                ['perm' => 'admin_management', 'route' => 'super-admin.freeze-account.index', 'icon' => 'bi-snow', 'label' => 'Freeze Accounts', 'count' => null],
                ];
                @endphp

                @foreach($navItems as $item)
                @if(Auth::guard('super_admin')->user()?->hasPermission($item['perm']))
                <li>
                    <a href="{{ route($item['route']) }}"
                        class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-no-underline hover:tw-bg-white/10 hover:tw-text-white {{ request()->routeIs($item['route']) ? 'tw-bg-white/20 tw-text-white' : 'tw-text-white/80' }}">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <i class="bi {{ $item['icon'] }} tw-text-lg"></i>
                            <span>{{ $item['label'] }}</span>
                        </div>
                        @if($item['count'])
                        <span class="tw-bg-white/10 tw-px-2 tw-py-0.5 tw-rounded tw-text-[10px]">{{ $item['count'] }}</span>
                        @endif
                    </a>
                </li>
                @endif
                @endforeach

                <li class="tw-px-4 tw-mt-6 tw-mb-2">
                    <span class="tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-text-white/40">{{ __('messages.operations') }}</span>
                </li>

                <!-- Operations Items -->
                @php
                $opItems = [
                ['perm' => 'work_order', 'route' => 'super-admin.work-order.index', 'icon' => 'bi-clipboard-check', 'label' => __('messages.work_order'), 'count' => $sidebarCounts['workOrdersCount']],
                ['perm' => 'stock_order', 'route' => 'super-admin.stock-order.index', 'icon' => 'bi-box2-heart', 'label' => __('messages.live_stock_order'), 'count' => $sidebarCounts['stockOrdersCount']],
                ['perm' => 'meetings', 'route' => 'super-admin.meetings.index', 'icon' => 'bi-camera-video', 'label' => 'Meetings', 'count' => null],
                ['perm' => 'work_order', 'route' => 'super-admin.repairs.index', 'icon' => 'bi-tools', 'label' => __('messages.repairs'), 'count' => $sidebarCounts['repairsCount']],
                ['perm' => 'purchase_order', 'route' => 'super-admin.purchase-order.index', 'icon' => 'bi-cart3', 'label' => __('messages.purchase_order'), 'count' => $sidebarCounts['purchaseOrdersCount']],
                ['perm' => 'product', 'route' => 'super-admin.product.index', 'icon' => 'bi-box-seam', 'label' => __('messages.product'), 'count' => $sidebarCounts['productsCount']],
                ['perm' => 'design', 'route' => 'super-admin.design.index', 'icon' => 'bi-palette', 'label' => __('messages.design'), 'count' => $sidebarCounts['designsCount']],
                ['perm' => 'catalogue', 'route' => 'super-admin.catalogue.index', 'icon' => 'bi-book', 'label' => __('messages.catalogue'), 'count' => $sidebarCounts['cataloguesCount']],
                ['perm' => 'design', 'route' => 'super-admin.favorites.index', 'icon' => 'bi-heart', 'label' => 'Favorites', 'count' => null],
                ['perm' => 'updates', 'route' => 'super-admin.updates.index', 'icon' => 'bi-heart', 'label' => 'Updates', 'count' => null],
                ];
                @endphp

                @foreach($opItems as $op)
                @if(Auth::guard('super_admin')->user()?->hasPermission($op['perm']))
                <li>
                    <a href="{{ route($op['route']) }}"
                        class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-no-underline hover:tw-bg-white/10 hover:tw-text-white {{ request()->routeIs($op['route']) ? 'tw-bg-white/20 tw-text-white' : 'tw-text-white/80' }}">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <i class="bi {{ $op['icon'] }} tw-text-lg"></i>
                            <span>{{ $op['label'] }}</span>
                        </div>
                        @if($op['count'])
                        <span class="tw-bg-white/10 tw-px-2 tw-py-0.5 tw-rounded tw-text-[10px]">{{ $op['count'] }}</span>
                        @endif
                    </a>
                </li>
                @endif
                @endforeach
                <li>
                    <a href="{{ route('super-admin.craftsman-production.index') }}"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-duration-200 tw-no-underline hover:tw-text-white hover:tw-bg-white/15 {{ request()->routeIs('super-admin.craftsman-production.*') ? 'tw-bg-white/20 tw-text-white tw-font-semibold' : 'tw-text-white/80' }}">
                        <i class="bi bi-person-gear tw-text-lg"></i>
                        <span>Craftsman Production</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('super-admin.chat.index') }}"
                        class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-2.5 tw-rounded-lg tw-font-medium tw-text-sm tw-transition-all tw-duration-200 tw-no-underline hover:tw-text-white hover:tw-bg-white/15 {{ request()->routeIs('super-admin.chat.*') ? 'tw-bg-white/20 tw-text-white tw-font-semibold' : 'tw-text-white/80' }}">
                        <i class="bi bi-chat-dots tw-text-lg"></i>
                        <span>Messages</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- User Profile (Bottom) -->
        <div class="tw-p-4 tw-bg-maroon-dark dark:tw-bg-slate-950 tw-border-t tw-border-white/10">
            <div class="tw-flex tw-items-center tw-gap-3">
                <div class="tw-w-9 tw-h-9 tw-rounded-full tw-bg-white/20 tw-text-white tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-sm">
                    {{ substr(Auth::guard('super_admin')->user()->first_name ?? 'U', 0, 1) }}
                </div>
                <div class="tw-flex-1 tw-min-w-0">
                    <p class="tw-text-xs tw-font-bold tw-text-white tw-truncate">{{ Auth::guard('super_admin')->user()->full_name ?? 'Admin' }}</p>
                    <p class="tw-text-[10px] tw-text-white/50 tw-uppercase">Super Admin</p>
                </div>
                <form method="POST" action="{{ route('super-admin.logout') }}">
                    @csrf
                    <button type="submit" class="tw-text-white/60 hover:tw-text-white">
                        <i class="bi bi-box-arrow-right tw-text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Overlay -->
    <div id="sidebar-overlay" class="tw-fixed tw-inset-0 tw-bg-black/50 tw-z-40 tw-hidden"></div>

    <!-- Content -->
    <div class="tw-flex-1 tw-flex tw-flex-col tw-overflow-hidden">
        <!-- Header -->
        <header class="tw-h-16 tw-flex tw-items-center tw-justify-between tw-px-6 tw-bg-maroon dark:tw-bg-slate-900 tw-shadow-md tw-z-30">
            <button id="sidebar-toggle" class="tw-text-white hover:tw-bg-white/10 tw-p-2 tw-rounded-lg">
                <i class="bi bi-list tw-text-2xl"></i>
            </button>

            <div class="tw-flex-1 tw-text-center">
                <h1 class="tw-text-white tw-text-xl tw-font-black tw-tracking-[0.15em] tw-uppercase">
                    ARIHANTH JEWELLERS
                </h1>
            </div>

            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-hidden lg:tw-flex tw-flex-col tw-items-end tw-border-r tw-border-white/20 tw-pr-4">
                    <span class="tw-text-[9px] tw-font-bold tw-text-white/60 tw-uppercase">India Time (IST)</span>
                    <span id="ist-clock" class="tw-text-sm tw-font-mono tw-font-bold tw-text-white">00:00:00</span>
                </div>

                <button id="dark-mode-toggle" class="tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-xl tw-bg-white/10 tw-text-white hover:tw-bg-white/20 tw-transition-all">
                    <i id="theme-icon" class="bi bi-moon-stars tw-text-lg"></i>
                </button>

                <div class="tw-relative">
                    <button class="tw-text-white/80 hover:tw-text-white">
                        <i class="bi bi-bell tw-text-xl"></i>
                        <span class="tw-absolute tw-top-0 tw-right-0 tw-w-2 tw-h-2 tw-bg-yellow-400 tw-rounded-full tw-border tw-border-maroon"></span>
                    </button>
                </div>
                <div class="tw-flex tw-items-center tw-gap-4">

                    <div class="tw-relative">
                        <a href="{{ route('super-admin.chat.index') }}" class="tw-text-white/80 hover:tw-text-white tw-transition-all tw-duration-200">
                            <i class="bi bi-chat-left-text tw-text-xl"></i>
                            <!-- Message Count Badge -->

                        </a>
                    </div>
                    <!-- Meetings Icon Dropdown -->
                    <div class="tw-relative dropdown">
                        <button class="tw-text-white/80 hover:tw-text-white tw-transition-colors" type="button" id="meetingsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Meetings">
                            <i class="bi bi-camera-video tw-text-xl"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end tw-shadow-2xl tw-border-0 tw-rounded-xl tw-mt-2" aria-labelledby="meetingsDropdown">
                            <li class="tw-px-4 tw-py-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-gray-500 tw-border-b tw-border-gray-100">Latest Meetings</li>
                            @forelse($latestMeetings as $meeting)
                                <li>
                                    <a class="dropdown-item tw-flex tw-flex-col tw-gap-1 tw-py-2 tw-px-4 hover:tw-bg-gray-50 tw-transition-colors" href="{{ route('super-admin.meetings.index') }}">
                                        <span class="tw-font-bold tw-text-sm tw-text-gray-800">
                                            {{ $meeting->participant ? ($meeting->participant->name ?? $meeting->participant->full_name) : 'N/A' }}
                                        </span>
                                        <span class="tw-text-xs tw-text-gray-500">
                                            {{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('d M, h:i A') }}
                                        </span>
                                        @if($meeting->started_at)
                                            <span class="tw-text-xs tw-text-green-600 tw-font-bold tw-flex tw-items-center tw-gap-1">
                                                <span class="tw-w-2 tw-h-2 tw-bg-green-600 tw-rounded-full tw-inline-block"></span> Active
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item tw-px-4 tw-py-3 tw-text-sm tw-text-gray-500">No recent meetings</span></li>
                            @endforelse
                            <li><hr class="dropdown-divider tw-my-0"></li>
                            <li>
                                <a class="dropdown-item tw-text-center tw-py-2 tw-text-sm tw-font-bold tw-text-maroon hover:tw-bg-maroon/5 tw-transition-colors" href="{{ route('super-admin.meetings.index') }}">
                                    View All Meetings
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </header>

        <!-- Main Content -->
        <main class="tw-flex-1 tw-overflow-y-auto tw-p-6 tw-bg-gray-50 dark:tw-bg-slate-950">
            @if(session('success'))
            <div class="alert alert-success tw-border-0 tw-shadow-sm tw-rounded-xl mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div class="alert alert-danger tw-border-0 tw-shadow-sm tw-rounded-xl mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') ?: 'Please correct the errors below.' }}
            </div>
            @endif

            <div class="tw-animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- PDF Modal -->
    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content dark:tw-bg-slate-900 dark:tw-border-slate-700">
                <div class="modal-header dark:tw-border-slate-700">
                    <h5 class="modal-title dark:tw-text-white">Design Preview</h5>
                    <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0" style="overflow: auto; max-height: 80vh;" id="modalPreviewContainer"></div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- FCM Web Push: Register super-admin browser & save token to DB --}}
    <script type="module">
      import { initializeApp } from "https://www.gstatic.com/firebasejs/12.15.0/firebase-app.js";
      import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.15.0/firebase-messaging.js";

      const firebaseConfig = {
          apiKey: "AIzaSyD6y5IeHzbDKJJhBoWRaB8q-GsulGVARS4",
          authDomain: "arihanth-1938c.firebaseapp.com",
          databaseURL: "https://arihanth-1938c-default-rtdb.firebaseio.com",
          projectId: "arihanth-1938c",
          storageBucket: "arihanth-1938c.firebasestorage.app",
          messagingSenderId: "601146486892",
          appId: "1:601146486892:web:47c5b8e9a8d84b491aa849"
      };

      const app = initializeApp(firebaseConfig, 'super-admin-messaging');
      const VAPID_KEY = '{{ env("FIREBASE_VAPID_KEY", "") }}';

      async function registerSuperAdminFcmToken() {
          // Skip silently if VAPID key is not configured
          if (!VAPID_KEY || VAPID_KEY === 'your_vapid_key_here' || VAPID_KEY.length < 20) {
              console.info('FCM: VAPID key not configured — skipping web push registration.');
              console.info('FCM: Get key from: Firebase Console → Project Settings → Cloud Messaging → Web Push certificates');
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
                  console.warn('FCM: Could not retrieve registration token.');
                  return;
              }

              await fetch('{{ route("super-admin.fcm-token.save") }}', {
                  method: 'POST',
                  headers: {
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Content-Type': 'application/json',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ token })
              });

              console.log('FCM: Super-admin browser token registered ✓');

              onMessage(messaging, (payload) => {
                  console.log('FCM foreground message (super-admin):', payload);
              });

          } catch (err) {
              console.warn('FCM web push registration skipped:', err.message);
          }
      }

      window.addEventListener('load', registerSuperAdminFcmToken);
    </script>

    <script>
        // Theme Logic
        const themeToggle = document.getElementById('dark-mode-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const root = document.getElementById('html-root');

        function updateTheme(isDark) {
            if (isDark) {
                root.classList.add('tw-dark');
                themeIcon.classList.replace('bi-moon-stars', 'bi-sun');
            } else {
                root.classList.remove('tw-dark');
                themeIcon.classList.replace('bi-sun', 'bi-moon-stars');
            }
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        themeToggle.addEventListener('click', () => {
            updateTheme(!root.classList.contains('tw-dark'));
        });

        // Initialize theme
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            updateTheme(true);
        }

        // Sidebar Toggle
        const wrapper = document.getElementById('sidebar-wrapper');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('sidebar-toggle');
        const close = document.getElementById('sidebar-close');

        function toggleSidebar() {
            wrapper.classList.toggle('-tw-ml-72');
            overlay.classList.toggle('tw-hidden');
        }

        toggle.onclick = toggleSidebar;
        if (close) close.onclick = toggleSidebar;
        overlay.onclick = toggleSidebar;

        // Clock Logic
        function updateClock() {
            const now = new Date().toLocaleTimeString('en-IN', {
                timeZone: 'Asia/Kolkata',
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('ist-clock').textContent = now;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @yield('scripts')
</body>

</html>