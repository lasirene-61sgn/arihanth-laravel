<?php
$po_file = 'd:/pulic_html/resources/views/admin/purchase-order/index.blade.php';
$po_content = file_get_contents($po_file);
$po_content = str_replace(
    "[\$po, 'return_url' => url()->full()]",
    "[\$po, 'return_url' => route('admin.purchase-order.index', array_merge(request()->query(), ['tab' => \$tab['id']]))]",
    $po_content
);
file_put_contents($po_file, $po_content);
echo "Updated purchase order index return_url logic\n";
