<?php
$files = [
    'admin' => "e:/public_html/resources/views/admin/purchase-order/show.blade.php",
    'super_admin' => "e:/public_html/resources/views/super-admin/purchase-order/show.blade.php"
];

foreach ($files as $guard => $path) {
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        continue;
    }
    
    $c = file_get_contents($path);
    
    $search = '<div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created At</label>
                    <p class="text-gray-900 font-medium">{{ $purchaseOrder->created_at->format(\'d M, Y H:i\') }}</p>
                </div>';
                
    $replace = '<div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created At</label>
                    <p class="text-gray-900 font-medium">{{ $purchaseOrder->created_at->format(\'d M, Y H:i\') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created By</label>
                    @php $creator = $purchaseOrder->creator_details; @endphp
                    <p class="text-gray-900 font-medium">{{ $creator[\'name\'] }} <span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $creator[\'type\'] }}</span></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Allocated By</label>
                    @php $allocator = $purchaseOrder->allocator_details; @endphp
                    <p class="text-gray-900 font-medium">{{ $allocator[\'name\'] }} @if($allocator[\'name\'] !== \'N/A\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $allocator[\'type\'] }}</span>@endif</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Approved By</label>
                    @php $approver = $purchaseOrder->approver_details; @endphp
                    <p class="text-gray-900 font-medium">{{ $approver[\'name\'] }} @if($approver[\'name\'] !== \'N/A\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $approver[\'type\'] }}</span>@endif</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Completed Date</label>
                    <p class="text-gray-900 font-medium">
                        @if(strtolower($purchaseOrder->status) === \'completed\')
                            <span class="text-green-600">{{ $purchaseOrder->updated_at ? $purchaseOrder->updated_at->format(\'d M, Y H:i\') : \'N/A\' }}</span>
                        @else
                            <span class="text-gray-400">Not Completed</span>
                        @endif
                    </p>
                </div>';
                
    $c = str_replace(str_replace("\n", "\r\n", $search), str_replace("\n", "\r\n", $replace), $c);
    $c = str_replace($search, $replace, $c);

    // Also change grid-cols-4 to grid-cols-8 or grid-cols-4 and add more rows.
    // It's `grid grid-cols-1 md:grid-cols-4 gap-6 mb-8`
    // Let's change it to `grid grid-cols-1 md:grid-cols-4 gap-6 gap-y-8 mb-8`
    $c = str_replace('grid grid-cols-1 md:grid-cols-4 gap-6 mb-8', 'grid grid-cols-1 md:grid-cols-4 gap-6 gap-y-8 mb-8', $c);

    file_put_contents($path, $c);
    echo "Updated show blade for $guard\n";
}
