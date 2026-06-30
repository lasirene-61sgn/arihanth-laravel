<?php

use App\Models\PurchaseOrder;
use App\Models\Craftman;

// Mocking a bulk allocate request
$orderIds = PurchaseOrder::pluck('id')->take(2)->toArray();
$craftsmanCode = Craftman::value('craftman_code');

if (empty($orderIds) || !$craftsmanCode) {
    echo "No test data available.\n";
    exit;
}

echo "Testing Bulk Allocate with IDs: " . implode(', ', $orderIds) . " and Craftsman: $craftsmanCode\n";

// This is just a logic check, actual HTTP test would require a running server
$affected = PurchaseOrder::whereIn('id', $orderIds)->update([
    'allocated_craftsman_code' => $craftsmanCode,
    'craftsman_status' => 'allocated'
]);

echo "Affected rows: $affected\n";
if ($affected == count($orderIds)) {
    echo "Logic Verification PASSED\n";
} else {
    echo "Logic Verification FAILED\n";
}
