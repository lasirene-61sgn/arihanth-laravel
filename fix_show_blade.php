<?php
$c = file_get_contents("e:/public_html/resources/views/admin/work-order/show.blade.php");
$old = '<div class="col-md-6 mb-3"><label class="text-muted small d-block">Customer Name</label>';
$new = '<div class="col-md-6 mb-3"><label class="text-muted small d-block">Completed Date</label><div class="fw-bold">@if(strtolower($workOrder->status) === \'completed\') <span class="text-success">{{ $workOrder->updated_at ? $workOrder->updated_at->format(\'d M, Y h:i A\') : \'N/A\' }}</span> @else <span class="text-muted">Not Completed</span> @endif</div></div><div class="col-md-6 mb-3"><label class="text-muted small d-block">Customer Name</label>';
$c = str_replace($old, $new, $c);
file_put_contents("e:/public_html/resources/views/admin/work-order/show.blade.php", $c);

$c2 = file_get_contents("e:/public_html/resources/views/super-admin/work-order/show.blade.php");
$c2 = str_replace($old, $new, $c2);
file_put_contents("e:/public_html/resources/views/super-admin/work-order/show.blade.php", $c2);
echo "Added Completed Date to show views\n";
