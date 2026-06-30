<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIRECT VERIFICATION OF REJECTED ITEMS ===\n\n";

try {
    // Get all purchase orders with rejected items
    $ordersWithRejectedItems = \App\Models\PurchaseOrder::whereNotNull('rejected_items')
        ->where('rejected_items', '!=', '[]')
        ->get();
    
    echo "Orders with rejected items: " . $ordersWithRejectedItems->count() . "\n\n";
    
    foreach ($ordersWithRejectedItems as $order) {
        echo "📋 " . $order->purchase_order_code . "\n";
        echo "   Allocated Craftsman: " . ($order->allocated_craftsman_code ?? 'None') . "\n";
        echo "   Craftsman Status: " . ($order->craftsman_status ?? 'None') . "\n";
        echo "   Main Status: " . $order->status . "\n";
        echo "   Rejected Items Count: " . count($order->rejected_items) . "\n";
        echo "   Current Items Count: " . count($order->items) . "\n";
        
        // Check if this would appear in craftsman rejected tab
        if ($order->allocated_craftsman_code && $order->craftsman_status === 'rejected') {
            echo "   ✅ Would appear in craftsman rejected tab\n";
        } else {
            echo "   ❌ Would NOT appear in craftsman rejected tab\n";
            echo "      (Craftsman Status: " . ($order->craftsman_status ?? 'null') . ")\n";
        }
        echo "---\n";
    }
    
    echo "\n=== CONCLUSION ===\n";
    echo "The rejected tab for craftsmen shows orders where:\n";
    echo "1. allocated_craftsman_code = craftsman's code\n";
    echo "2. craftsman_status = 'rejected'\n\n";
    echo "Currently, orders with rejected items but craftsman_status = 'completed' won't appear in the rejected tab.\n";
    echo "This is the correct behavior - completed orders (even with rejected items) belong in the completed tab.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";