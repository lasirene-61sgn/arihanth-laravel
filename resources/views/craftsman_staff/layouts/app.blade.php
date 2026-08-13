@php
    $sidebarUser = auth()->guard('craftsman_staff')->user();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Craftsman Dashboard')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        tailwind.config = { darkMode: 'class' }
    </script>
    <style type="text/tailwindcss">
        html.dark body { background-color: #111827; color: #f3f4f6; }
        html.dark #content { background-color: #111827 !important; }
        html.dark header { background-color: #1f2937 !important; border-color: #374151 !important; }
        html.dark .bg-white, html.dark .card, html.dark main { background-color: #1f2937 !important; color: #f3f4f6 !important; }
        html.dark .text-indigo-900, html.dark .text-indigo-800, html.dark .text-indigo-700 { color: #f3f4f6 !important; }
        html.dark .bg-indigo-50 { background-color: #374151 !important; }
        html.dark .bg-indigo-100 { background-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark .border-indigo-200, html.dark .border-indigo-100 { border-color: #374151 !important; }
        html.dark .table, html.dark .table th, html.dark .table td { border-color: #374151 !important; color: #f3f4f6 !important; }
        html.dark .modal-content { background-color: #1f2937 !important; color: #f3f4f6 !important; }
        html.dark .form-control, html.dark .form-select { background-color: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
        html.dark .form-control::placeholder { color: #9ca3af !important; }
        html.dark .dropdown-menu { background-color: #1f2937 !important; border-color: #374151 !important; }
        html.dark .dropdown-item { color: #f3f4f6 !important; }
        html.dark .dropdown-item:hover { background-color: #374151 !important; }
        html.dark .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>

    <style>
        @keyframes custom-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.2; }
        }
        .animate-custom-blink {
            animation: custom-blink 1.2s ease-in-out infinite;
        }

        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #065f46;
            border-radius: 10px;
        }

        /* Smooth Sidebar Transitions */
        #sidebar {
            transition: transform 0.3s ease-in-out;
            z-index: 1050;
        }

        /* Overlay for mobile when sidebar is open */
        #sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }

            #sidebar.open~#sidebar-overlay {
                display: block;
            }

            #content {
                margin-left: 0 !important;
            }
        }

        /* Active Link Styles */
        .nav-link.active {
            background: rgba(52, 211, 153, 0.15);
            border-left: 4px solid #34d399;
            color: #00000 !important;
        }
    </style>
    @yield('styles')
</head>

<body class="bg-indigo-50 text-indigo-950 font-sans">

    <div class="flex min-h-screen">

    <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <nav id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-indigo-900 text-white flex flex-col lg:translate-x-0 transition-transform duration-300 z-50">
        <div class="p-6 border-b border-indigo-800">
            <div class="flex items-center gap-3">
                <div class="bg-white/10 p-1.5 rounded-lg">
                    <img src="{{ asset('images/taralogo.png') }}" class="h-20 w-20" alt="AJ Logo">
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                @if($sidebarUser && $sidebarUser->hasPermission('dashboard'))
                <li>
                    <a href="{{ route('craftsman_staff.dashboard') }}"
                        class="nav-link flex items-center p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.dashboard') ? 'active bg-indigo-800' : '' }}">
                        <i class="bi bi-speedometer2 mr-3"></i> Dashboard
                    </a>
                </li>
                @endif

                <!-- NEW: Messages (Moved higher for visibility) -->
                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.chat.*') ? 'active bg-indigo-800' : '' }}">
                        <i class="bi bi-chat-dots mr-3"></i> Messages
                    </a>
                </li>

                <!-- NEW: Meetings -->
                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.meetings.*') ? 'active bg-indigo-800' : '' }}">
                        <i class="bi bi-camera-video mr-3"></i> Meetings
                    </a>
                </li>

                <div class="my-2 border-t border-indigo-800/50 mx-3"></div>

                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.profile.edit') ? 'active bg-indigo-800' : '' }}">
                        <i class="bi bi-person-circle mr-3"></i> My Profile
                    </a>
                </li>

                @if($sidebarUser && ($sidebarUser->hasPermission('wo_view') || $sidebarUser->hasPermission('wo_accept') || $sidebarUser->hasPermission('wo_reject')))
                <li>
                    <a href="{{ route('craftsman_staff.work-order.index') }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.work-order.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-clipboard mr-3"></i> Work Orders</span>
                    </a>
                </li>
                @endif

                @if($sidebarUser && $sidebarUser->hasPermission('stock_order'))
                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.stock-order.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-box2-heart mr-3"></i> Live Stock Order</span>
                    </a>
                </li>
                @endif

                @if($sidebarUser && ($sidebarUser->hasPermission('po_view') || $sidebarUser->hasPermission('po_accept') || $sidebarUser->hasPermission('po_reject')))
                <li>
                    <a href="{{ route('craftsman_staff.purchase-order.index') }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.purchase-order.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-file-earmark-text mr-3"></i> Purchase Orders</span>
                    </a>
                </li>
                @endif

                @if(!$sidebarUser || ($sidebarUser->hasPermission('repair_view') || $sidebarUser->hasPermission('repair_accept') || $sidebarUser->hasPermission('repair_reject')))
                <li>
                    <a href="{{ route('craftsman_staff.repairs.index') }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.repairs.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-tools mr-3"></i> Samples/Repairs</span>
                    </a>
                </li>
                @endif

                @if($sidebarUser && ($sidebarUser->hasPermission('product_view') || $sidebarUser->hasPermission('product_create') || $sidebarUser->hasPermission('product_edit')))
                <li>
                    <a href="{{ route('craftsman_staff.product.index') }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.product.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-box-seam mr-3"></i> Products</span>
                    </a>
                </li>
                @endif

                @if($sidebarUser && $sidebarUser->hasPermission('design_view'))
                <li>
                    <a href="{{ route('craftsman_staff.design.index') }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.design.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-brush mr-3"></i> Designs</span>
                    </a>
                </li>
                @endif
                @if($sidebarUser && $sidebarUser->hasPermission('favorites'))
                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.favorites.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-heart mr-3"></i> Favorites</span>
                    </a>
                </li>
                
                @endif

                @if($sidebarUser && $sidebarUser->hasPermission('catalogue_view'))
                <li>
                    <a href="{{ route('craftsman_staff.catalogue.index') }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.catalogue.index') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-book mr-3"></i> Catalogue</span>
                    </a>
                </li>
                @endif
                
                @if($sidebarUser && $sidebarUser->hasPermission('craftsman_staff'))
                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.staff.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-people mr-3"></i> Craftsman Staff</span>
                    </a>
                </li>
                @endif
                
                @if($sidebarUser && $sidebarUser->hasPermission('finance'))
                <li>
                    <a href="{{ '#' }}"
                        class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800 transition {{ request()->routeIs('craftsman.finance.*') ? 'active bg-indigo-800' : '' }}">
                        <span><i class="bi bi-currency-dollar mr-3"></i> Finance</span>
                    </a>
                </li>
                @endif

                <div class="my-4 border-t border-indigo-800 mx-3"></div>

                <li>
                    <a href="{{ route('craftsman_staff.logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="flex items-center p-3 rounded-lg text-indigo-300 hover:text-white hover:bg-red-900/30 transition">
                        <i class="bi bi-box-arrow-right mr-3"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('craftsman_staff.logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div id="content" class="flex-1 flex flex-col lg:ml-64 transition-all duration-300">
        <header class="h-16 bg-white border-b border-indigo-200 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-40">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md bg-indigo-100 text-indigo-800 mr-4">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                <h2 class="text-lg font-semibold text-indigo-900 hidden md:block">
                    Dashboard
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <!-- Dark Mode Toggle -->
                <button id="darkModeToggle" class="hidden md:flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors" title="Toggle Dark Mode">
                    <i id="darkIcon" class="bi bi-moon-stars"></i>
                    <i id="lightIcon" class="bi bi-sun hidden"></i>
                </button>

                <!-- Desktop Icons -->
                <a href="{{ '#' }}" class="hidden md:flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors" title="Messages">
                    <i class="bi bi-chat-dots"></i>
                </a>
                <!-- Removed Meetings Dropdown as Craftsman Staff do not have meetings -->

                <!-- Mobile 'More' Dropdown -->
                <div class="dropdown md:hidden">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors" type="button" id="moreMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-xl" aria-labelledby="moreMenuButton">
                        <li>
                            <a class="dropdown-item flex items-center gap-2" href="{{ '#' }}">
                                <i class="bi bi-chat-dots text-indigo-600"></i> Messages
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item flex items-center gap-2" href="{{ '#' }}">
                                <i class="bi bi-camera-video text-indigo-600"></i> Meetings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item flex items-center gap-2" id="mobileDarkModeToggle">
                                <i class="bi bi-moon-stars text-indigo-600" id="mobileDarkIcon"></i>
                                <span id="mobileDarkText">Dark Mode</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <span class="text-sm font-medium text-indigo-700 hidden sm:block">
                    Welcome, <span class="font-bold text-indigo-900">{{ $sidebarUser->full_name ?? $sidebarUser->name ?? 'User' }}</span>
                </span>
            </div>
        </header>

        <main class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>


    </div>
</div>

<style>
    @keyframes pulse-green {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
    }
    .pulse-green { animation: pulse-green 2s infinite; }
    /* Ensure active links are highlighted correctly */
    .nav-link.active { background-color: #064e3b !important; color: white !important; }
</style>

    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-xl border-none shadow-2xl">
                <div class="modal-header border-indigo-100">
                    <h5 class="modal-title font-bold text-indigo-900">Design Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0 overflow-auto max-h-[80vh]" id="modalPreviewContainer">
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PDF.js from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';


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
                "tolerance to"
            ];

            for (let i = 0; i < items.length; i++) {
                let current = items[i].str.toLowerCase();
                let next = items[i + 1] ? items[i + 1].str.toLowerCase() : "";
                let combined = current + " " + next;

                fieldsToHide.forEach(field => {

                    if (current.includes(field) || combined.includes(field)) {
                        const labelTx = pdfjsLib.Util.transform(
                            viewport.transform,
                            items[i].transform
                        );

                        const labelY = labelTx[5];

                        for (let k = 0; k < items.length; k++) {
                            const valueTx = pdfjsLib.Util.transform(
                                viewport.transform,
                                items[k].transform
                            );

                            const valueY = valueTx[5];

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
        }


        // ================= PREVIEW ==================

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

                        const viewport = page.getViewport({
                            scale: 2.0
                        });

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        const context = canvas.getContext('2d');

                        await page.render({
                            canvasContext: context,
                            viewport: viewport
                        }).promise;

                        // 🔥 ERP VALUE HIDING
                        await redactSensitiveText(page, context, viewport);
                    }

                }).catch(err => {
                    console.error("PDF Render Error:", err);
                });
            } else {
                const img = document.createElement('img');
                img.src = url;
                img.className = 'img-fluid rounded shadow';
                container.appendChild(img);
            }
        }



        // ================= THUMBNAIL ==================

        window.renderPdfThumbnails = function() {
            const canvases = document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');

            canvases.forEach(canvas => {

                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 100;

                canvas.dataset.rendered = 'true';

                pdfjsLib.getDocument(url).promise.then(async pdf => {

                    const page = await pdf.getPage(1);

                    const viewport_raw = page.getViewport({
                        scale: 1.0
                    });
                    const scale = desiredWidth / viewport_raw.width;
                    const viewport = page.getViewport({
                        scale: scale
                    });

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const context = canvas.getContext('2d');

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;

                    // 🔥 ERP VALUE HIDING
                    await redactSensitiveText(page, context, viewport);

                });

            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            renderPdfThumbnails();
        });
    </script>

    <!-- Script to handle tab switching -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    content.classList.toggle('active');
                });
            }

            // Close sidebar when clicking on a link (mobile)
            const sidebarLinks = document.querySelectorAll('#sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('active');
                        content.classList.remove('active');
                    }
                });
            });

            // Function to switch to a specific tab
            function switchTab(tabName) {
                var tabTrigger = document.querySelector('#' + tabName + '-tab');
                if (tabTrigger) {
                    var tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }

            // Check URL parameters for tab switching
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                switchTab(tab);
            }
        });
    </script>

    <!-- Firebase Notifications -->
    <!--  -->

    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dark mode toggle logic
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

            // Init icons on load
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

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
            if (mobileThemeToggleBtn) {
                mobileThemeToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleTheme();
                });
            }
        });
    </script>
</body>

</html>
