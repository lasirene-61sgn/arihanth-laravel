<?php

$files = [
    'resources/views/super-admin/work-order/index.blade.php',
    'resources/views/admin/work-order/index.blade.php',
    'resources/views/craftsman/work-order/index.blade.php',
];

$logicToInject = <<<'LOGIC'
                                $displayImage = $order->product_image ?? null;
                                $isPdf = $displayImage && \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                if (!$displayImage && !empty($order->product) && $order->product->images && $order->product->images->count() > 0) {
                                    $displayImage = $order->product->images->first()->path;
                                    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($displayImage), '.pdf');
                                }

                                $statusClass = match($order->status ?? '') {
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'allocated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'in_process' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                    'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
LOGIC;

foreach ($files as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (!file_exists($fullPath)) continue;
    
    $content = file_get_contents($fullPath);
    
    if (strpos($content, '$displayImage = $order->product_image') === false) {
        $content = preg_replace('/(\$rowStyle = \'\';)/', "$1\n" . $logicToInject . "\n", $content);
        file_put_contents($fullPath, $content);
        echo "Fixed $file\n";
    } else {
        echo "Already fixed $file\n";
    }
}
echo "Done.\n";
