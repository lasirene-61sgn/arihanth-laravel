<?php
$file = 'E:/arihanth/app/Http/Controllers/SuperAdmin/LoginController.php';
$content = file_get_contents($file);

$startMarker = '    public function getCalendarData(Request $request)';
$endMarker = "    }\n}\n";

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker, $startPos);

if ($startPos !== false && $endPos !== false) {
    $before = substr($content, 0, $startPos);
    
    $newFunction = <<<'EOD'
    public function getCalendarData(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Fetch Orders
        $workOrders = \App\Models\WorkOrder::whereBetween('created_at', [$startDate, $endDate])->get();
        $purchaseOrders = \App\Models\PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])->get();
        $stockOrders = \App\Models\StockOrder::whereBetween('created_at', [$startDate, $endDate])->get();

        $events = [];
        
        // Initialize days
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
            $date = $tempDate->format('Y-m-d');
            $events[$date] = [
                'work_orders' => ['new' => 0, 'allocated' => 0, 'in_process' => 0, 'completed' => 0, 'overdue' => 0, 'for_approval' => 0, 'rejected' => 0],
                'purchase_orders' => ['new' => 0, 'allocated' => 0, 'in_process' => 0, 'completed' => 0, 'overdue' => 0, 'for_approval' => 0, 'rejected' => 0],
                'stock_orders' => 0,
                'holidays' => []
            ];
            
            if ($tempDate->isSunday()) {
                $events[$date]['holidays'][] = 'Sunday Leave';
            }
            $tempDate->addDay();
        }

        $holidays = [
            '2026-01-26' => 'Republic Day',
            '2026-08-15' => 'Independence Day',
            '2026-10-02' => 'Gandhi Jayanti',
        ];

        foreach ($holidays as $date => $title) {
            if (\Carbon\Carbon::parse($date)->between($startDate, $endDate)) {
                if (isset($events[$date])) {
                    $events[$date]['holidays'][] = $title;
                }
            }
        }

        // Aggregate Work Orders
        foreach ($workOrders as $wo) {
            $date = $wo->created_at->format('Y-m-d');
            if (isset($events[$date])) {
                if (!$wo->craftsman_status || $wo->craftsman_status == 'new') $events[$date]['work_orders']['new']++;
                elseif ($wo->craftsman_status == 'allocated') $events[$date]['work_orders']['allocated']++;
                elseif ($wo->craftsman_status == 'in_process') $events[$date]['work_orders']['in_process']++;
                elseif ($wo->craftsman_status == 'rejected') $events[$date]['work_orders']['rejected']++;
                
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') $events[$date]['work_orders']['completed']++;
                if ($wo->status == 'for_approval') $events[$date]['work_orders']['for_approval']++;
                if ($wo->isOverdue()) $events[$date]['work_orders']['overdue']++;
            }
        }

        // Aggregate Purchase Orders
        foreach ($purchaseOrders as $po) {
            $date = $po->created_at->format('Y-m-d');
            if (isset($events[$date])) {
                if (!$po->craftsman_status || $po->craftsman_status == 'new') $events[$date]['purchase_orders']['new']++;
                elseif ($po->craftsman_status == 'allocated') $events[$date]['purchase_orders']['allocated']++;
                elseif ($po->craftsman_status == 'in_process') $events[$date]['purchase_orders']['in_process']++;
                elseif ($po->craftsman_status == 'rejected') $events[$date]['purchase_orders']['rejected']++;
                
                if ($po->craftsman_status == 'completed' || $po->status == 'completed') $events[$date]['purchase_orders']['completed']++;
                if ($po->status == 'for_approval') $events[$date]['purchase_orders']['for_approval']++;
                if ($po->due_date && $po->due_date < now() && $po->status != 'completed') $events[$date]['purchase_orders']['overdue']++;
            }
        }

        // Aggregate Stock Orders
        foreach ($stockOrders as $so) {
            $date = $so->created_at->format('Y-m-d');
            if (isset($events[$date])) {
                $events[$date]['stock_orders']++;
            }
        }
        
        // Remove empty days to save payload size
        $finalEvents = [];
        foreach ($events as $date => $data) {
            $hasData = false;
            foreach ($data['work_orders'] as $v) if($v > 0) $hasData = true;
            foreach ($data['purchase_orders'] as $v) if($v > 0) $hasData = true;
            if ($data['stock_orders'] > 0) $hasData = true;
            if (!empty($data['holidays'])) $hasData = true;
            
            if ($hasData) {
                // Determine event types for the frontend dots
                $types = [];
                if (!empty($data['holidays'])) $types[] = ['type' => 'holiday'];
                
                $hasWorkOrder = false;
                foreach ($data['work_orders'] as $v) if($v > 0) $hasWorkOrder = true;
                if ($hasWorkOrder) $types[] = ['type' => 'work_order'];
                
                $hasPurchaseOrder = false;
                foreach ($data['purchase_orders'] as $v) if($v > 0) $hasPurchaseOrder = true;
                if ($hasPurchaseOrder) $types[] = ['type' => 'purchase_order'];
                
                if ($data['stock_orders'] > 0) $types[] = ['type' => 'stock_order'];
                
                $data['types'] = $types; // Used by frontend calendar rendering to place dots
                $finalEvents[$date] = $data;
            }
        }

        return response()->json($finalEvents);
EOD;

    $after = substr($content, $endPos);
    $content = $before . $newFunction . "\n" . $after;
    file_put_contents($file, $content);
    echo "Controller Updated Successfully.\n";
} else {
    echo "Could not find markers.\n";
}
