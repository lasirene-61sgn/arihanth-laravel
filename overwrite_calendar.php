<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

$fullScript = <<<'JS'
<script>
    // Calendar JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const calendarBody = document.getElementById('calendarBody');
        const currentMonthElement = document.getElementById('currentMonth');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        window.calendarEvents = {};

        async function loadCalendarData(month, year) {
            try {
                const response = await fetch(`{{ route('super-admin.dashboard.calendar-data') }}?month=${month + 1}&year=${year}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    window.calendarEvents = await response.json();
                    generateCalendar(month, year);
                    
                    // Show events for today by default
                    let today = new Date();
                    let todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    showEventsForDate(todayStr);
                }
            } catch (error) {
                console.error('Error loading calendar data:', error);
                generateCalendar(month, year);
            }
        }

        function generateCalendar(month, year) {
            calendarBody.innerHTML = '';
            currentMonthElement.textContent = `${months[month]} ${year}`;

            let firstDay = new Date(year, month, 1);
            let startingDayOfWeek = firstDay.getDay();
            startingDayOfWeek = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1;
            let monthLength = new Date(year, month + 1, 0).getDate();
            let prevMonthLength = new Date(year, month, 0).getDate();

            let day = 1;
            let nextMonthDay = 1;

            for (let i = 0; i < 6; i++) {
                let row = document.createElement('tr');
                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement('td');

                    if (i === 0 && j < startingDayOfWeek) {
                        cell.textContent = prevMonthLength - startingDayOfWeek + j + 1;
                        cell.classList.add('tw-py-2', 'tw-text-gray-300', 'dark:tw-text-gray-600');
                    } else if (day > monthLength) {
                        cell.textContent = nextMonthDay;
                        cell.classList.add('tw-py-2', 'tw-text-gray-300', 'dark:tw-text-gray-600');
                        nextMonthDay++;
                    } else {
                        cell.textContent = day;
                        cell.classList.add('tw-py-2', 'tw-cursor-pointer', 'tw-rounded-lg', 'hover:tw-bg-gray-200', 'dark:hover:tw-bg-slate-700', 'tw-transition-all');
                        
                        // Alternating grey background for odd days (1, 3, 5, 7...)
                        if (day % 2 !== 0) {
                            cell.style.backgroundColor = '#f3f4f6';
                            cell.classList.add('dark:tw-bg-slate-800/50');
                        }

                        let today = new Date();
                        if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                            cell.classList.add('tw-bg-maroon', 'tw-text-white', 'tw-font-bold');
                            cell.style.borderRadius = '50%';
                            cell.style.width = '32px';
                            cell.style.height = '32px';
                            cell.style.lineHeight = '32px';
                            cell.style.padding = '0';
                            cell.style.display = 'inline-block';
                            cell.style.backgroundColor = '#800000'; // Maroon
                        }

                        let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        
                        if (window.calendarEvents && window.calendarEvents[dateStr]) {
                            const data = window.calendarEvents[dateStr];
                            const hasHoliday = data.holidays && data.holidays.length > 0;
                            const hasOrder = (data.work_orders && (data.work_orders.new > 0 || data.work_orders.allocated > 0 || data.work_orders.in_process > 0 || data.work_orders.completed > 0)) || 
                                            (data.purchase_orders && (data.purchase_orders.new > 0 || data.purchase_orders.allocated > 0 || data.purchase_orders.in_process > 0 || data.purchase_orders.completed > 0)) || 
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

                        cell.addEventListener('click', function() {
                            document.querySelectorAll('#calendarBody td').forEach(td => {
                                td.classList.remove('tw-bg-maroon/10', 'tw-text-maroon', 'tw-font-bold');
                            });
                            if (!this.classList.contains('tw-bg-maroon')) {
                                this.classList.add('tw-bg-maroon/10', 'tw-text-maroon', 'tw-font-bold', 'tw-rounded-full');
                            }
                            showEventsForDate(dateStr);
                        });

                        day++;
                    }
                    row.appendChild(cell);
                }
                calendarBody.appendChild(row);
                if (day > monthLength) break;
            }
        }

        prevMonthBtn.addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            loadCalendarData(currentMonth, currentYear);
        });

        nextMonthBtn.addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            loadCalendarData(currentMonth, currentYear);
        });

        function showEventsForDate(dateStr) {
            const meetingsSection = document.getElementById('eventsSection');
            if (window.calendarEvents && window.calendarEvents[dateStr]) {
                const data = window.calendarEvents[dateStr];
                let html = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">';
                html += '<h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6>';
                html += '</div>';
                html += '<div class="meetings-list tw-space-y-3">';
                
                if (data.holidays && data.holidays.length > 0) {
                    data.holidays.forEach(holiday => {
                        html += '<div class="tw-flex tw-items-center tw-p-3 tw-rounded-xl tw-bg-red-50 dark:tw-bg-red-900/10 tw-border tw-border-red-100 dark:tw-border-red-900/30">';
                        html += '<div class="tw-w-8 tw-h-8 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-bg-red-100 tw-text-red-600 tw-mr-3"><i class="bi bi-calendar-x"></i></div>';
                        html += '<div class="tw-font-bold tw-text-red-900 dark:tw-text-red-400 tw-text-sm">' + holiday + '</div></div>';
                    });
                }

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

                if (data.stock_orders > 0) {
                    html += '<div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-rounded-xl tw-bg-amber-50/50 dark:tw-bg-amber-900/10 tw-border tw-border-amber-100 dark:tw-border-amber-900/30">';
                    html += '<div class="tw-flex tw-items-center"><div class="tw-w-6 tw-h-6 tw-rounded tw-bg-amber-100 tw-text-amber-600 tw-flex tw-items-center tw-justify-center tw-mr-2"><i class="bi bi-box-seam"></i></div><span class="tw-font-bold tw-text-amber-900 dark:tw-text-amber-400 tw-text-sm">Stock Orders</span></div>';
                    html += '<div class="tw-text-amber-800 dark:tw-text-amber-300 tw-font-bold">' + data.stock_orders + '</div></div>';
                }

                html += '</div>';
                meetingsSection.innerHTML = html;
            } else {
                meetingsSection.innerHTML = '<div class="tw-flex tw-justify-between tw-items-center tw-mb-4"><h6 class="tw-mb-0 tw-font-bold tw-text-gray-900 dark:tw-text-white tw-text-[11px] tw-uppercase tw-tracking-wider">Events for ' + dateStr + '</h6></div><div class="tw-text-center tw-text-gray-400 tw-py-8"><div class="tw-w-12 tw-h-12 tw-rounded-full tw-bg-gray-100 dark:tw-bg-slate-800 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3"><i class="bi bi-calendar2-x tw-text-xl tw-opacity-50"></i></div><p class="tw-mb-0 tw-text-xs tw-font-medium">No events scheduled</p></div>';
            }
        }

        loadCalendarData(currentMonth, currentYear);
    });
</script>
JS;

// Match the entire script block starting from "Calendar JavaScript"
$content = preg_replace(
    '/<script>\s*\/\/\s*Calendar JavaScript\s*document\.addEventListener\(.*?<\/script>/s',
    $fullScript,
    $content
);

file_put_contents($file, $content);
echo "Calendar Script Overwritten.\n";
