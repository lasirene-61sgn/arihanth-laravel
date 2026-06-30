<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'User Panel')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        /* Smooth transitions for the sidebar */
        #sidebar { transition: transform 0.3s ease-in-out; z-index: 1050; }
        #sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1040; }
        
        @media (max-width: 1023px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #sidebar.open ~ #sidebar-overlay { display: block; }
            #main-layout { margin-left: 0 !important; }
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #818cf8;
            color: white !important;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900">

    <div class="flex min-h-screen relative">
        
        <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

        <nav id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-slate-900 text-slate-300 flex flex-col lg:translate-x-0 shadow-2xl">
            <div class="p-6 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div >
                        <img src="{{ asset('images/taralogo.png') }}" class="h-30 w-30" alt="AJ Logo">
                    </div>
                    <div>
                        <!-- <h1 class="font-bold text-lg tracking-tight text-white leading-tight">ARIHANTH JEWELLERS</h1>
                        <p class="text-xs leading-tight text-white/60 font-medium uppercase tracking-wider">USER</p> -->
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <li>
                        <a href="{{ route('user.dashboard') }}" 
                           class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('user.work-order.index') }}" 
                           class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('user.work-order.*') ? 'active' : '' }}">
                            <span class="flex items-center">
                                <i class="bi bi-clipboard mr-3 text-lg"></i>
                                <span class="font-medium">Work Orders</span>
                            </span>
                            <!-- <span class="bg-slate-700 text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-600">{{ $sidebarCounts['workOrdersCount'] }}</span> -->
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('user.product.index') }}" 
                           class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('user.product.*') ? 'active' : '' }}">
                            <span class="flex items-center">
                                <i class="bi bi-box-seam mr-3 text-lg"></i>
                                <span class="font-medium">Products</span>
                            </span>
                            <!-- <span class="bg-slate-700 text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-600">{{ $sidebarCounts['productsCount'] }}</span> -->
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('user.design.index') }}" 
                           class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('user.design.*') ? 'active' : '' }}">
                            <span class="flex items-center">
                                <i class="bi bi-palette mr-3 text-lg"></i>
                                <span class="font-medium">Design</span>
                            </span>
                            <!-- <span class="bg-slate-700 text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-600">{{ $sidebarCounts['designsCount'] }}</span> -->
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('user.catalogue.index') }}" 
                           class="nav-link flex items-center justify-between p-3 rounded-lg hover:bg-slate-800 hover:text-white transition {{ request()->routeIs('user.catalogue.*') ? 'active' : '' }}">
                            <span class="flex items-center">
                                <i class="bi bi-book mr-3 text-lg"></i>
                                <span class="font-medium">Catalogue</span>
                            </span>
                            <!-- <span class="bg-slate-700 text-white text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-600">{{ $sidebarCounts['cataloguesCount'] }}</span> -->
                        </a>
                    </li>

                    <li class="mt-4 pt-4 border-t border-slate-800">
                        <span class="px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mb-2 block">Support</span>
                        <a href="#" 
                           data-bs-toggle="modal" data-bs-target="#contactSupportModal"
                           class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-800 hover:text-white transition">
                            <i class="bi bi-headset mr-3 text-lg"></i>
                            <span class="font-medium">Contact Us</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('user.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center p-2 text-sm font-semibold text-red-400 hover:bg-red-950/30 rounded-lg transition group">
                        <i class="bi bi-box-arrow-right mr-2 transition-transform group-hover:translate-x-1"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <div id="main-layout" class="flex-1 flex flex-col lg:ml-64 transition-all duration-300">
            
            <header class="h-16 bg-white border-b border-slate-200 flex items-center px-4 lg:px-8 sticky top-0 z-40 shadow-sm">
                <div class="flex justify-between align-items-center w-100">
                    <div class="flex items-center">
                        <button class="lg:hidden p-2 rounded-md bg-slate-100 text-slate-600 mr-4" id="sidebarToggle" onclick="toggleSidebar()">
                            <i class="bi bi-list text-xl"></i>
                        </button>
                        <h2 class="text-sm lg:text-lg font-bold text-slate-800 hidden sm:block uppercase tracking-wider">
                            @yield('title', 'Control Center')
                        </h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-bold text-slate-900 leading-none">
                                {{ Auth::guard('web')->user()->full_name ?? Auth::guard('web')->user()->name ?? 'User' }}
                            </div>
                            <small class="text-[10px] text-slate-400 font-medium">
                                {{ Auth::guard('web')->user()->user_code ?? Auth::guard('web')->user()->email }}
                            </small>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-indigo-600 border-2 border-indigo-100 flex items-center justify-center text-white text-xs font-black shadow-inner">
                            {{ strtoupper(substr(Auth::guard('web')->user()->full_name ?? Auth::guard('web')->user()->name ?? 'U', 0, 2)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 bg-white">
                <div class="p-4 lg:p-8 max-w-[1600px] mx-auto min-h-screen">
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
                    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
                    70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
                    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
                }
                .pulse-green {
                    animation: pulse-green 2s infinite;
                }
            </style>

            <footer class="bg-white border-t border-slate-100 py-4 px-8">
                <p class="text-xs text-slate-400 text-center mb-0 font-medium">
                    &copy; {{ date('Y') }} <span class="text-indigo-600">User Panel</span>. All rights reserved.
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

    <div class="modal fade" id="universalPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-2xl overflow-hidden border-0 shadow-2xl">
                <div class="modal-header bg-slate-900 border-0 py-3 px-6">
                    <h5 class="modal-title text-white text-xs font-black tracking-widest uppercase mb-0">Design Preview</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0 bg-slate-50 overflow-auto max-h-[85vh]" id="modalPreviewContainer">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        // PDF.js Worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        // Global function to render PDF thumbnails
        window.renderPdfThumbnails = function() {
            const canvases = document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');
            if(canvases.length === 0) return;

            canvases.forEach(canvas => {
                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 100;
                renderPdfToCanvas(canvas, url, desiredWidth).then(numPages => {
                    if (numPages > 1) {
                        const container = canvas.parentElement;
                        if (container && !container.querySelector('.pdf-page-count-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'pdf-page-count-badge absolute bottom-0 right-0 bg-dark bg-opacity-75 text-white font-bold leading-none px-1.5 py-1 rounded-top-start';
                            badge.style.fontSize = '9px';
                            badge.style.position = 'absolute';
                            badge.style.bottom = '0';
                            badge.style.right = '0';
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
                    const viewport_raw = page.getViewport({ scale: 1.0 });
                    const scale = desiredWidth / viewport_raw.width;
                    const viewport = page.getViewport({ scale: scale });

                    const context = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    
                    return page.render(renderContext).promise.then(() => {
                        // REDACTION: Applied to thumbnail
                        context.style = "#FFFFFF";
                        context.fillRect(0, 0, canvas.width * 0.40, canvas.height * 0.50);
                        return numPages;
                    });
                });
            }).catch(error => {
                console.error('Error rendering PDF:', error);
                return 0;
            });
        }

        // Function to open Preview in Modal
        window.openUniversalPreview = function(url, type) {
            const modalEl = document.getElementById('universalPreviewModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            const container = document.getElementById('modalPreviewContainer');
            container.innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';

            if (type === 'pdf') {
                pdfjsLib.getDocument(url).promise.then(async pdf => {
                    container.innerHTML = '';
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const canvas = document.createElement('canvas');
                        canvas.className = 'img-fluid mx-auto d-block my-4 shadow bg-white rounded';
                        container.appendChild(canvas);
                        
                        const page = await pdf.getPage(pageNum);
                        const viewport_raw = page.getViewport({ scale: 1.0 });
                        const scale = 2.0; 
                        const viewport = page.getViewport({ scale: scale });

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
                    console.error("PDF Preview Error:", err);
                    let errorMsg = 'Error rendering PDF';
                    if (err.name === 'MissingPDFException' || err.name === 'MissingPDF') {
                        errorMsg = 'PDF File Not Found (404)';
                    } else if (err.message) {
                        errorMsg = 'Error: ' + err.message;
                    }
                    container.innerHTML = `<div class="p-5 text-danger fw-bold">${errorMsg}</div><div class="text-xs text-muted mb-4 break-all px-4">${url}</div>`;
                });
            } else {
                const img = new Image();
                img.crossOrigin = "Anonymous";
                img.src = url;
                img.onload = function() {
                    container.innerHTML = '';
                    const canvas = document.createElement('canvas');
                    canvas.className = 'img-fluid mx-auto d-block shadow-lg';
                    container.appendChild(canvas);
                    const context = canvas.getContext('2d');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    context.drawImage(img, 0, 0);
                };
                img.onerror = function() {
                    container.innerHTML = '<div class="p-5 text-danger fw-bold">Error loading image</div>';
                };
            }
        };

        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });

        document.addEventListener('DOMContentLoaded', function() {
            renderPdfThumbnails();
        });
    </script>
    
    @yield('scripts')
</body>
</html>
