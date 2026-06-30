<?php

$files = [
    'resources/views/super-admin/work-order/index.blade.php',
    'resources/views/super-admin/purchase-order/index.blade.php',
    'resources/views/super-admin/stock-order/index.blade.php',
    'resources/views/admin/work-order/index.blade.php',
    'resources/views/admin/purchase-order/index.blade.php',
    'resources/views/admin/stock-order/index.blade.php',
    'resources/views/craftsman/work-order/index.blade.php',
    'resources/views/craftsman/purchase-order/index.blade.php',
    'resources/views/craftsman/stock-order/index.blade.php',
];

$logic = <<<'LOGIC'
                                @php
                                $rowBgClass = '';
                                $isOverdue = false;
                                $isDueWithin48Hours = false;
                                $allocatedWithin48Hours = false;
                                $now = \Carbon\Carbon::now();

                                if ($order->craftsman_due_date) {
                                    $dueDate = \Carbon\Carbon::parse($order->craftsman_due_date);
                                    if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                        $isOverdue = true;
                                    } else {
                                        $hoursDiff = $now->diffInHours($dueDate, false);
                                        if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                            $isDueWithin48Hours = true;
                                        }
                                    }
                                }

                                if (isset($activeTab) && $activeTab == 'allocated-orders' && $order->updated_at) {
                                    if ($order->updated_at->diffInHours($now) <= 48) {
                                        $allocatedWithin48Hours = true;
                                    }
                                }

                                if ($isOverdue) {
                                    $rowBgClass = 'tw-bg-rose-50/50';
                                } elseif ($isDueWithin48Hours) {
                                    $rowBgClass = 'tw-bg-orange-50/50';
                                } elseif (isset($activeTab) && $activeTab == 'in-process-orders') {
                                    $rowBgClass = 'tw-bg-green-50/50';
                                } elseif (isset($activeTab) && $activeTab == 'allocated-orders' && $allocatedWithin48Hours) {
                                    $rowBgClass = 'tw-bg-blue-50/50';
                                } elseif (isset($activeTab) && $activeTab == 'new-orders') {
                                    $rowBgClass = 'tw-bg-yellow-50/50';
                                }
                                @endphp
LOGIC;

foreach ($files as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (!file_exists($fullPath)) {
        echo "File not found: $fullPath\n";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // We want to replace the PHP block that calculates isOverdue, and then the <tr class="..."> tag
    // We will look for @foreach(...) ... @php ... $isOverdue ... @endphp ... <tr ...>
    
    $pattern = '/@php\s+(?:(?!\@endphp).)*?\$isOverdue.*?\@endphp\s*<tr[^>]*class="([^"]*)"[^>]*>/is';
    
    $content = preg_replace_callback($pattern, function($matches) use ($logic) {
        $classes = $matches[1];
        // Remove existing color classes like tw-bg-rose-50/50 or hover:tw-bg-gray-50
        $classes = preg_replace('/tw-bg-rose-[^\s]+/', '', $classes);
        $classes = preg_replace('/tw-bg-red-[^\s]+/', '', $classes);
        $classes = preg_replace('/tw-bg-gray-[^\s]+/', '', $classes);
        $classes = preg_replace('/hover:tw-bg-[^\s]+/', '', $classes);
        $classes = preg_replace('/tw-transition-[^\s]+/', '', $classes);
        $classes = str_replace('{{ $isOverdue ? \'\' : \'\' }}', '', $classes);
        $classes = str_replace('{{ $isOverdue ? \'tw-bg-rose-50/50\' : \'\' }}', '', $classes);
        // It's cleaner to just provide standard classes and append $rowBgClass
        // We will keep hover effect
        return $logic . "\n" . '                                <tr class="hover:tw-bg-gray-50 tw-transition-colors {{ $rowBgClass }} ' . trim(preg_replace('/\{\{.*?\}\}/', '', $classes)) . '">';
    }, $content);
    
    file_put_contents($fullPath, $content);
    echo "Updated $file\n";
}
echo "Done.\n";
