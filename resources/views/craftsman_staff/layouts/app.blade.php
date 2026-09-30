@php
    $sidebarUser = auth()->guard('craftsman_staff')->user();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Craftsman Dashboard')</title>

    <!-- Tailwind CSS (Play CDN) & Bootstrap 5 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <script>
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
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        panel: {
                            sidebar: '#0f172a',
                            sidebarHover: '#1e293b',
                            darkBg: '#0b1120',
                            darkCard: '#1e293b',
                            darkBorder: '#334155'
                        }
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        /* Dark mode overrides for child components & bootstrap elements */
        html.dark body { background-color: #0b1120; color: #f1f5f9; }
        html.dark #content { background-color: #0b1120 !important; }
        html.dark header { background-color: #0f172a !important; border-color: #1e293b !important; }
        html.dark .bg-white, html.dark .card, html.dark main { background-color: #1e293b !important; color: #f1f5f9 !important; }
        html.dark .text-slate-900, html.dark .text-gray-900, html.dark .text-indigo-900 { color: #f8fafc !important; }
        html.dark .text-slate-700, html.dark .text-gray-700, html.dark .text-indigo-700 { color: #cbd5e1 !important; }
        html.dark .bg-slate-50, html.dark .bg-gray-50, html.dark .bg-indigo-50 { background-color: #1e293b !important; }
        html.dark .bg-slate-100, html.dark .bg-gray-100, html.dark .bg-indigo-100 { background-color: #334155 !important; color: #f8fafc !important; }
        html.dark .border-slate-200, html.dark .border-gray-200, html.dark .border-indigo-200 { border-color: #334155 !important; }
        html.dark .table, html.dark .table th, html.dark .table td { border-color: #334155 !important; color: #f1f5f9 !important; }
        html.dark .modal-content { background-color: #1e293b !important; color: #f1f5f9 !important; }
        html.dark .form-control, html.dark .form-select { background-color: #0f172a !important; border-color: #334155 !important; color: #f1f5f9 !important; }
        html.dark .form-control::placeholder { color: #94a3b8 !important; }
        html.dark .dropdown-menu { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .dropdown-item { color: #f1f5f9 !important; }
        html.dark .dropdown-item:hover { background-color: #334155 !important; }
        html.dark .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>

    <style>
        /* Custom clean scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 9999px;
        }

        /* Sidebar transitions and active link styles */
        #sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
        }

        .nav-link {
            transition: all 0.2s ease-in-out;
        }

        .nav-link.active {
            background: rgba(16, 185, 129, 0.15) !important;
            border-left: 4px solid #10b981 !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        /* Responsive Mobile Layout */
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
            }
            #sidebar.open {
                transform: translateX(0);
            }
            #sidebar.open ~ #sidebar-overlay {
                display: block;
            }
            #content {
                margin-left: 0 !important;
            }
        }
    </style>
    @yield('styles')
</head>

<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased">

    <div class="flex min-h-screen relative">

        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-40"></div>

        <!-- Sidebar -->
        <nav id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-slate-900 border-r border-slate-800 text-slate-300 flex flex-col lg:translate-x-0">
            <div class="p-4 border-b border-slate-800 flex items-center justify-center">
                <div class="p-2 bg-slate-800/60 rounded-xl">
                    <img src="{{ asset('images/taralogo.png') }}" class="h-14 w-auto object-contain" alt="Logo">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <ul class="space-y-1">
                    @if($sidebarUser && $sidebarUser->hasPermission('dashboard'))
                    <li>
                        <a href="{{ route('craftsman_staff.dashboard') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 text-lg mr-3"></i> Dashboard
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('craftsman_staff.global-search') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.global-search') ? 'active' : '' }}">
                            <i class="bi bi-search text-lg mr-3"></i> Global Search
                        </a>
                    </li>

                    <li>
                        <a href="#" class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white">
                            <i class="bi bi-chat-dots text-lg mr-3"></i> Messages
                        </a>
                    </li>

                    <li>
                        <a href="#" class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white">
                            <i class="bi bi-camera-video text-lg mr-3"></i> Meetings
                        </a>
                    </li>

                    <div class="my-2 border-t border-slate-800"></div>

                    @if($sidebarUser && ($sidebarUser->hasPermission('wo_view') || $sidebarUser->hasPermission('wo_accept') || $sidebarUser->hasPermission('wo_reject')))
                    <li>
                        <a href="{{ route('craftsman_staff.work-order.index') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.work-order.*') ? 'active' : '' }}">
                            <i class="bi bi-clipboard text-lg mr-3"></i> Work Orders
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && $sidebarUser->hasPermission('stock_order'))
                    <li>
                        <a href="#" class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white">
                            <i class="bi bi-box2-heart text-lg mr-3"></i> Live Stock Order
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && ($sidebarUser->hasPermission('po_view') || $sidebarUser->hasPermission('po_accept') || $sidebarUser->hasPermission('po_reject')))
                    <li>
                        <a href="{{ route('craftsman_staff.purchase-order.index') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.purchase-order.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text text-lg mr-3"></i> Purchase Orders
                        </a>
                    </li>
                    @endif

                    @if(!$sidebarUser || ($sidebarUser->hasPermission('repair_view') || $sidebarUser->hasPermission('repair_accept') || $sidebarUser->hasPermission('repair_reject')))
                    <li>
                        <a href="{{ route('craftsman_staff.repairs.index') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.repairs.*') ? 'active' : '' }}">
                            <i class="bi bi-tools text-lg mr-3"></i> Samples/Repairs
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && ($sidebarUser->hasPermission('product_view') || $sidebarUser->hasPermission('product_create') || $sidebarUser->hasPermission('product_edit')))
                    <li>
                        <a href="{{ route('craftsman_staff.product.index') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.product.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam text-lg mr-3"></i> Products
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && $sidebarUser->hasPermission('design_view'))
                    <li>
                        <a href="{{ route('craftsman_staff.design.index') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.design.*') ? 'active' : '' }}">
                            <i class="bi bi-brush text-lg mr-3"></i> Designs
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && $sidebarUser->hasPermission('favorites'))
                    <li>
                        <a href="#" class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white">
                            <i class="bi bi-heart text-lg mr-3"></i> Favorites
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && $sidebarUser->hasPermission('catalogue_view'))
                    <li>
                        <a href="{{ route('craftsman_staff.catalogue.index') }}"
                           class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('craftsman_staff.catalogue.*') ? 'active' : '' }}">
                            <i class="bi bi-book text-lg mr-3"></i> Catalogue
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && $sidebarUser->hasPermission('craftsman_staff'))
                    <li>
                        <a href="#" class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white">
                            <i class="bi bi-people text-lg mr-3"></i> Craftsman Staff
                        </a>
                    </li>
                    @endif

                    @if($sidebarUser && $sidebarUser->hasPermission('finance'))
                    <li>
                        <a href="#" class="nav-link flex items-center p-2.5 rounded-lg hover:bg-slate-800 hover:text-white">
                            <i class="bi bi-currency-dollar text-lg mr-3"></i> Finance
                        </a>
                    </li>
                    @endif

                    <div class="my-2 border-t border-slate-800"></div>

                    <li>
                        <a href="{{ route('craftsman_staff.logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="flex items-center p-2.5 rounded-lg text-rose-400 hover:bg-rose-950/30 hover:text-rose-300 transition">
                            <i class="bi bi-box-arrow-right text-lg mr-3"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('craftsman_staff.logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div id="content" class="flex-1 flex flex-col lg:ml-64 min-w-0 transition-all duration-300">
            <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 mr-3">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 hidden md:block m-0">
                        Dashboard
                    </h2>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Desktop Dark Mode Toggle -->
                    <button id="darkModeToggle" class="hidden md:flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Toggle Theme">
                        <i id="darkIcon" class="bi bi-moon-stars text-sm"></i>
                        <i id="lightIcon" class="bi bi-sun text-sm hidden"></i>
                    </button>

                    <!-- Quick Messages -->
                    <a href="#" class="hidden md:flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Messages">
                        <i class="bi bi-chat-dots text-sm"></i>
                    </a>

                    <!-- Mobile Dropdown -->
                    <div class="dropdown md:hidden">
                        <button class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" type="button" id="moreMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-xl" aria-labelledby="moreMenuButton">
                            <li>
                                <a class="dropdown-item flex items-center gap-2" href="#">
                                    <i class="bi bi-chat-dots text-emerald-600"></i> Messages
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item flex items-center gap-2" href="#">
                                    <i class="bi bi-camera-video text-emerald-600"></i> Meetings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item flex items-center gap-2" id="mobileDarkModeToggle">
                                    <i class="bi bi-moon-stars text-emerald-600" id="mobileDarkIcon"></i>
                                    <span id="mobileDarkText">Dark Mode</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300 hidden sm:block">
                        Welcome, <span class="font-semibold text-slate-900 dark:text-white">{{ $sidebarUser->full_name ?? $sidebarUser->name ?? 'User' }}</span>
                    </span>
                </div>
            </header>

            <main class="p-4 lg:p-6 flex-1">
                <div class="w-full max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-2xl border-none shadow-2xl">
                <div class="modal-header border-slate-200 dark:border-slate-800">
                    <h5 class="modal-title font-bold text-slate-900 dark:text-slate-100">Design Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0 overflow-auto max-h-[80vh]" id="modalPreviewContainer"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Theme Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggleBtn = document.getElementById('darkModeToggle');
            const mobileThemeToggleBtn = document.getElementById('mobileDarkModeToggle');
            const darkIcon = document.getElementById('darkIcon');
            const lightIcon = document.getElementById('lightIcon');
            const mobileDarkIcon = document.getElementById('mobileDarkIcon');
            const mobileDarkText = document.getElementById('mobileDarkText');

            function updateIcons(isDark) {
                if (isDark) {
                    if (darkIcon) darkIcon.classList.add('hidden');
                    if (lightIcon) lightIcon.classList.remove('hidden');
                    if (mobileDarkIcon) {
                        mobileDarkIcon.classList.remove('bi-moon-stars');
                        mobileDarkIcon.classList.add('bi-sun');
                    }
                    if (mobileDarkText) mobileDarkText.innerText = 'Light Mode';
                } else {
                    if (darkIcon) darkIcon.classList.remove('hidden');
                    if (lightIcon) lightIcon.classList.add('hidden');
                    if (mobileDarkIcon) {
                        mobileDarkIcon.classList.remove('bi-sun');
                        mobileDarkIcon.classList.add('bi-moon-stars');
                    }
                    if (mobileDarkText) mobileDarkText.innerText = 'Dark Mode';
                }
            }

            updateIcons(document.documentElement.classList.contains('dark'));

            function toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    updateIcons(false);
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    updateIcons(true);
                }
            }

            if (themeToggleBtn) themeToggleBtn.addEventListener('click', toggleTheme);
            if (mobileThemeToggleBtn) {
                mobileThemeToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleTheme();
                });
            }
        });

        // PDF Sensitive Field Redaction Logic
        async function redactSensitiveText(page, context, viewport) {
            const textContent = await page.getTextContent();
            const items = textContent.items;
            const fieldsToHide = ["order id", "design", "qty", "weight", "size", "product", "tolerance from", "tolerance to"];

            for (let i = 0; i < items.length; i++) {
                let current = items[i].str.toLowerCase();
                let next = items[i + 1] ? items[i + 1].str.toLowerCase() : "";
                let combined = current + " " + next;

                fieldsToHide.forEach(field => {
                    if (current.includes(field) || combined.includes(field)) {
                        const labelTx = pdfjsLib.Util.transform(viewport.transform, items[i].transform);
                        const labelY = labelTx[5];

                        for (let k = 0; k < items.length; k++) {
                            const valueTx = pdfjsLib.Util.transform(viewport.transform, items[k].transform);
                            const valueY = valueTx[5];

                            if (Math.abs(labelY - valueY) < 3) {
                                const x = valueTx[4];
                                const y = valueTx[5];
                                const width = items[k].width * viewport.scale;
                                const height = items[k].height * viewport.scale;

                                context.fillStyle = "#FFFFFF";
                                context.fillRect(x - 2, y - height, width + 20, height + 4);
                            }
                        }
                    }
                });
            }
        }

        // Modal Preview
        window.openUniversalPreview = function(url, type) {
            const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
            modal.show();

            const container = document.getElementById('modalPreviewContainer');
            container.innerHTML = '';

            if (type === 'pdf') {
                pdfjsLib.getDocument(url).promise.then(async pdf => {
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const canvas = document.createElement('canvas');
                        canvas.className = 'img-fluid mb-2 border-bottom';
                        container.appendChild(canvas);

                        const page = await pdf.getPage(pageNum);
                        const viewport = page.getViewport({ scale: 2.0 });

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        const context = canvas.getContext('2d');
                        await page.render({ canvasContext: context, viewport: viewport }).promise;
                        await redactSensitiveText(page, context, viewport);
                    }
                }).catch(err => console.error("PDF Render Error:", err));
            } else {
                const img = document.createElement('img');
                img.src = url;
                img.className = 'img-fluid rounded shadow';
                container.appendChild(img);
            }
        };

        // Thumbnail Generator
        window.renderPdfThumbnails = function() {
            const canvases = document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');
            canvases.forEach(canvas => {
                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 100;
                canvas.dataset.rendered = 'true';

                pdfjsLib.getDocument(url).promise.then(async pdf => {
                    const page = await pdf.getPage(1);
                    const viewport_raw = page.getViewport({ scale: 1.0 });
                    const scale = desiredWidth / viewport_raw.width;
                    const viewport = page.getViewport({ scale: scale });

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const context = canvas.getContext('2d');
                    await page.render({ canvasContext: context, viewport: viewport }).promise;
                    await redactSensitiveText(page, context, viewport);
                });
            });
        };

        document.addEventListener("DOMContentLoaded", function() {
            renderPdfThumbnails();
        });
    </script>
    @yield('scripts')
</body>

</html>