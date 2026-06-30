<?php
$files = [
    'admin' => "e:/public_html/resources/views/admin/purchase-order/show.blade.php",
    'super_admin' => "e:/public_html/resources/views/super-admin/purchase-order/show.blade.php"
];

foreach ($files as $guard => $path) {
    if (!file_exists($path)) {
        continue;
    }
    
    $c = file_get_contents($path);
    
    if ($guard === 'admin') {
        // Wrap Creator
        $c = preg_replace(
            '/<div>\s*<label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created By<\/label>\s*@php \$creator = \$purchaseOrder->creator_details; @endphp\s*<p class="text-gray-900 font-medium">.*?<\/p>\s*<\/div>/s',
            '@if($purchaseOrder->creator_details[\'name\'] !== \'System\' && $purchaseOrder->creator_details[\'name\'] !== \'N/A\')
                  <div>
                      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created By</label>
                      @php $creator = $purchaseOrder->creator_details; @endphp
                      <p class="text-gray-900 font-medium">{{ $creator[\'name\'] }} <span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $creator[\'type\'] }}</span></p>
                  </div>
                  @endif',
            $c
        );
        // Wrap Allocator
        $c = preg_replace(
            '/<div>\s*<label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Allocated By<\/label>\s*@php \$allocator = \$purchaseOrder->allocator_details; @endphp\s*<p class="text-gray-900 font-medium">.*?<\/p>\s*<\/div>/s',
            '@if($purchaseOrder->allocator_details[\'name\'] !== \'Unknown User\' && $purchaseOrder->allocator_details[\'name\'] !== \'N/A\')
                  <div>
                      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Allocated By</label>
                      @php $allocator = $purchaseOrder->allocator_details; @endphp
                      <p class="text-gray-900 font-medium">{{ $allocator[\'name\'] }} <span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $allocator[\'type\'] }}</span></p>
                  </div>
                  @endif',
            $c
        );
        // Wrap Approver
        $c = preg_replace(
            '/<div>\s*<label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Approved By<\/label>\s*@php \$approver = \$purchaseOrder->approver_details; @endphp\s*<p class="text-gray-900 font-medium">.*?<\/p>\s*<\/div>/s',
            '@if($purchaseOrder->approver_details[\'name\'] !== \'Unknown User\' && $purchaseOrder->approver_details[\'name\'] !== \'N/A\')
                  <div>
                      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Approved By</label>
                      @php $approver = $purchaseOrder->approver_details; @endphp
                      <p class="text-gray-900 font-medium">{{ $approver[\'name\'] }} <span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $approver[\'type\'] }}</span></p>
                  </div>
                  @endif',
            $c
        );
    } else {
        // Wrap Creator
        $c = preg_replace(
            '/<div class="col-md-3 mt-3">\s*<label class="form-label fw-bold text-muted small">Created By<\/label>\s*@php \$creator = \$purchaseOrder->creator_details; @endphp\s*<p class="mb-0">.*?<\/p>\s*<\/div>/s',
            '@if($purchaseOrder->creator_details[\'name\'] !== \'System\' && $purchaseOrder->creator_details[\'name\'] !== \'N/A\')
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Created By</label>
                            @php $creator = $purchaseOrder->creator_details; @endphp
                            <p class="mb-0">{{ $creator[\'name\'] }} <span class="badge bg-secondary">{{ $creator[\'type\'] }}</span></p>
                        </div>
                        @endif',
            $c
        );
        // Wrap Allocator
        $c = preg_replace(
            '/<div class="col-md-3 mt-3">\s*<label class="form-label fw-bold text-muted small">Allocated By<\/label>\s*@php \$allocator = \$purchaseOrder->allocator_details; @endphp\s*<p class="mb-0">.*?<\/p>\s*<\/div>/s',
            '@if($purchaseOrder->allocator_details[\'name\'] !== \'Unknown User\' && $purchaseOrder->allocator_details[\'name\'] !== \'N/A\')
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Allocated By</label>
                            @php $allocator = $purchaseOrder->allocator_details; @endphp
                            <p class="mb-0">{{ $allocator[\'name\'] }} <span class="badge bg-secondary">{{ $allocator[\'type\'] }}</span></p>
                        </div>
                        @endif',
            $c
        );
        // Wrap Approver
        $c = preg_replace(
            '/<div class="col-md-3 mt-3">\s*<label class="form-label fw-bold text-muted small">Approved By<\/label>\s*@php \$approver = \$purchaseOrder->approver_details; @endphp\s*<p class="mb-0">.*?<\/p>\s*<\/div>/s',
            '@if($purchaseOrder->approver_details[\'name\'] !== \'Unknown User\' && $purchaseOrder->approver_details[\'name\'] !== \'N/A\')
                        <div class="col-md-3 mt-3">
                            <label class="form-label fw-bold text-muted small">Approved By</label>
                            @php $approver = $purchaseOrder->approver_details; @endphp
                            <p class="mb-0">{{ $approver[\'name\'] }} <span class="badge bg-secondary">{{ $approver[\'type\'] }}</span></p>
                        </div>
                        @endif',
            $c
        );
    }
    
    file_put_contents($path, $c);
}
echo "Wrapped UI elements with @if checks.\n";
