<?php
$files = [
    'd:/pulic_html/resources/views/craftsman/purchase-order/index.blade.php',
    'd:/pulic_html/resources/views/craftsman_staff/purchase-order/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    $content = file_get_contents($file);

    // 1. Add 'overdue' to match statements
    $content = str_replace(
        "'in-process' => \$inProcessOrders,",
        "'overdue' => \$overdueOrders,\n        'in-process' => \$inProcessOrders,",
        $content
    );
    
    $content = str_replace(
        "'in-process' => 'In Process',",
        "'overdue' => 'Overdue',\n        'in-process' => 'In Process',",
        $content
    );
    
    $content = str_replace(
        "'in-process' => 'yellow',",
        "'overdue' => 'orange',\n        'in-process' => 'yellow',",
        $content
    );

    // 2. Add the tab button
    // Determine the route prefix
    $prefix = strpos($file, 'craftsman_staff') !== false ? 'craftsman_staff' : 'craftsman';
    
    $tabHtml = <<<EOD
        <a href="{{ route('$prefix.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'overdue'])) }}"
            class="px-6 py-3 rounded-full font-bold text-sm transition-all duration-300 {{ request('tab') == 'overdue' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-100' }}">
            Overdue <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] {{ request('tab') == 'overdue' ? 'bg-emerald-500/50' : 'bg-emerald-100' }}">{{ \$overdueOrders->total() }}</span>
        </a>
EOD;

    // Find where to insert the tab HTML
    $tabSearch = "<a href=\"{{ route('$prefix.purchase-order.index', array_merge(request()->except('tab'), ['tab' => 'allocated'])) }}\"";
    
    $content = str_replace(
        $tabSearch,
        $tabHtml . "\n        " . $tabSearch,
        $content
    );

    file_put_contents($file, $content);
}
echo "Updated views\n";
