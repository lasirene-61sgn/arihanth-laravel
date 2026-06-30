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
        // Creator
        $c = str_replace(
            '<p class="text-gray-900 font-medium">{{ $creator[\'name\'] }} <span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $creator[\'type\'] }}</span></p>',
            '<p class="text-gray-900 font-medium">{{ $creator[\'name\'] === \'System\' ? \'N/A\' : $creator[\'name\'] }} @if($creator[\'name\'] !== \'System\' && $creator[\'name\'] !== \'N/A\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $creator[\'type\'] }}</span>@endif</p>',
            $c
        );
        // Allocator
        $c = str_replace(
            '<p class="text-gray-900 font-medium">{{ $allocator[\'name\'] }} @if($allocator[\'name\'] !== \'N/A\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $allocator[\'type\'] }}</span>@endif</p>',
            '<p class="text-gray-900 font-medium">{{ $allocator[\'name\'] === \'Unknown User\' ? \'N/A\' : $allocator[\'name\'] }} @if($allocator[\'name\'] !== \'N/A\' && $allocator[\'name\'] !== \'Unknown User\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $allocator[\'type\'] }}</span>@endif</p>',
            $c
        );
        // Approver
        $c = str_replace(
            '<p class="text-gray-900 font-medium">{{ $approver[\'name\'] }} @if($approver[\'name\'] !== \'N/A\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $approver[\'type\'] }}</span>@endif</p>',
            '<p class="text-gray-900 font-medium">{{ $approver[\'name\'] === \'Unknown User\' ? \'N/A\' : $approver[\'name\'] }} @if($approver[\'name\'] !== \'N/A\' && $approver[\'name\'] !== \'Unknown User\')<span class="text-[10px] bg-gray-200 text-gray-700 px-1 rounded">{{ $approver[\'type\'] }}</span>@endif</p>',
            $c
        );
    } else {
        // Creator
        $c = str_replace(
            '<p class="mb-0">{{ $creator[\'name\'] }} <span class="badge bg-secondary">{{ $creator[\'type\'] }}</span></p>',
            '<p class="mb-0">{{ $creator[\'name\'] === \'System\' ? \'N/A\' : $creator[\'name\'] }} @if($creator[\'name\'] !== \'System\' && $creator[\'name\'] !== \'N/A\')<span class="badge bg-secondary">{{ $creator[\'type\'] }}</span>@endif</p>',
            $c
        );
        // Allocator
        $c = str_replace(
            '<p class="mb-0">{{ $allocator[\'name\'] }} @if($allocator[\'name\'] !== \'N/A\')<span class="badge bg-secondary">{{ $allocator[\'type\'] }}</span>@endif</p>',
            '<p class="mb-0">{{ $allocator[\'name\'] === \'Unknown User\' ? \'N/A\' : $allocator[\'name\'] }} @if($allocator[\'name\'] !== \'N/A\' && $allocator[\'name\'] !== \'Unknown User\')<span class="badge bg-secondary">{{ $allocator[\'type\'] }}</span>@endif</p>',
            $c
        );
        // Approver
        $c = str_replace(
            '<p class="mb-0">{{ $approver[\'name\'] }} @if($approver[\'name\'] !== \'N/A\')<span class="badge bg-secondary">{{ $approver[\'type\'] }}</span>@endif</p>',
            '<p class="mb-0">{{ $approver[\'name\'] === \'Unknown User\' ? \'N/A\' : $approver[\'name\'] }} @if($approver[\'name\'] !== \'N/A\' && $approver[\'name\'] !== \'Unknown User\')<span class="badge bg-secondary">{{ $approver[\'type\'] }}</span>@endif</p>',
            $c
        );
    }
    
    file_put_contents($path, $c);
}
echo "Cleaned up N/A and System tags.\n";
