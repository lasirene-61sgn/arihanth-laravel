<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// Strip out ALL topPicksClientsModal and leastPicksClientsModal blocks
$content = preg_replace('/<!-- (Top|Least) Picks Clients Modal -->.*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s', '', $content);

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

$content = preg_replace('/<script>\s*\/\/ Calendar JavaScript/', $clientsModals . "\n\n<script>\n    // Calendar JavaScript", $content, 1);

file_put_contents($file, $content);
echo "Cleaned up Modals.\n";
