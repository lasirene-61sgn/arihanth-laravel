<?php
$admin_path = "e:/public_html/resources/views/admin/work-order/index.blade.php";
$c = file_get_contents($admin_path);

$admin_pattern = '/(<input type="hidden" name="tab" value="completed-orders">[\s\S]*?)<select name="per_page"/';
$admin_replacement = '$1<select name="completed_filter" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="">All Time</option>
                                    <option value="day" {{ request(\'completed_filter\') == \'day\' ? \'selected\' : \'\' }}>Today</option>
                                    <option value="week" {{ request(\'completed_filter\') == \'week\' ? \'selected\' : \'\' }}>This Week</option>
                                    <option value="month" {{ request(\'completed_filter\') == \'month\' ? \'selected\' : \'\' }}>This Month</option>
                                </select>
                                <select name="per_page"';
$c = preg_replace($admin_pattern, $admin_replacement, $c);
file_put_contents($admin_path, $c);


$sa_path = "e:/public_html/resources/views/super-admin/work-order/index.blade.php";
$c2 = file_get_contents($sa_path);
// In super-admin, there are two forms with value="completed-orders", the first is search, the second is for per_page
// The second one starts right after $completedOrders->total()
$sa_pattern = '/(<input type="hidden" name="tab" value="completed-orders">\s*<input type="hidden" name="search" value="\{\{\s*request\(\'search\'\)\s*\}\}">\s*)<select name="per_page"/';
$sa_replacement = '$1<select name="completed_filter" onchange="this.form.submit()" class="tw-text-xs tw-border-gray-200 tw-rounded-lg tw-bg-white focus:tw-ring-emerald-500 tw-py-1">
                                <option value="">All Time</option>
                                <option value="day" {{ request(\'completed_filter\') == \'day\' ? \'selected\' : \'\' }}>Today</option>
                                <option value="week" {{ request(\'completed_filter\') == \'week\' ? \'selected\' : \'\' }}>This Week</option>
                                <option value="month" {{ request(\'completed_filter\') == \'month\' ? \'selected\' : \'\' }}>This Month</option>
                            </select>
                            <select name="per_page"';
$c2 = preg_replace($sa_pattern, $sa_replacement, $c2);
file_put_contents($sa_path, $c2);

echo "Fixed index blades for completed filter.\n";
