<?php
$path = "d:/pulic_html/app/Http/Controllers/API/Common/WorkOrderController.php";
$content = file_get_contents($path);

// Insert logging before the foreach loop
$replacement = "        \Illuminate\Support\Facades\Log::info('Bulk Complete Request:', ['ids' => \$request->ids, 'is_admin' => \$this->isAdmin(\$user), 'role' => \$user->role ?? 'none']);
        \$count = 0;
        foreach (\$request->ids as \$id) {";
        
$content = str_replace("        \$count = 0;\n        foreach (\$request->ids as \$id) {", $replacement, $content);
file_put_contents($path, $content);
echo "Added logs.";
