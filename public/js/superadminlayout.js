 
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';


        // 🔴 WORDS TO HIDE ONLY
        const REDACT_WORDS = [
            'order id',
            'design',
            'qty',
            'weight',
            'size',
            'product',
            'tolerance from',
            'tolerance to'
        ];


        // 🔴 TEXT BASED REDACTION FUNCTION
        async function renderPageWithRedaction(page, canvas, scale = 1.5) {

            const viewport = page.getViewport({
                scale: scale
            });

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            const ctx = canvas.getContext('2d');

            await page.render({
                canvasContext: ctx,
                viewport: viewport
            }).promise;

            const textContent = await page.getTextContent();

            textContent.items.forEach(item => {

                const text = item.str.toLowerCase();

                if (REDACT_WORDS.some(word => text.includes(word))) {

                    const tx = pdfjsLib.Util.transform(
                        viewport.transform,
                        item.transform
                    );

                    const x = tx[4];
                    const y = tx[5];
                    const width = item.width * viewport.scale;
                    const height = item.height * viewport.scale;

                    ctx.fillStyle = "#FFFFFF";

                    ctx.fillRect(
                        x - 5,
                        y - height,
                        width + 15,
                        height + 5
                    );
                }
            });
        }



        // 🔴 THUMBNAIL WITH PAGE COUNT BADGE
        window.renderPdfThumbnails = function() {

            const canvases =
                document.querySelectorAll('.pdf-canvas:not([data-rendered="true"])');

            canvases.forEach(async canvas => {

                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 100;

                canvas.dataset.rendered = 'true';

                try {

                    const pdf =
                        await pdfjsLib.getDocument(url).promise;

                    const numPages = pdf.numPages;

                    // 🟢 YOUR EXISTING BADGE UPDATED
                    if (numPages > 1) {

                        const container = canvas.parentElement;

                        if (container &&
                            !container.querySelector('.pdf-page-count-badge')) {

                            const badge =
                                document.createElement('span');

                            badge.className =
                                'pdf-page-count-badge position-absolute bottom-0 end-0 badge rounded-pill bg-dark bg-opacity-75';

                            badge.style.fontSize = '0.6rem';
                            badge.style.padding = '2px 4px';
                            badge.innerText = '+' + (numPages - 1);

                            container.appendChild(badge);
                        }
                    }

                    const page =
                        await pdf.getPage(1);

                    const viewport_raw =
                        page.getViewport({
                            scale: 1
                        });

                    const scale =
                        desiredWidth / viewport_raw.width;

                    await renderPageWithRedaction(page, canvas, scale);

                } catch (error) {

                    console.error('Error rendering PDF:', error);

                    const ctx = canvas.getContext('2d');
                    ctx.font = '10px Arial';
                    ctx.fillText('PDF Error', 10, 25);
                }
            });
        }



        // 🔴 PREVIEW MODAL MULTI PAGE SAFE
        window.openUniversalPreview =
            async function(url, type) {

                const modal =
                    new bootstrap.Modal(
                        document.getElementById('pdfPreviewModal'));

                modal.show();

                const container =
                    document.getElementById('modalPreviewContainer');

                container.innerHTML = '';

                if (type === 'pdf') {

                    const pdf =
                        await pdfjsLib.getDocument(url).promise;

                    for (let i = 1; i <= pdf.numPages; i++) {

                        const canvas =
                            document.createElement('canvas');

                        canvas.className =
                            'img-fluid mb-2 border-bottom';

                        container.appendChild(canvas);

                        const page =
                            await pdf.getPage(i);

                        await renderPageWithRedaction(page, canvas, 2);
                    }
                } else {
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'img-fluid';
                    container.appendChild(img);
                }
            };


        document.addEventListener(
            'DOMContentLoaded',
            renderPdfThumbnails
        );

        // Global object to store rates per 1 gram
        let liveData = {
            g24: 0,
            s1: 0
        };

        async function getLiveRates() {
            try {
                // Fetching from your Laravel backend (which scrapes the association site)
                const response = await fetch('/api/live-gold-rates');
                const data = await response.json();

                // Store the base 24K rate and Silver rate
                liveData.g24 = data.gold24;
                liveData.s1 = data.silver;

                // Default to showing 22K on page load
                updateUI();

                // Update Silver display (Formatted to 2 decimal places for Paise)
                const silverDisplay = document.getElementById('silver-rate-display');
                if (silverDisplay) {
                    silverDisplay.innerText = 
                        new Intl.NumberFormat('en-IN', {
                            style: 'currency',
                            currency: 'INR',
                            maximumFractionDigits: 2
                        })
                        .format(liveData.s1);
                }

            } catch (e) {
                console.error("Live Rate Error:", e);
                const goldDisplay = document.getElementById('gold-rate-display');
                if (goldDisplay) {
                    goldDisplay.innerText = "Rate Error";
                }
            }
        }

        function updateUI() {
            // 22K is approx 91.6% Pure
            let rate = liveData.g24 * 0.916;

            // Format as Indian Rupees (₹) with comma separators (e.g., 7,450)
            const formatter = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            const goldDisplay = document.getElementById('gold-rate-display');
            if (goldDisplay) {
                goldDisplay.innerText = formatter.format(rate);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', getLiveRates);

        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar logic
            const sidebar = document.getElementById('sidebar-wrapper');
            const toggle = document.getElementById('sidebar-toggle');
            const close = document.getElementById('sidebar-close');
            const overlay = document.getElementById('sidebar-overlay');

            toggle.addEventListener('click', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.toggle('md:tw-ml-0');
                    sidebar.classList.toggle('md:-tw-ml-72');
                } else {
                    sidebar.classList.toggle('-tw-ml-72');
                    sidebar.classList.toggle('tw-ml-0');
                    overlay.classList.toggle('tw-hidden');
                }
            });

            close.addEventListener('click', function() {
                sidebar.classList.add('-tw-ml-72');
                sidebar.classList.remove('tw-ml-0');
                overlay.classList.add('tw-hidden');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.add('-tw-ml-72');
                sidebar.classList.remove('tw-ml-0');
                overlay.classList.add('tw-hidden');
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('-tw-ml-72', 'tw-ml-0');
                    sidebar.classList.add('md:tw-ml-0');
                    overlay.classList.add('tw-hidden');
                } else {
                    sidebar.classList.add('-tw-ml-72');
                }
            });

            // Dark Mode Logic
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            const darkIcon = document.getElementById('dark-icon');
            const lightIcon = document.getElementById('light-icon');

            function updateIcons() {
                if (document.documentElement.classList.contains('dark')) {
                    darkIcon.classList.add('tw-hidden');
                    lightIcon.classList.remove('tw-hidden');
                } else {
                    darkIcon.classList.remove('tw-hidden');
                    lightIcon.classList.add('tw-hidden');
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

            // IST Clock Implementation
            function updateClock() {
                const now = new Date();
                const options = {
                    timeZone: 'Asia/Kolkata',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };
                const formatter = new Intl.DateTimeFormat('en-IN', options);
                document.getElementById('ist-clock').innerText = formatter.format(now);
            }
            setInterval(updateClock, 1000);
            updateClock();


            // Re-initialize Bootstrap tabs if needed
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                var tabTrigger = document.querySelector('#' + tab + '-tab');
                if (tabTrigger) {
                    var bsTab = new bootstrap.Tab(tabTrigger);
                    bsTab.show();
                }
            }
        });
