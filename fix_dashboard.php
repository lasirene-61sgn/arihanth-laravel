<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Fix Calendar JS logic
$jsOld = <<<'JS'
        function showEventsForDate(dateStr) {
            const meetingsSection = document.getElementById('eventsSection');

            if (window.calendarEvents && window.calendarEvents[dateStr] && window.calendarEvents[dateStr].length > 0) {
                let html = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">';
                html += '<h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6>';
                html += '</div>';

                html += '<div class="meetings-list">';
                window.calendarEvents[dateStr].forEach(event => {
                    let icon = 'bi-calendar-event';
                    let color = 'tw-text-primary';
                    let bg = 'tw-bg-gray-100';

                    if (event.type === 'work_order') {
                        icon = 'bi-clipboard-check';
                        color = 'tw-text-blue-600';
                        bg = 'tw-bg-blue-100';
                    } else if (event.type === 'purchase_order') {
                        icon = 'bi-bag-check';
                        color = 'tw-text-emerald-600';
                        bg = 'tw-bg-emerald-100';
                    } else if (event.type === 'stock_order') {
                        icon = 'bi-box-seam';
                        color = 'tw-text-amber-600';
                        bg = 'tw-bg-amber-100';
                    } else if (event.type === 'holiday') {
                        icon = 'bi-calendar-x';
                        color = 'tw-text-red-600';
                        bg = 'tw-bg-red-100';
                    }

                    html += '<div class="tw-flex tw-items-center tw-p-3 tw-mb-2 tw-rounded-xl tw-bg-white dark:tw-bg-slate-800/50 tw-border tw-border-gray-100 dark:tw-border-slate-700 tw-transition-all hover:tw-shadow-sm">';
                    html += '<div class="tw-w-10 tw-h-10 tw-rounded-lg tw-flex tw-items-center tw-justify-center ' + bg + ' ' + color + ' tw-mr-3">';
                    html += '<i class="bi ' + icon + ' tw-text-lg"></i>';
                    html += '</div>';
                    html += '<div class="tw-flex-grow">';
                    
                    if (event.type === 'holiday') {
                        html += '<div class="tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-sm">' + event.title + '</div>';
                    } else {
                        html += '<div class="tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-sm">' + event.type.replace('_', ' ').toUpperCase() + ': ' + event.number + '</div>';
                        html += '<div class="tw-text-[10px] tw-text-gray-500 dark:tw-text-gray-400">Status: ' + event.status + '</div>';
                        if (event.craftsman) {
                            html += '<div class="tw-text-[10px] tw-text-gray-500 dark:tw-text-gray-400">Craftsman: ' + event.craftsman + '</div>';
                        }
                    }
                    
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';

                meetingsSection.innerHTML = html;
            } else {
JS;

$jsNew = <<<'JS'
        function showEventsForDate(dateStr) {
            const meetingsSection = document.getElementById('eventsSection');

            if (window.calendarEvents && window.calendarEvents[dateStr]) {
                let data = window.calendarEvents[dateStr];
                
                let html = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">';
                html += '<h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6>';
                html += '</div>';
                html += '<div class="meetings-list tw-space-y-3">';

                // Holidays
                if (data.holidays && data.holidays.length > 0) {
                    data.holidays.forEach(holiday => {
                        html += '<div class="tw-flex tw-items-center tw-p-3 tw-rounded-xl tw-bg-red-50 dark:tw-bg-red-900/10 tw-border tw-border-red-100 dark:tw-border-red-900/30">';
                        html += '<div class="tw-w-8 tw-h-8 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-bg-red-100 tw-text-red-600 tw-mr-3">';
                        html += '<i class="bi bi-calendar-x tw-text-lg"></i></div>';
                        html += '<div class="tw-font-bold tw-text-red-900 dark:tw-text-red-400 tw-text-sm">' + holiday + '</div>';
                        html += '</div>';
                    });
                }

                // Work Orders
                let wo = data.work_orders;
                if (wo && (wo.new > 0 || wo.allocated > 0 || wo.in_process > 0 || wo.completed > 0 || wo.overdue > 0 || wo.for_approval > 0 || wo.rejected > 0)) {
                    html += '<div class="tw-p-3 tw-rounded-xl tw-bg-blue-50/50 dark:tw-bg-blue-900/10 tw-border tw-border-blue-100 dark:tw-border-blue-900/30">';
                    html += '<div class="tw-flex tw-items-center tw-mb-2"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-blue-100 tw-text-blue-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-clipboard-check"></i></div><span class="tw-font-bold tw-text-blue-900 dark:tw-text-blue-400 tw-text-sm">Work Orders</span></div>';
                    html += '<div class="tw-grid tw-grid-cols-2 tw-gap-x-2 tw-gap-y-1 tw-text-[11px] tw-text-blue-800 dark:tw-text-blue-300">';
                    if (wo.new > 0) html += '<div>New: <span class="tw-font-bold">' + wo.new + '</span></div>';
                    if (wo.allocated > 0) html += '<div>Allocated: <span class="tw-font-bold">' + wo.allocated + '</span></div>';
                    if (wo.in_process > 0) html += '<div>In Process: <span class="tw-font-bold">' + wo.in_process + '</span></div>';
                    if (wo.for_approval > 0) html += '<div>For Approval: <span class="tw-font-bold">' + wo.for_approval + '</span></div>';
                    if (wo.overdue > 0) html += '<div class="tw-text-red-600">Overdue: <span class="tw-font-bold">' + wo.overdue + '</span></div>';
                    if (wo.completed > 0) html += '<div class="tw-text-emerald-600">Completed: <span class="tw-font-bold">' + wo.completed + '</span></div>';
                    if (wo.rejected > 0) html += '<div class="tw-text-red-600">Rejected: <span class="tw-font-bold">' + wo.rejected + '</span></div>';
                    html += '</div></div>';
                }

                // Purchase Orders
                let po = data.purchase_orders;
                if (po && (po.new > 0 || po.allocated > 0 || po.in_process > 0 || po.completed > 0 || po.overdue > 0 || po.for_approval > 0 || po.rejected > 0)) {
                    html += '<div class="tw-p-3 tw-rounded-xl tw-bg-indigo-50/50 dark:tw-bg-indigo-900/10 tw-border tw-border-indigo-100 dark:tw-border-indigo-900/30">';
                    html += '<div class="tw-flex tw-items-center tw-mb-2"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-indigo-100 tw-text-indigo-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-bag-check"></i></div><span class="tw-font-bold tw-text-indigo-900 dark:tw-text-indigo-400 tw-text-sm">Purchase Orders</span></div>';
                    html += '<div class="tw-grid tw-grid-cols-2 tw-gap-x-2 tw-gap-y-1 tw-text-[11px] tw-text-indigo-800 dark:tw-text-indigo-300">';
                    if (po.new > 0) html += '<div>New: <span class="tw-font-bold">' + po.new + '</span></div>';
                    if (po.allocated > 0) html += '<div>Allocated: <span class="tw-font-bold">' + po.allocated + '</span></div>';
                    if (po.in_process > 0) html += '<div>In Process: <span class="tw-font-bold">' + po.in_process + '</span></div>';
                    if (po.for_approval > 0) html += '<div>For Approval: <span class="tw-font-bold">' + po.for_approval + '</span></div>';
                    if (po.overdue > 0) html += '<div class="tw-text-red-600">Overdue: <span class="tw-font-bold">' + po.overdue + '</span></div>';
                    if (po.completed > 0) html += '<div class="tw-text-emerald-600">Completed: <span class="tw-font-bold">' + po.completed + '</span></div>';
                    if (po.rejected > 0) html += '<div class="tw-text-red-600">Rejected: <span class="tw-font-bold">' + po.rejected + '</span></div>';
                    html += '</div></div>';
                }

                // Stock Orders
                if (data.stock_orders > 0) {
                    html += '<div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-rounded-xl tw-bg-amber-50/50 dark:tw-bg-amber-900/10 tw-border tw-border-amber-100 dark:tw-border-amber-900/30">';
                    html += '<div class="tw-flex tw-items-center"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-amber-100 tw-text-amber-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-box-seam"></i></div><span class="tw-font-bold tw-text-amber-900 dark:tw-text-amber-400 tw-text-sm">Live Stock Orders</span></div>';
                    html += '<div class="tw-text-amber-800 dark:tw-text-amber-300 tw-font-bold">' + data.stock_orders + '</div>';
                    html += '</div>';
                }

                html += '</div>';
                meetingsSection.innerHTML = html;
            } else {
JS;
$content = str_replace($jsOld, $jsNew, $content);

// Update event highlighting logic
$highlightOld = <<<'JS'
                        // Highlight days with events or holidays
                        if (window.calendarEvents && window.calendarEvents[dateStr]) {
                            let hasHoliday = window.calendarEvents[dateStr].some(e => e.type === 'holiday');
                            let hasOrder = window.calendarEvents[dateStr].some(e => e.type !== 'holiday');

                            if (hasHoliday) {
                                cell.classList.add('tw-text-red-500', 'tw-font-bold');
                            }
                            
                            if (hasOrder) {
                                let dot = document.createElement('div');
                                dot.classList.add('tw-w-1', 'tw-h-1', 'tw-bg-maroon', 'tw-rounded-full', 'tw-mx-auto', 'tw-mt-0.5');
                                if (cell.classList.contains('tw-bg-maroon')) {
                                    dot.classList.add('tw-bg-white');
                                }
                                cell.appendChild(dot);
                            }
                        }
JS;

$highlightNew = <<<'JS'
                        // Highlight days with events or holidays
                        if (window.calendarEvents && window.calendarEvents[dateStr]) {
                            let data = window.calendarEvents[dateStr];
                            let hasHoliday = data.holidays && data.holidays.length > 0;
                            let hasOrder = data.types && data.types.some(e => e.type !== 'holiday');

                            if (hasHoliday) {
                                cell.classList.add('tw-text-red-500', 'tw-font-bold');
                            }
                            
                            if (hasOrder) {
                                let dot = document.createElement('div');
                                dot.classList.add('tw-w-1', 'tw-h-1', 'tw-bg-maroon', 'tw-rounded-full', 'tw-mx-auto', 'tw-mt-0.5');
                                if (cell.classList.contains('tw-bg-maroon')) {
                                    dot.classList.add('tw-bg-white');
                                }
                                cell.appendChild(dot);
                            }
                        }
JS;
$content = str_replace($highlightOld, $highlightNew, $content);

// 2. Fix topPicksCraftsmanModal
$craftsmanOldRow = '@forelse($topPicksCraftsmanFull as $code => $stat)
                            <tr>';
$craftsmanNewRow = '@forelse($topPicksCraftsmanFull as $code => $stat)
                            <tr class="odd:tw-bg-white even:tw-bg-gray-50/50 dark:odd:tw-bg-slate-900 dark:even:tw-bg-slate-800/50 hover:tw-bg-blue-50/50 dark:hover:tw-bg-slate-700/50 tw-transition-colors">';
$content = str_replace($craftsmanOldRow, $craftsmanNewRow, $content);

// Replace the craftsman data cells to hide 0 weights. We will use preg_replace to target the `{{ $stat['wo']... }}` patterns.
$content = preg_replace('/\{\{\s*\$stat\[\'wo\'\]\[\'(new|allocated|in_process|for_approval|completed)\'\]\[\'count\'\]\s*\}\}\s*-\s*\{\{\s*number_format\(\$stat\[\'wo\'\]\[\'\1\'\]\[\'weight\'\],\s*2\)\s*\}\}/', '{!! $stat[\'wo\'][\'$1\'][\'weight\'] > 0 ? $stat[\'wo\'][\'$1\'][\'count\'] . \' - \' . number_format($stat[\'wo\'][\'$1\'][\'weight\'], 2) : \'\' !!}', $content);
$content = preg_replace('/\{\{\s*\$stat\[\'po\'\]\[\'(allocated|in_process|completed|for_approval|overdue)\'\]\[\'count\'\]\s*\}\}\s*-\s*\{\{\s*number_format\(\$stat\[\'po\'\]\[\'\1\'\]\[\'weight\'\],\s*2\)\s*\}\}/', '{!! $stat[\'po\'][\'$1\'][\'weight\'] > 0 ? $stat[\'po\'][\'$1\'][\'count\'] . \' - \' . number_format($stat[\'po\'][\'$1\'][\'weight\'], 2) : \'\' !!}', $content);

// 3. Append Top Picks Clients and Least Picks Clients Modals before the final script tag
$clientsModals = <<<'HTML'
<!-- Top Picks Clients Modal -->
<div class="modal fade" id="topPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-emerald-700 dark:tw-text-emerald-400">{{ __('messages.top_picks_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-emerald-50 dark:tw-bg-emerald-900/20 tw-text-emerald-700 dark:tw-text-emerald-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksClientsFull as $code => $stat)
                            <tr class="odd:tw-bg-white even:tw-bg-gray-50/50 dark:odd:tw-bg-slate-900 dark:even:tw-bg-slate-800/50 hover:tw-bg-emerald-50/50 dark:hover:tw-bg-slate-700/50 tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-emerald-700 dark:tw-text-emerald-400 tw-py-3 tw-text-[15px] tw-bg-slate-50/50" style="color: #047857 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Least Picks Clients Modal -->
<div class="modal fade" id="leastPicksClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl roboto-font">
        <div class="modal-content dark:tw-bg-slate-900 tw-border-0 tw-rounded-2xl tw-shadow-2xl">
            <div class="modal-header tw-border-0 tw-pb-0">
                <h5 class="modal-title tw-font-extrabold tw-text-gray-600 dark:tw-text-gray-400">{{ __('messages.least_pick_clients') }} (Top 15)</h5>
                <button type="button" class="btn-close dark:tw-invert" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tw-p-9">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle dark:tw-text-gray-300">
                        <thead class="tw-bg-gray-100 dark:tw-bg-slate-800">
                            <tr>
                                <th rowspan="2" class="tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">Client Name</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">BP Code</th>
                                <th colspan="6" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-bg-gray-200 dark:tw-bg-gray-700 tw-text-gray-700 dark:tw-text-gray-300">WORK ORDERS (WA)</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle tw-bg-gray-50">Total Orders</th>
                            </tr>
                            <tr class="tw-bg-gray-50 dark:tw-bg-slate-800/50">
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">NEW (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">PROCESS (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-red-500">REJECTED (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-emerald-600">COMPLETED (C/W)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leastPicksClientsFull as $code => $stat)
                            <tr class="odd:tw-bg-white even:tw-bg-gray-50/50 dark:odd:tw-bg-slate-900 dark:even:tw-bg-slate-800/50 hover:tw-bg-gray-100 dark:hover:tw-bg-slate-700/50 tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{{ $code }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important;">{!! $stat['new']['weight'] > 0 ? $stat['new']['count'] . ' - ' . number_format($stat['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important;">{!! $stat['in_process']['weight'] > 0 ? $stat['in_process']['count'] . ' - ' . number_format($stat['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important;">{!! $stat['for_approval']['weight'] > 0 ? $stat['for_approval']['count'] . ' - ' . number_format($stat['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important;">{!! $stat['overdue']['weight'] > 0 ? $stat['overdue']['count'] . ' - ' . number_format($stat['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-red-600 dark:tw-text-red-400 tw-font-bold" style="color: #dc2626 !important;">{!! $stat['rejected']['weight'] > 0 ? $stat['rejected']['count'] . ' - ' . number_format($stat['rejected']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important;">{!! $stat['completed']['weight'] > 0 ? $stat['completed']['count'] . ' - ' . number_format($stat['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-font-black tw-text-gray-700 dark:tw-text-gray-400 tw-py-3 tw-text-[15px] tw-bg-slate-50/50" style="color: #374151 !important;">{{ $stat['orders'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

$content = str_replace('<script>', $clientsModals . "\n\n<script>", $content);

file_put_contents($file, $content);
echo "Dashboard UI Updated Successfully.\n";
