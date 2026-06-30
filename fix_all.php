<?php

$poFiles = [
    'resources/views/super-admin/purchase-order/index.blade.php',
    'resources/views/admin/purchase-order/index.blade.php',
    'resources/views/craftsman/purchase-order/index.blade.php',
];

$poLogic = <<<'LOGIC'
                                @php
                                $rowBgClass = '';
                                $isOverdue = false;
                                $isDueWithin48Hours = false;
                                $allocatedWithin48Hours = false;
                                $now = \Carbon\Carbon::now();

                                if ($po->due_date) {
                                    $dueDate = \Carbon\Carbon::parse($po->due_date);
                                    if ($dueDate->lt($now->startOfDay()) || ($dueDate->isToday() && $now->hour >= 12)) {
                                        $isOverdue = true;
                                    } else {
                                        $hoursDiff = $now->diffInHours($dueDate, false);
                                        if ($hoursDiff >= 0 && $hoursDiff <= 48) {
                                            $isDueWithin48Hours = true;
                                        }
                                    }
                                }

                                if (isset($tab['id']) && $tab['id'] == 'allocated' && $po->updated_at) {
                                    if ($po->updated_at->diffInHours($now) <= 48) {
                                        $allocatedWithin48Hours = true;
                                    }
                                }

                                if ($isOverdue) {
                                    $rowBgClass = 'tw-bg-rose-50/50';
                                } elseif ($isDueWithin48Hours) {
                                    $rowBgClass = 'tw-bg-orange-50/50';
                                } elseif (isset($tab['id']) && $tab['id'] == 'in_process') {
                                    $rowBgClass = 'tw-bg-green-50/50';
                                } elseif (isset($tab['id']) && $tab['id'] == 'allocated' && $allocatedWithin48Hours) {
                                    $rowBgClass = 'tw-bg-blue-50/50';
                                } elseif (isset($tab['id']) && $tab['id'] == 'created') {
                                    $rowBgClass = 'tw-bg-yellow-50/50';
                                }
                                @endphp
LOGIC;

foreach ($poFiles as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (!file_exists($fullPath)) continue;
    
    $content = file_get_contents($fullPath);
    
    $pattern = '/(@php\s+\$rowBgClass = \'\';.*?@endphp)/is';
    
    $content = preg_replace_callback($pattern, function($matches) use ($poLogic) {
        return $poLogic;
    }, $content);
    
    file_put_contents($fullPath, $content);
    echo "Fixed $file\n";
}

$soFiles = [
    'resources/views/super-admin/stock-order/index.blade.php',
    'resources/views/admin/stock-order/index.blade.php',
    'resources/views/craftsman/stock-order/index.blade.php',
];

$soLogic = <<<'LOGIC'
                    @php
                    $rowBgClass = '';
                    $allocatedWithin48Hours = false;
                    $now = \Carbon\Carbon::now();

                    if (isset($activeTab) && $activeTab == 'allocated-orders' && $order->updated_at) {
                        if ($order->updated_at->diffInHours($now) <= 48) {
                            $allocatedWithin48Hours = true;
                        }
                    }

                    if (isset($activeTab) && $activeTab == 'in-process-orders') {
                        $rowBgClass = 'tw-bg-green-50/50';
                    } elseif (isset($activeTab) && $activeTab == 'allocated-orders' && $allocatedWithin48Hours) {
                        $rowBgClass = 'tw-bg-blue-50/50';
                    } elseif (isset($activeTab) && $activeTab == 'new-orders') {
                        $rowBgClass = 'tw-bg-yellow-50/50';
                    }
                    @endphp
LOGIC;

foreach ($soFiles as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (!file_exists($fullPath)) continue;
    
    $content = file_get_contents($fullPath);
    
    // Clean up if already injected previously (just in case)
    $content = preg_replace('/@php\s+\$rowBgClass = \'\';\s+\$allocatedWithin48Hours = false;.*?@endphp\s*/is', '', $content);
    $content = str_replace(' {{ $rowBgClass }}', '', $content);
    
    // Inject logic before the tr tag
    $pattern = '/<tr class="(hover:tw-bg-slate-50\/50 tw-transition-colors tw-group)">/is';
    
    $content = preg_replace_callback($pattern, function($matches) use ($soLogic) {
        return $soLogic . "\n" . '                    <tr class="hover:tw-bg-slate-50/50 tw-transition-colors tw-group {{ $rowBgClass }}">';
    }, $content);
    
    file_put_contents($fullPath, $content);
    echo "Fixed $file\n";
}

echo "Done.\n";
