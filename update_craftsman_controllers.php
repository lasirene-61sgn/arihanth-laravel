<?php
$files = [
    'd:/pulic_html/app/Http/Controllers/Craftsman/PurchaseOrderController.php',
    'd:/pulic_html/app/Http/Controllers/CraftsmanStaff/PurchaseOrderController.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // 1. Add overdueOrders query before the compact() call
    $overdueQueryStr = <<<'EOD'
        $now = \Carbon\Carbon::now();
        $overdueOrders = (clone $query)->where(function($q) use ($now) {
            $q->where(function($sub1) use ($now) {
                $sub1->whereNotNull('craftsman_due_date')
                     ->whereDate('craftsman_due_date', '<', $now->startOfDay());
            })->orWhere(function($sub2) use ($now) {
                $sub2->whereNotNull('craftsman_due_date')
                     ->whereDate('craftsman_due_date', '=', $now->startOfDay())
                     ->whereRaw('? >= 12', [$now->hour]);
            })->orWhere(function($sub3) use ($now) {
                $sub3->whereNull('craftsman_due_date')
                     ->whereNotNull('due_date')
                     ->whereDate('due_date', '<', $now->startOfDay());
            })->orWhere(function($sub4) use ($now) {
                $sub4->whereNull('craftsman_due_date')
                     ->whereNotNull('due_date')
                     ->whereDate('due_date', '=', $now->startOfDay())
                     ->whereRaw('? >= 12', [$now->hour]);
            });
        })->whereIn('craftsman_status', ['allocated', 'in_process'])
          ->paginate(self::PER_PAGE, ['*'], 'overdue_orders_page');

        $productCategories = ProductCategory::orderBy('name')->get();
EOD;

    $content = str_replace(
        "\$productCategories = ProductCategory::orderBy('name')->get();",
        $overdueQueryStr,
        $content
    );

    // 2. Add 'overdueOrders' to compact
    $content = str_replace(
        "compact(\n            'allocatedOrders',",
        "compact(\n            'overdueOrders',\n            'allocatedOrders',",
        $content
    );
    
    file_put_contents($file, $content);
}
echo "Updated controllers\n";
