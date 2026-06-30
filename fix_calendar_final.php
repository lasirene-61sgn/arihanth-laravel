<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Fix generateCalendar to add alternating grey background for odd days (1, 3, 5, 7...)
$searchGen = <<<'JS'
                        cell.textContent = day;
                        cell.classList.add('tw-py-2', 'tw-cursor-pointer', 'tw-rounded-lg', 'hover:tw-bg-gray-100', 'dark:hover:tw-bg-slate-800', 'tw-transition-all');

                        // Highlight today
JS;

$replaceGen = <<<'JS'
                        cell.textContent = day;
                        cell.classList.add('tw-py-2', 'tw-cursor-pointer', 'tw-rounded-lg', 'hover:tw-bg-gray-200', 'dark:hover:tw-bg-slate-700', 'tw-transition-all');
                        
                        // Alternating grey background for odd days (1, 3, 5, 7...)
                        if (day % 2 !== 0) {
                            cell.style.backgroundColor = '#f3f4f6';
                            cell.classList.add('dark:tw-bg-slate-800/50');
                        }

                        // Highlight today
JS;
$content = str_replace($searchGen, $replaceGen, $content);

// 2. Fix showEventsForDate to handle the NEW aggregated JSON format from LoginController
$oldShowEvents = <<<'JS'
        // Function to show events for a selected date
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

$newShowEvents = <<<'JS'
        // Function to show events for a selected date
        function showEventsForDate(dateStr) {
            const meetingsSection = document.getElementById('eventsSection');

            if (window.calendarEvents && window.calendarEvents[dateStr]) {
                const data = window.calendarEvents[dateStr];
                let html = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">';
                html += '<h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6>';
                html += '</div>';

                html += '<div class="meetings-list tw-space-y-3">';
                
                // Holidays
                if (data.holidays && data.holidays.length > 0) {
                    data.holidays.forEach(holiday => {
                        html += '<div class="tw-flex tw-items-center tw-p-3 tw-rounded-xl tw-bg-red-50 dark:tw-bg-red-900/10 tw-border tw-border-red-100 dark:tw-border-red-900/30">';
                        html += '<div class="tw-w-8 tw-h-8 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-bg-red-100 tw-text-red-600 tw-mr-3">';
                        html += '<i class="bi bi-calendar-x"></i></div>';
                        html += '<div class="tw-font-bold tw-text-red-900 dark:tw-text-red-400 tw-text-sm">' + holiday + '</div>';
                        html += '</div>';
                    });
                }

                // Work Orders
                const wo = data.work_orders;
                if (wo && (wo.new > 0 || wo.allocated > 0 || wo.in_process > 0 || wo.completed > 0 || wo.overdue > 0 || wo.for_approval > 0 || wo.rejected > 0)) {
                    html += '<div class="tw-p-3 tw-rounded-xl tw-bg-blue-50/50 dark:tw-bg-blue-900/10 tw-border tw-border-blue-100 dark:tw-border-blue-900/30">';
                    html += '<div class="tw-flex tw-items-center tw-mb-2"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-blue-100 tw-text-blue-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-clipboard-check"></i></div><span class="tw-font-bold tw-text-blue-900 dark:tw-text-blue-400 tw-text-sm">Work Orders</span></div>';
                    html += '<div class="tw-grid tw-grid-cols-2 tw-gap-x-2 tw-gap-y-1 tw-text-[10px] tw-text-blue-800 dark:tw-text-blue-300">';
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
                const po = data.purchase_orders;
                if (po && (po.new > 0 || po.allocated > 0 || po.in_process > 0 || po.completed > 0 || po.overdue > 0 || po.for_approval > 0 || po.rejected > 0)) {
                    html += '<div class="tw-p-3 tw-rounded-xl tw-bg-indigo-50/50 dark:tw-bg-indigo-900/10 tw-border tw-border-indigo-100 dark:tw-border-indigo-900/30">';
                    html += '<div class="tw-flex tw-items-center tw-mb-2"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-indigo-100 tw-text-indigo-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-bag-check"></i></div><span class="tw-font-bold tw-text-indigo-900 dark:tw-text-indigo-400 tw-text-sm">Purchase Orders</span></div>';
                    html += '<div class="tw-grid tw-grid-cols-2 tw-gap-x-2 tw-gap-y-1 tw-text-[10px] tw-text-indigo-800 dark:tw-text-indigo-300">';
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
$content = str_replace($oldShowEvents, $newShowEvents, $content);

// 3. Fix calendar highlight logic to use data.types
$oldHighlight = <<<'JS'
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

$newHighlight = <<<'JS'
                        // Highlight days with events or holidays
                        if (window.calendarEvents && window.calendarEvents[dateStr]) {
                            const data = window.calendarEvents[dateStr];
                            const hasHoliday = data.holidays && data.holidays.length > 0;
                            const hasOrder = (data.work_orders && Object.values(data.work_orders).some(v => v > 0)) || 
                                            (data.purchase_orders && Object.values(data.purchase_orders).some(v => v > 0)) || 
                                            data.stock_orders > 0;

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
$content = str_replace($oldHighlight, $newHighlight, $content);

file_put_contents($file, $content);
echo "Calendar Fixed.\n";
