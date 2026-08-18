<?php

$paths = [
    'resources/views/super-admin/work-order/index.blade.php',
    'resources/views/super-admin/purchase-order/index.blade.php',
    'resources/views/admin/work-order/index.blade.php',
    'resources/views/admin/purchase-order/index.blade.php',
    'resources/views/craftsman/work-order/index.blade.php',
    'resources/views/craftsman/purchase-order/index.blade.php',
    'resources/views/craftsman_staff/work-order/index.blade.php',
    'resources/views/craftsman_staff/purchase-order/index.blade.php',
];

$block1 = <<<EOF
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-emerald-100 tw-text-emerald-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[10px]">
                                                {{ \$order->craftsman ? substr(\$order->craftsman->name, 0, 1) : '?' }}
                                            </div>
                                            <div class="tw-text-xs tw-text-gray-700">{{ \$order->craftsman ? \$order->craftsman->name : 'N/A' }}</div>
                                        </div>
EOF;

$block1_replacement = <<<EOF
                                        <div class="tw-flex tw-flex-col tw-gap-2">
                                            <div class="tw-flex tw-items-center tw-gap-2">
                                                <div class="tw-w-7 tw-h-7 tw-rounded-full tw-bg-emerald-100 tw-text-emerald-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[10px]" title="Craftsman">
                                                    {{ \$order->craftsman ? substr(\$order->craftsman->name, 0, 1) : '?' }}
                                                </div>
                                                <div class="tw-text-xs tw-text-gray-700">{{ \$order->craftsman ? \$order->craftsman->name : 'N/A' }}</div>
                                            </div>
                                            @if(\$order->staff_completed_at && \$order->craftsmanStaff)
                                            <div class="tw-flex tw-items-center tw-gap-2 tw-mt-1">
                                                <div class="tw-w-6 tw-h-6 tw-rounded-full tw-bg-purple-100 tw-text-purple-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[9px]" title="Completed by Staff">
                                                    {{ substr(\$order->craftsmanStaff->name, 0, 1) }}
                                                </div>
                                                <div class="tw-text-[11px] tw-text-gray-600"><span class="tw-text-purple-600 tw-font-semibold">Staff (Completed):</span> {{ \$order->craftsmanStaff->name }}</div>
                                            </div>
                                            @elseif(\$order->staff_accepted_at && \$order->acceptedByStaff)
                                            <div class="tw-flex tw-items-center tw-gap-2 tw-mt-1">
                                                <div class="tw-w-6 tw-h-6 tw-rounded-full tw-bg-blue-100 tw-text-blue-700 tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[9px]" title="Accepted by Staff">
                                                    {{ substr(\$order->acceptedByStaff->name, 0, 1) }}
                                                </div>
                                                <div class="tw-text-[11px] tw-text-gray-600"><span class="tw-text-blue-600 tw-font-semibold">Staff (Accepted):</span> {{ \$order->acceptedByStaff->name }}</div>
                                            </div>
                                            @endif
                                        </div>
EOF;

foreach ($paths as $path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $replaced1 = str_replace($block1, $block1_replacement, $content, $count1);
        
        if ($count1 > 0) {
            file_put_contents($path, $replaced1);
            echo "Updated \$path: replaced \$count1 occurrences of generic craftsman.\n";
        } else {
            echo "No changes needed for \$path.\n";
        }
    } else {
        echo "File not found: \$path\n";
    }
}
