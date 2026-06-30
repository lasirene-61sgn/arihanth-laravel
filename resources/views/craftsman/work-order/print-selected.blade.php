<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            body { background: white; }
        }
        /* Ensures the capture area has a clean background */
        .capture-area { background: white; }
    </style>
</head>
<body class="bg-gray-100 antialiased text-gray-900">

    <div class="no-print bg-white p-4 flex justify-center gap-4 border-b border-gray-200 sticky top-0 z-50">
        <button onclick="window.print()" class="px-5 py-2 bg-slate-800 text-white text-sm font-medium rounded shadow-sm hover:bg-slate-700 transition">
            Print / Save PDF
        </button>
        <button onclick="saveAsImage()" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded shadow-sm hover:bg-blue-700 transition">
            Download PNG Image
        </button>
    </div>

    <div id="print-content">
        @foreach($workOrders as $workOrder)
        <div class="capture-area max-w-3xl mx-auto p-8 mb-4 border border-gray-200 md:my-6 bg-white {{ !$loop->last ? 'page-break' : '' }}">
            
            <div class="flex flex-col md:flex-row gap-10">
                <div class="flex-1 space-y-6">
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-widest text-blue-600 mb-4 border-b pb-1">
                            Specifications
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">WorkOrder Number</p>
                                <p class="text-sm font-medium text-gray-700 capitalize">{{ (string)($workOrder->work_order_number ?? '') }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Status</p>
                                <p class="text-sm font-medium text-gray-700 capitalize">{{ (string)($workOrder->craftsman_status ?? '') }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Category</p>
                                <p class="text-sm text-gray-800">{{ $workOrder->productCategory->name ?? (is_array($workOrder->product_category) ? implode(', ', $workOrder->product_category) : ($workOrder->product_category ?? 'N/A')) }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Subcategory</p>
                                <p class="text-sm text-gray-800">{{ $workOrder->subcategoryRelation->name ?? (is_array($workOrder->subcategory) ? implode(', ', $workOrder->subcategory) : ($workOrder->subcategory ?? 'N/A')) }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Order Type</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->type) ? implode(', ', $workOrder->type) : ($workOrder->type ?? 'Regular') }}</p>
                            </div>

                             @if($workOrder->due_date)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Order Date</p>
                                <p class="text-sm text-gray-800">{{ $workOrder->due_date ? $workOrder->due_date->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            @endif

                             @if($workOrder->craftsman_due_date)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Due Date</p>
                                <p class="text-sm text-gray-800">{{ $workOrder->craftsman_due_date ? $workOrder->craftsman_due_date->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            @endif

                            @if($workOrder->weight_from)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Weight</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->weight_from) ? implode(', ', $workOrder->weight_from) : $workOrder->weight_from }}</p>
                            </div>
                            @endif

                            @if($workOrder->quantity)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Quantity</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->quantity) ? implode(', ', $workOrder->quantity) : $workOrder->quantity }}</p>
                            </div>
                            @endif
                             @if($workOrder->hook)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Hook</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->hook) ? implode(', ', $workOrder->hook) : $workOrder->hook }}</p>
                            </div>
                            @endif
                             @if($workOrder->enamel)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Enamel</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->enamel) ? implode(', ', $workOrder->enamel) : $workOrder->enamel }}</p>
                            </div>
                            @endif
                             @if($workOrder->rodium)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Rodium</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->rodium) ? implode(', ', $workOrder->rodium) : $workOrder->rodium }}</p>
                            </div>
                            @endif
                             @if($workOrder->stone)
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Stone</p>
                                <p class="text-sm text-gray-800">{{ is_array($workOrder->stone) ? implode(', ', $workOrder->stone) : $workOrder->stone }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($workOrder->size || $workOrder->length)
                    <div class="space-y-3">
                        @if($workOrder->size)
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Size Details</p>
                            <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded mt-1">{{ is_array($workOrder->size) ? implode(', ', $workOrder->size) : $workOrder->size }}</p>
                        </div>
                        @endif
                        
                        @if($workOrder->length)
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Length</p>
                            <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded mt-1">{{ is_array($workOrder->length) ? implode(', ', $workOrder->length) : $workOrder->length }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="w-full md:w-72 space-y-4">
                    @php 
                        $productGallery = $workOrder->product_gallery_images; 
                        $processedImages = [];
                        foreach ($productGallery ?? [] as $imagePath) {
                            if (!$imagePath || !is_string($imagePath)) continue;
                            
                            if (Str::endsWith(strtolower($imagePath), '.pdf')) {
                                $processedImages[] = ['type' => 'pdf', 'content' => $imagePath];
                                continue;
                            }

                            $relativePath = str_replace(asset(''), '', $imagePath);
                            $fullPath = public_path(ltrim($relativePath, '/'));

                            if (!file_exists($fullPath)) {
                                $storagePath = str_replace('storage/', '', $relativePath);
                                $fullPath = storage_path('app/public/' . ltrim($storagePath, '/'));
                            }

                            if (file_exists($fullPath) && !empty(getimagesize($fullPath))) {
                                $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                                $data = file_get_contents($fullPath);
                                $base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
                                $processedImages[] = ['type' => 'image', 'content' => $base64];
                            } else {
                                $processedImages[] = ['type' => 'image', 'content' => $imagePath];
                            }
                        }
                    @endphp

                    @forelse($processedImages as $img)
                        <div class="relative group mb-4">
                            @if($img['type'] === 'pdf')
                                <div class="w-full border border-gray-100 rounded-lg bg-gray-50 flex flex-col items-center justify-center shadow-sm overflow-hidden p-1">
                                    <canvas class="pdf-canvas w-full h-auto" data-url="{{ $img['content'] }}" data-desired-width="288"></canvas>
                                </div>
                            @else
                                <div class="aspect-square w-full border border-gray-100 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden shadow-sm">
                                    <img src="{{ $img['content'] }}" crossorigin="anonymous" class="w-full h-full object-contain p-2">
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="aspect-square w-full border-2 border-dashed border-gray-100 rounded-lg flex flex-col items-center justify-center text-gray-300">
                            <span class="text-[10px] uppercase font-bold">No Image Available</span>
                        </div>
                    @endforelse

                    <!-- @php $completionProofs = $workOrder->completion_proof_images; @endphp
                    @if(!empty($completionProofs))
                        <div class="pt-4 space-y-3">
                            <p class="text-[10px] font-black text-center text-blue-500 uppercase tracking-widest">Completion Proof</p>
                            @foreach($completionProofs as $imagePath)
                                <div class="aspect-square border-2 border-blue-50 rounded-lg overflow-hidden shadow-sm">
                                    <img src="{{ $imagePath }}" crossorigin="anonymous" class="w-full h-full object-contain p-1">
                                </div>
                            @endforeach
                        </div>
                    @endif -->
                </div>
            </div>

            @if($workOrder->narration_craftsman)
            <div class="mt-10 pt-6 border-t border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Notes & Instructions</p>
                <div class="text-sm text-gray-600 leading-relaxed italic">
                    "{{ is_array($workOrder->narration_craftsman) ? implode(', ', $workOrder->narration_craftsman) : $workOrder->narration_craftsman }}"
                </div>
            </div>
            @endif

            <div class="mt-12 text-center text-[9px] text-gray-300 uppercase tracking-widest">
                Ref: {{ $workOrder->work_order_number }} • {{ now()->format('M d, Y') }}
            </div>
        </div>
        @endforeach
    </div>

    <script>
        function saveAsImage() {
            const element = document.getElementById('print-content');
            
            // Configuration for high quality
            const options = {
                scale: 2, // Higher scale for better resolution
                useCORS: true, // Crucial for loading external images
                logging: false,
                backgroundColor: "#f3f4f6" // Matches the gray background
            };

            html2canvas(element, options).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Work-Order-{{ is_array($workOrder->work_order_number) ? implode("_", $workOrder->work_order_number) : ($workOrder->work_order_number ?? "export") }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }

        // Shortcut for Ctrl + S to trigger image download instead of HTML save
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveAsImage();
            }
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        // PDFjs configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        async function redactSensitiveText(page, context, viewport) {
            const textContent = await page.getTextContent();
            const items = textContent.items;

            const fieldsToHide = [
                "order id", "design", "qty", "weight", "size", "product", "tolerance from", "tolerance to"
            ];

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

            if (items.length == 0) {
                context.fillStyle = "#FFFFFF";
                context.fillRect(viewport.width * 0.04, viewport.height * 0.03, viewport.width * 0.50, viewport.height * 0.20);
            }
        }

        async function renderPdfs() {
            const canvases = document.querySelectorAll('.pdf-canvas');
            
            for (const canvas of canvases) {
                const url = canvas.dataset.url;
                const desiredWidth = parseInt(canvas.dataset.desiredWidth) || 288;

                try {
                    const pdf = await pdfjsLib.getDocument(url).promise;
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        let currentCanvas = canvas;
                        
                        if (pageNum > 1) {
                            currentCanvas = document.createElement('canvas');
                            currentCanvas.className = 'pdf-canvas mb-3';
                            currentCanvas.style.width = '100%';
                            currentCanvas.style.border = '1px solid #ddd';
                            canvas.parentElement.appendChild(currentCanvas);
                        }

                        const page = await pdf.getPage(pageNum);
                        
                        // Fixed high-resolution scale (4x) for extreme zoom clarity
                        const scale = 4.0; 
                        const viewport = page.getViewport({ scale: scale });

                        const context = currentCanvas.getContext('2d');
                        currentCanvas.width = viewport.width;
                        currentCanvas.height = viewport.height;
                        currentCanvas.style.width = '100%'; // Style scales high-res canvas to container

                        await page.render({ canvasContext: context, viewport: viewport }).promise;
                        await redactSensitiveText(page, context, viewport);
                    }
                } catch (error) {
                    console.error("Error rendering PDF:", error);
                }
            }
        }

        window.onload = function() {
            renderPdfs().then(() => {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('auto_print')) {
                    setTimeout(() => { window.print(); }, 1000);
                }
            });
        }
    </script>
</body>
</html>
