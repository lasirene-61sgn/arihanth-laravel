<?php
$c = file_get_contents("e:/public_html/resources/views/admin/work-order/index.blade.php");

$wrong_new = '<div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Filter:</span>
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="completed-orders">
                                @if(request(\'search\')) <input type="hidden" name="search" value="{{ request(\'search\') }}"> @endif
                                <select name="completed_filter" onchange="this.form.submit()" class="text-xs bg-white border-slate-200 rounded py-1 px-2 focus:ring-pink-500">
                                    <option value="">All Time</option>
                                    <option value="day" {{ request(\'completed_filter\') == \'day\' ? \'selected\' : \'\' }}>Today</option>
                                    <option value="week" {{ request(\'completed_filter\') == \'week\' ? \'selected\' : \'\' }}>This Week</option>
                                    <option value="month" {{ request(\'completed_filter\') == \'month\' ? \'selected\' : \'\' }}>This Month</option>
                                </select>
                            </form>
                        </div>
                        <div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>';

$original = '<div class="h-6 w-px bg-slate-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500">Show:</span>';

$wrong_new_crlf = str_replace("\n", "\r\n", $wrong_new);
$original_crlf = str_replace("\n", "\r\n", $original);

$c = str_replace($wrong_new_crlf, $original_crlf, $c);
$c = str_replace($wrong_new, $original, $c); // LF fallback

file_put_contents("e:/public_html/resources/views/admin/work-order/index.blade.php", $c);
echo "Reverted admin index blade\n";
