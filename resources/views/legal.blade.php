<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy & Terms | ARIHANTH JEWELLERS PYT LTD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .brand-font {
            font-family: 'Playfair Display', serif;
        }

        .legal-header {
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.9)),
                url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&q=80&w=1200');
            background-size: cover;
            background-position: center;
        }

        .toc-link.active {
            color: #f59e0b;
            border-left-color: #f59e0b;
            background-color: #fffbeb;
        }

        @media (min-width: 1024px) {
            .sticky-toc {
                position: sticky;
                top: 2rem;
                max-height: calc(100vh - 4rem);
                overflow-y: auto;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased line-height-relaxed">

    <!-- Premium Header -->
    <header class="legal-header py-24 px-6 text-center">
        <div class="max-w-4xl mx-auto">
            <div class="inline-block p-4 bg-white/10 backdrop-blur-md rounded-2xl mb-8 border border-white/20">
                <img src="{{ asset('images/ajlogo.png') }}" alt="AJ Logo" class="h-20 mx-auto drop-shadow-2xl">
            </div>
            <h1 class="brand-font text-5xl md:text-7xl mb-6 text-amber-400 tracking-tight">ARIHANTH JEWELLERS</h1>
            <p class="text-xl md:text-2xl font-light tracking-widest text-slate-300 uppercase">Privacy Architecture & Enterprise Terms</p>
            <div class="mt-8 flex flex-wrap justify-center gap-4 text-xs font-medium uppercase tracking-widest text-slate-400">
                <span class="px-3 py-1 bg-slate-800/50 rounded-full border border-slate-700">Version 1.2.0</span>
                <span class="px-3 py-1 bg-slate-800/50 rounded-full border border-slate-700">Updated: May 2026</span>
                <span class="px-3 py-1 bg-slate-800/50 rounded-full border border-slate-700">Google Play & Apple App Store Compliant</span>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex flex-col lg:flex-row gap-12">

            <!-- Sticky Sidebar Navigation -->
            <aside class="lg:w-1/4">
                <div class="sticky-toc bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest mb-6 pb-2 border-b">Table of Contents</h2>
                    <nav class="space-y-1">
                        <a href="#introduction" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">1.0 Introduction</a>
                        <a href="#store-compliance" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">2.0 Mobile Store Governance</a>
                        <a href="#camera" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">3.0 Camera & Media Assets</a>
                        <a href="#meetings" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">4.0 Scheduled Consultations</a>
                        <a href="#tracking" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">5.0 Production Tracking</a>
                        <a href="#security" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">6.0 Security & Encryption</a>
                        <a href="#retention" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">7.0 Data Retention Policy</a>
                        <a href="#rights" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">8.0 Your Data Rights</a>
                        <a href="#roles" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">9.0 Role-Based Governance</a>
                        <a href="#intellectual" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">10.0 Intellectual Property</a>
                        <a href="#contact" class="toc-link block px-4 py-2 text-sm font-medium border-l-2 border-transparent hover:text-amber-600 hover:bg-slate-50 transition-all">11.0 Grievance Redressal</a>
                    </nav>
                </div>
            </aside>

            <!-- Main Legal Content -->
            <main class="lg:w-3/4 space-y-16">

                <!-- Section: Introduction -->
                <section id="introduction" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">01</span> Introduction
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed text-lg">
                        <p>
                            <strong>ARIHANTH JEWELLERS PVT LTD</strong> (referred to as "Company", "We", "Us", or "Our") operates the proprietary Jewelry Management ERP and associated Mobile Application. This document outlines our stringent commitment to data integrity, privacy by design, and the operational terms governing the jewelry manufacturing lifecycle.
                        </p>
                        <p class="mt-4">
                            By accessing the platform as an administrator, craftsman, or buyer, you acknowledge the collection of operational data essential for the secure procurement, production, and scheduling of design frameworks for high-value jewelry assets.
                        </p>
                    </div>
                </section>

                <!-- Section: Mobile Store Compliance -->
                <section id="store-compliance" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">02</span> Mobile Store Governance (App Store & Play Store)
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                        <p>
                            In strict conformity with the <strong>Google Play Store Developer Distribution Agreement</strong> and the <strong>Apple App Store Review Guidelines</strong>, our application operates under an explicit-consent workflow:
                        </p>
                        <ul class="list-disc ml-5 space-y-3 mt-4">
                            <li><strong>Runtime Disclosures:</strong> No background data harvesting or hardware activations are conducted without distinct runtime confirmation prompts.</li>
                            <li><strong>Data Safety Transparency:</strong> All user-provided records, media files, and system interactions are processed over cryptographically fortified paths, explicitly avoiding any third-party marketing or profiling operations.</li>
                            <li><strong>Account Deletion Mandate:</strong> In compliance with mobile storefront requirements, users retain the right to trigger self-initiated account deletion directly within the mobile terminal settings.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section: Camera & Media -->
                <section id="camera" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">03</span> Camera & Media Assets
                    </h2>
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                        <p class="mb-6 font-medium text-slate-900">As mandated by App Store Guidelines and Google Play Store policies for high-risk access scopes:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h3 class="font-bold text-amber-600 uppercase text-xs tracking-widest">Purpose of Access</h3>
                                <p class="text-sm border-l-2 border-amber-200 pl-4">The application utilizes the device camera and photo storage parameters exclusively for <strong>Visual Quality Control (VQC)</strong>. This encompasses capturing design variations for active Work Orders and evaluating physical material states by our production teams.</p>
                            </div>
                            <div class="space-y-4">
                                <h3 class="font-bold text-amber-600 uppercase text-xs tracking-widest">Encryption & Privacy</h3>
                                <p class="text-sm border-l-2 border-amber-200 pl-4">All processed design media assets are encrypted using <strong>AES-256 protocols</strong> before transport to secure silos. The app strictly refrains from indexing, tracking, or altering unrelated directories on user devices.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section: Scheduled Meetings -->
                <section id="meetings" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">04</span> Scheduled Consultations & Meetings
                    </h2>
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 space-y-8">
                        <div class="prose prose-slate max-w-none text-slate-600">
                            <p>To provide personalized design consultations, custom fabrications, and validation workflows, users can organize interactive check-ins through our integrated <strong>Schedule Meeting</strong> architecture:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <h4 class="font-bold text-slate-900 text-sm mb-2">Scope of Information</h4>
                                    <p class="text-xs text-slate-500">Processing handles scheduling details, time markers, and design-related logs specifically curated for the review window.</p>
                                </div>
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <h4 class="font-bold text-slate-900 text-sm mb-2">Channel Protection</h4>
                                    <p class="text-xs text-slate-500">Virtual consultation feeds, stream mappings, and technical notes are restricted to assigned internal stakeholders and structural architects.</p>
                                </div>
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <h4 class="font-bold text-slate-900 text-sm mb-2">Calendar Synchronicity</h4>
                                    <p class="text-xs text-slate-500">Device calendar authorizations are used solely to confirm and alert users regarding assigned appointments, strictly omitting peripheral checks.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Agora SDK & Background Mode Sub-Section -->
                        <div class="mt-6 p-6 bg-amber-50/60 rounded-2xl border border-amber-100">
                            <h3 class="text-md font-bold text-slate-900 mb-3 flex items-center">
                                <span class="h-2 w-2 rounded-full bg-amber-500 mr-2"></span>
                                4.1 Real-Time Agora Video/Audio Communications & Background Execution
                            </h3>
                            <div class="prose prose-slate text-sm text-slate-600 space-y-3">
                                <p>
                                    Our platform incorporates the <strong>Agora SDK</strong> to enable interactive, low-latency video and audio virtual design consultations. To maintain critical workflow synchronization throughout high-value manufacturing sequences, the application utilizes continuous background processing mechanics:
                                </p>
                                <ul class="list-disc ml-5 space-y-2">
                                    <li>
                                        <strong>Super Admin Remote Activation:</strong> Whenever a designated <strong>Super Admin</strong> initiates or joins a scheduled design review meeting session, system signaling routines will broadcast a real-time call event payload directly to the assigned <strong>Buyer</strong> or <strong>Craftsman</strong>.
                                    </li>
                                    <li>
                                        <strong>Background Processing Mode & Wake Capabilities:</strong> To protect the transactional timeline of custom physical moldings, the application relies on operating system background mode triggers. This configuration enables the mobile system terminal to intercept incoming communications, sound custom connection rings, and present incoming video/audio bridge prompts—<strong>even when the application is minimized, sleeping, or running execution routines in the background</strong>.
                                    </li>
                                    <li>
                                        <strong>Hardware Access Scope:</strong> Camera and Microphone pipelines are engaged exclusively upon the recipient’s runtime confirmation to connect to the active Agora channel bridge, and are systematically dropped immediately upon session teardown.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section: Tracking -->
                <section id="tracking" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">05</span> Production Tracking & Comms
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600">
                        <p>To sustain transactional accuracy and minimize tracking delays within the fulfillment process, the framework incorporates <strong>Firebase Cloud Messaging (FCM)</strong> and validated <strong>Carrier SMS Engines (MSG91)</strong>:</p>
                        <ul class="list-disc ml-5 space-y-3 mt-4">
                            <li><strong>Real-time Allocation:</strong> Instant push triggers to advise our craftsmen regarding new design structures.</li>
                            <li><strong>Approval Workflow:</strong> Essential structural responses detailing design confirmation, modifications, or production updates.</li>
                            <li><strong>System Logs:</strong> Non-volatile audit timelines chronicling workflow modifications relative to active procurement sequences.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section: Security -->
                <section id="security" class="scroll-mt-8">
                    <div class="bg-slate-900 rounded-3xl p-10 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <svg class="h-32 w-32" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.12 8.94-7 10-3.88-1.06-7-5.33-7-10v-4.7l7-3.12z" />
                            </svg>
                        </div>
                        <h2 class="brand-font text-3xl text-amber-400 mb-6">06 Security & Encryption</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                            <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                                <h3 class="font-bold mb-2">TLS 1.3 Transmission</h3>
                                <p class="text-slate-400">All information passing between nodes is managed via high-grade SSL/TLS cryptographic baselines.</p>
                            </div>
                            <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                                <h3 class="font-bold mb-2">Argon2 Hashing</h3>
                                <p class="text-slate-400">System credentials are systematically salted and salted out using recognized modern processing standards.</p>
                            </div>
                            <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                                <h3 class="font-bold mb-2">Private Storage Buckets</h3>
                                <p class="text-slate-400">Design models and graphic logs are preserved in separate, non-indexed storage areas utilizing temporary signature tokens.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section: Retention -->
                <section id="retention" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">07</span> Data Retention Policy
                    </h2>
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                        <div class="space-y-6 text-slate-600">
                            <div class="flex gap-6 items-start">
                                <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0 font-bold">A</div>
                                <div>
                                    <h3 class="font-bold text-slate-900">Operational Data</h3>
                                    <p class="text-sm">Production timelines, order assignments, and fiscal confirmations are retained for a baseline duration of <strong>7 years</strong> to comply with statutory expectations and financial frameworks in India.</p>
                                </div>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0 font-bold">B</div>
                                <div>
                                    <h3 class="font-bold text-slate-900">Consultation Logs & Consultation Records</h3>
                                    <p class="text-sm">Information gathered under scheduled updates, meeting parameters, and reference metrics remains available for <strong>2 years</strong> to ensure design accuracy, following which it is structured for removal.</p>
                                </div>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0 font-bold">C</div>
                                <div>
                                    <h3 class="font-bold text-slate-900">System Activity Records</h3>
                                    <p class="text-sm">Access trails, temporary operational tags, and validation steps are cleared every <strong>90 days</strong> to protect resource efficiency and access bounds.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section: Rights -->
                <section id="rights" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">08</span> Your Data Rights
                    </h2>
                    <p class="text-slate-600 mb-8 leading-relaxed">Platform users maintain structured management rights regarding their processing profiles. You can engage our data desk to carry out the following processes:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-6 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">
                            <h3 class="font-bold text-slate-900 mb-2">Right to Access</h3>
                            <p class="text-xs text-slate-500 uppercase tracking-widest mb-3">Response: 15 Working Days</p>
                            <p class="text-sm">Obtain an explicit compilation of the operational information managed within our enterprise systems in a legible form.</p>
                        </div>
                        <div class="p-6 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">
                            <h3 class="font-bold text-slate-900 mb-2">Right to Deletion</h3>
                            <p class="text-xs text-slate-500 uppercase tracking-widest mb-3">Response: 30 Working Days</p>
                            <p class="text-sm">Request complete removal of your active application footprint, including related meeting records and design logs (barring regulatory retention mandates).</p>
                        </div>
                    </div>
                </section>

                <!-- Section: Roles -->
                <section id="roles" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">09</span> Role-Based Governance
                    </h2>
                    <div class="overflow-hidden border border-slate-200 rounded-3xl bg-white shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-900 text-white text-xs uppercase tracking-widest font-bold">
                                <tr>
                                    <th class="p-6">Entity Role</th>
                                    <th class="p-6">Operational Rights</th>
                                    <th class="p-6">Data Access Scope</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr>
                                    <td class="p-6 font-bold text-slate-900 bg-slate-50/50">Admins</td>
                                    <td class="p-6">Full CRUD, Allocation, Global Approvals, Meeting Moderation</td>
                                    <td class="p-6 text-slate-500 italic">Universal Read/Write</td>
                                </tr>
                                <tr>
                                    <td class="p-6 font-bold text-slate-900 bg-slate-50/50">Buyers / Users</td>
                                    <td class="p-6">Order Creation, Design Submission, Meeting Scheduling</td>
                                    <td class="p-6 text-slate-500 italic">Self-owned Records only</td>
                                </tr>
                                <tr>
                                    <td class="p-6 font-bold text-slate-900 bg-slate-50/50">Craftsmen</td>
                                    <td class="p-6">Process Management, Work Proof capture, Timeline Updates</td>
                                    <td class="p-6 text-slate-500 italic">Assigned Tasks only</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Section: Intellectual Property -->
                <section id="intellectual" class="scroll-mt-8">
                    <h2 class="brand-font text-3xl text-slate-900 mb-6 flex items-center">
                        <span class="text-amber-500 mr-4 text-4xl">10</span> Intellectual Property
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed border-l-4 border-slate-900 pl-8">
                        <p>
                            All design models, custom geometric structures, dynamic renders, structural methodologies, and organizational assets logged inside this solution remain the <strong>EXCLUSIVE INTELLECTUAL PROPERTY</strong> of Arihanth Jewellers Pvt Ltd.
                        </p>
                        <p class="mt-4 italic">
                            Appropriate operational and structural enforcement parameters will be deployed under the Information Technology Act, 2000 and the Copyright Act, 1957 relative to any illicit data collection or asset redistribution.
                        </p>
                    </div>
                </section>

                <!-- Section: Contact -->
                <section id="contact" class="scroll-mt-8">
                    <div class="bg-amber-50 rounded-3xl p-10 border border-amber-100">
                        <h2 class="brand-font text-3xl text-slate-900 mb-4">11 Grievance Redressal</h2>
                        <p class="text-slate-600 mb-6">For queries regarding your data safety, application authorizations, or to manage scheduled items:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="font-bold text-slate-900 mb-1">Data Protection Officer</h3>
                                <p class="text-sm text-slate-500 mb-2">Arihanth Jewellers Pvt Ltd, Mumbai</p>
                                <a href="mailto:arihanthjewellers@gmail.com" class="text-amber-600 font-bold hover:underline">arihanthjewellers@gmail.com</a>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 mb-1">Technical Support</h3>
                                <p class="text-sm text-slate-500 mb-2">Lasirene Exim Pvt Ltd (Admin Desk)</p>
                                <a href="mailto:support@lasirene.com" class="text-amber-600 font-bold hover:underline">support@lasirene.com</a>
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- Enhanced Footer -->
    <footer class="bg-slate-900 text-slate-400 py-20 mt-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h3 class="brand-font text-2xl text-amber-400 mb-2">ARIHANTH JEWELLERS PVT LTD</h3>
            <p class="text-sm uppercase tracking-widest mb-10">Trusted Manufacturers Since Decades</p>

            <div class="flex justify-center gap-8 mb-12">
                <div class="h-1 bg-amber-500/30 w-12 rounded-full"></div>
                <div class="h-1 bg-amber-500/30 w-12 rounded-full"></div>
                <div class="h-1 bg-amber-500/30 w-12 rounded-full"></div>
            </div>

            <p class="text-xs leading-loose font-light mb-8 max-w-2xl mx-auto">
                System Architecture and Cyber-Security Governance by <br>
                <span class="text-white font-bold tracking-widest">LASIRENE EXIM PVT LTD</span> <br>
                Software Solutions & Enterprise Administration Solutions.
            </p>

            <p class="text-[10px] text-slate-600 italic">
                &copy; {{ date('Y') }} All Rights Reserved. This portal is for authorized corporate use only. <br>
                Compliance: IT Act 2000 | GDPR (Internal Standards) | Play Store & App Store Policy 2026.
            </p>
        </div>
    </footer>

    <!-- Progress Script for Sticky TOC -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('.toc-link');
            const sections = document.querySelectorAll('section');

            function changeActiveLink() {
                let index = sections.length;
                while (--index && window.scrollY + 100 < sections[index].offsetTop) {}
                links.forEach((link) => link.classList.remove('active'));
                links[index].classList.add('active');
            }

            window.addEventListener('scroll', changeActiveLink);
            changeActiveLink();
        });
    </script>

</body>

</html>