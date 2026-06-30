<!DOCTYPE html>
<html lang="en" class="h-full" id="html-tag">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Buyer Panel') - ERP System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Dark Mode Transitions */
        .sidebar-transition { transition: transform 0.3s ease, background 0.3s ease; }
        
        /* Dark Mode Palette */
        .dark #html-tag { background-color: #0f172a !important; }
        .dark body { background-color: #0f172a !important; color: #f1f5f9; }
        .dark .main-content-area { background-color: #0f172a !important; }
        .dark header { background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%) !important; border-bottom-color: #4338ca !important; }
        .dark h2, .dark span, .dark h5 { color: #f8fafc !important; }
        .dark .bg-white { background-color: #1e293b !important; color: #f8fafc !important; }
        
        /* FAB Animation */
        #fabMenu.show { display: flex !important; animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-900 transition-colors duration-300" style="background-color:#fdfaff;">

    <div class="flex min-h-screen relative">
        @if(Auth::guard('buyer')->check() || Auth::guard('key_user')->check())
        <aside id="sidebar" class="sidebar-transition fixed inset-y-0 left-0 z-50 w-64 border-r border-purple-900 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 flex flex-col" style="background: linear-gradient(180deg, #2d1b69 0%, #4a1d96 100%); box-shadow: 4px 0 20px rgba(0,0,0,0.3);">
            
            <div class="flex items-center justify-between h-16 px-6" style="border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div class="flex items-center gap-3">
                    <div class="#">
                        <img src="{{ asset('images/taralogo.png') }}" class="h-30 w-30" alt="AJ Logo">
                    </div>
                </div>
                <button id="closeSidebar" class="lg:hidden text-purple-200 hover:text-white">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto py-4 px-4 space-y-1">
                <nav class="space-y-1">
                    <a href="{{ route('buyer.dashboard') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.dashboard') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.dashboard') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <i class="bi bi-speedometer2 text-lg mr-3"></i> 
                        <span>Dashboard</span>
                    </a>

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('product'))
                    <a href="{{ route('buyer.product.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.product.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.product.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-box-seam text-lg mr-3"></i> <span>Products</span></span>
                    </a>
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('work_order'))
                    <a href="{{ route('buyer.work-order.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.work-order.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.work-order.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-file-earmark-text text-lg mr-3"></i> <span>Work Orders</span></span>
                    </a>
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('stock_order'))
                    <a href="{{ route('buyer.stock-order.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.stock-order.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.stock-order.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-box2-heart text-lg mr-3"></i> <span>Live Stock Order</span></span>
                    </a>
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('work_order'))
                    <a href="{{ route('buyer.repairs.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.repairs.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.repairs.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-tools text-lg mr-3"></i> <span>Repairs</span></span>
                    </a>
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('design'))
                    <a href="{{ route('buyer.design.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.design.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.design.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-palette text-lg mr-3"></i> <span>Designs</span></span>
                    </a>

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('favorites'))
                    <a href="{{ route('buyer.favorites.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.favorites.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.favorites.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-heart text-lg mr-3"></i> <span>Favorites</span></span>
                    </a>
                    @endif
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('catalogue'))
                    <a href="{{ route('buyer.catalogue.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.catalogue.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.catalogue.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-book text-lg mr-3"></i> <span>Catalogues</span></span>
                    </a>
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('key_user'))
                    <a href="{{ route('buyer.key-user-management.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.key-user-management.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.key-user-management.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <span class="flex items-center"><i class="bi bi-person-badge text-lg mr-3"></i> <span>Key Users</span></span>
                    </a>
                    @endif

                    @if(Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->hasPermission('finance'))
                    <a href="{{ route('buyer.finance.index') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.finance.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.finance.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <i class="bi bi-currency-dollar text-lg mr-3"></i> 
                        <span>Finance</span>
                    </a>
                    @endif
                </nav>

                <div class="pt-6 mt-6" style="border-top: 1px solid rgba(255,255,255,0.12);">
                    <h6 class="px-3 mb-2 text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,0.4);">Account</h6>
                    <a href="{{ route('buyer.profile.edit') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.profile.edit') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.profile.edit') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <i class="bi bi-person text-lg mr-3"></i>
                        <span>My Profile</span>
                    </a>

                    <a href="{{ route('buyer.chat.index') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.chat.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.chat.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <i class="bi bi-chat-dots text-lg mr-3"></i>
                        <span>Messages</span>
                    </a>

                    <a href="{{ route('buyer.meetings.index') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('buyer.meetings.*') ? 'text-white font-semibold' : 'text-purple-200 hover:text-white' }}" style="{{ request()->routeIs('buyer.meetings.*') ? 'background:rgba(255,255,255,0.15)' : '' }}">
                        <i class="bi bi-calendar-check text-lg mr-3"></i>
                        <span>Meetings</span>
                    </a>
                </div>
            </div>
        </aside>
        @endif

        <div class="flex flex-col flex-1 min-w-0 main-content-area" style="background-color:#fdfaff;">
            
            <header class="flex items-center justify-between h-16 px-4 md:px-8 sticky top-0 z-40" style="background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 100%); border-bottom: 2px solid #c4b5fd; box-shadow: 0 2px 12px rgba(109,40,217,0.08);">
                <div class="flex items-center">
                    @if(Auth::guard('buyer')->check() || Auth::guard('key_user')->check())
                    <button id="sidebarToggle" class="p-2 -ml-2 text-slate-600 lg:hidden hover:bg-slate-50 rounded-lg">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    @endif
                    <h2 class="ml-4 text-lg font-bold text-slate-800 hidden sm:block">Welcome, {{ Auth::guard('buyer')->check() ? Auth::guard('buyer')->user()->name : (Auth::guard('key_user')->check() ? Auth::guard('key_user')->user()->full_name : 'User') }}</h2>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button id="darkModeToggle" class="p-2 text-purple-600 hover:bg-purple-100 rounded-full transition-all">
                        <i class="bi bi-moon-stars text-xl" id="darkIcon"></i>
                    </button>

                    <!-- Nav Icons -->
                    <div class="hidden md:flex items-center space-x-3 mr-2">
                        <a href="{{ route('buyer.chat.index') }}" class="text-purple-600 hover:text-purple-800 transition-colors">
                            <i class="bi bi-chat-dots text-xl"></i>
                        </a>
                        <div class="dropdown">
                            <button class="text-purple-600 hover:text-purple-800 transition-colors" type="button" id="meetingsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Meetings">
                                <i class="bi bi-calendar-check text-xl"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-xl mt-2" aria-labelledby="meetingsDropdown">
                                <li class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 border-b border-gray-100">Latest Meetings</li>
                                @forelse($latestMeetings as $meeting)
                                    <li>
                                        <div class="dropdown-item flex flex-col gap-1 py-2 px-4 hover:bg-gray-50 transition-colors">
                                            <span class="font-bold text-sm text-gray-800">
                                                {{ $meeting->host ? ($meeting->host->name ?? $meeting->host->full_name) : 'N/A' }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('d M, h:i A') }}
                                            </span>
                                            @if($meeting->started_at)
                                                <a href="{{ route('video.join', $meeting->room_id) }}" class="mt-1 inline-flex items-center justify-center px-3 py-1 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition-colors">
                                                    Join Now
                                                </a>
                                            @endif
                                        </div>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item px-4 py-3 text-sm text-gray-500">No recent meetings</span></li>
                                @endforelse
                                <li><hr class="dropdown-divider my-0"></li>
                                <li>
                                    <a class="dropdown-item text-center py-2 text-sm font-bold text-purple-600 hover:bg-purple-50 transition-colors" href="{{ route('buyer.meetings.index') }}">
                                        View All Meetings
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if(Auth::guard('buyer')->check() || Auth::guard('key_user')->check())
                        <div class="hidden sm:flex flex-col items-end leading-tight">
                            <span class="text-sm font-bold text-slate-900">
                                {{ Auth::guard('buyer')->check() ? (Auth::guard('buyer')->user()->business_name ?? Auth::guard('buyer')->user()->name) : Auth::guard('key_user')->user()->full_name }}
                            </span>
                            <span class="text-xs text-slate-500">
                                {{ Auth::guard('buyer')->check() ? Auth::guard('buyer')->user()->bp_code : Auth::guard('key_user')->user()->user_code }}
                            </span>
                        </div>

                        <div class="w-10 h-10 flex items-center justify-center rounded-full text-white font-bold text-sm shadow-sm" style="background: linear-gradient(135deg, #6d28d9, #7c3aed);">
                            {{ substr(Auth::guard('buyer')->check() ? (Auth::guard('buyer')->user()->business_name ?? Auth::guard('buyer')->user()->name) : Auth::guard('key_user')->user()->full_name, 0, 2) }}
                        </div>

                        <form method="POST" action="{{ route('buyer.logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                <i class="bi bi-box-arrow-right sm:mr-2"></i>
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    @endif
                </div>
            </header>

            <main class="flex-1 p-4 md:p-8">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>

        <!-- Floating Action Button Code -->
        <div class="fixed bottom-6 right-6 z-50 flex flex-col-reverse items-center gap-3">
            <button id="fabToggle" class="w-14 h-14 bg-purple-700 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-purple-800 transition-all transform active:scale-90">
                <i class="bi bi-plus-lg text-2xl" id="fabMainIcon"></i>
            </button>
            <div id="fabMenu" class="hidden flex flex-col gap-3 mb-2">
                <a href="{{ route('buyer.meetings.index') }}" title="Meetings" class="w-12 h-12 bg-white text-purple-700 rounded-full shadow-lg flex items-center justify-center border border-purple-100 hover:bg-purple-50 transition-all">
                    <i class="bi bi-calendar-check text-xl"></i>
                </a>
                <a href="{{ route('buyer.chat.index') }}" title="Messages" class="w-12 h-12 bg-white text-purple-700 rounded-full shadow-lg flex items-center justify-center border border-purple-100 hover:bg-purple-50 transition-all">
                    <i class="bi bi-chat-dots text-xl"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="overlay" class="fixed inset-0 bg-slate-900/40 z-40 hidden lg:hidden"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

    <script>
        // Sidebar Logic
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // FAB Logic
        const fabToggle = document.getElementById('fabToggle');
        const fabMenu = document.getElementById('fabMenu');
        const fabMainIcon = document.getElementById('fabMainIcon');

        fabToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            fabMenu.classList.toggle('hidden');
            fabMenu.classList.toggle('show');
            fabMainIcon.classList.toggle('bi-plus-lg');
            fabMainIcon.classList.toggle('bi-x-lg');
        });

        // Dark Mode Logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlTag = document.getElementById('html-tag');
        const darkIcon = document.getElementById('darkIcon');

        darkModeToggle.addEventListener('click', () => {
            htmlTag.classList.toggle('dark');
            const isDark = htmlTag.classList.contains('dark');
            darkIcon.classList.toggle('bi-moon-stars', !isDark);
            darkIcon.classList.toggle('bi-sun', isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Check local storage for theme
        if (localStorage.getItem('theme') === 'dark') {
            htmlTag.classList.add('dark');
            darkIcon.classList.replace('bi-moon-stars', 'bi-sun');
        }
    </script>

    <!-- PDF Logic (Unchanged) -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        window.renderPdfThumbnails = function() {
            const canvases = document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');
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

        window.renderPdfToCanvas = function(canvas, url, desiredWidth) {
            canvas.dataset.rendered = 'true';
            return pdfjsLib.getDocument(url).promise.then(pdf => {
                const numPages = pdf.numPages;
                return pdf.getPage(1).then(page => {
                    const viewport_raw = page.getViewport({ scale: 1.0 });
                    const scale = desiredWidth / viewport_raw.width;
                    const viewport = page.getViewport({ scale: scale });
                    const context = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    return page.render({ canvasContext: context, viewport: viewport }).promise.then(() => {
                        context.fillStyle = "#FFFFFF";
                        context.fillRect(0, 0, canvas.width * 0.40, canvas.height * 0.50);
                        return numPages;
                    });
                });
            }).catch(err => 0);
        }

        window.openUniversalPreview = function(url, type) {
            const modalEl = document.getElementById('universalPreviewModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            const container = document.getElementById('modalPreviewContainer');
            container.innerHTML = '<div class="p-12 text-center"><div class="spinner-border text-primary"></div></div>';

            if (type === 'pdf') {
                pdfjsLib.getDocument(url).promise.then(async pdf => {
                    container.innerHTML = '';
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const canvas = document.createElement('canvas');
                        canvas.className = 'max-w-full mx-auto my-4 shadow-lg bg-white rounded-lg';
                        container.appendChild(canvas);
                        const page = await pdf.getPage(pageNum);
                        const viewport = page.getViewport({ scale: 2.0 });
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        const context = canvas.getContext('2d');
                        await page.render({ canvasContext: context, viewport: viewport }).promise;
                        context.fillStyle = "#FFFFFF";
                        context.fillRect(0, 0, canvas.width * 0.22, canvas.height * 0.20);
                    }
                });
            } else {
                const img = new Image();
                img.src = url;
                img.onload = () => {
                    container.innerHTML = `<img src="${url}" class="img-fluid">`;
                };
            }
        };

        document.addEventListener('DOMContentLoaded', renderPdfThumbnails);
    </script>
    
    @yield('scripts')

    <!-- PDF Preview Modal -->
    <div class="modal fade" id="universalPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content overflow-hidden border-0 shadow-2xl">
                <div class="modal-header bg-gray-900 border-0 py-3 px-4">
                    <h5 class="modal-title text-white text-sm font-bold tracking-wider uppercase">Design Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0 bg-gray-100" style="overflow: auto; max-height: 85vh;" id="modalPreviewContainer"></div>
            </div>
        </div>
    </div>
</body>
</html>