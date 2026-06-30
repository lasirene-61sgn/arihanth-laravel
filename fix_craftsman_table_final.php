<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Locate the Top Picks Craftsman Modal and its table
$startMarker = 'id="topPicksCraftsmanModal"';
$endMarker = 'id="leastPicksCraftsmanModal"';

$modalStartPos = strpos($content, $startMarker);
$modalEndPos = strpos($content, $endMarker);

if ($modalStartPos === false || $modalEndPos === false) {
    die("Modals not found.");
}

$modalContent = substr($content, $modalStartPos, $modalEndPos - $modalStartPos);

// 2. Define the correct table body and headers for the Top Picks Craftsman table
$correctBody = <<<'HTML'
                                <th class="tw-text-center tw-text-[14px] tw-font-bold">FOR APPROVAL (C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold tw-text-orange-500">OVERDUE(C/W)</th>
                                <th class="tw-text-center tw-text-[14px] tw-font-bold dark:tw-bg-indigo-800/30">PA TOTAL</th>
                                <th rowspan="2" class="tw-text-center tw-text-[14px] tw-font-bold tw-uppercase tw-tracking-wider tw-align-middle">{{ __('messages.total_weight') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPicksCraftsmanFull as $code => $stat)
                            <tr class="hover:tw-bg-blue-50/50 tw-transition-colors">
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ $stat['name'] }}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ $code }}</td>
                                
                                {{-- Work Orders --}}
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['new']['weight'] > 0 ? $stat['wo']['new']['count'] . ' - ' . number_format($stat['wo']['new']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['allocated']['weight'] > 0 ? $stat['wo']['allocated']['count'] . ' - ' . number_format($stat['wo']['allocated']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-blue-600 dark:tw-text-blue-400" style="color: #2563eb !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['in_process']['weight'] > 0 ? $stat['wo']['in_process']['count'] . ' - ' . number_format($stat['wo']['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['for_approval']['weight'] > 0 ? $stat['wo']['for_approval']['count'] . ' - ' . number_format($stat['wo']['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-green-600 dark:tw-text-green-400" style="color: #16a34a !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['wo']['completed']['weight'] > 0 ? $stat['wo']['completed']['count'] . ' - ' . number_format($stat['wo']['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold dark:tw-bg-blue-900/10 tw-text-blue-800 dark:tw-text-blue-300" style="color: #1e40af !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ number_format($stat['wa_total_weight'], 2) }}</td>
                                
                                {{-- Purchase Orders --}}
                                <td class="tw-text-center tw-text-[14px] tw-font-bold tw-text-slate-700 dark:tw-text-slate-300" style="color: #334155 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['allocated']['weight'] > 0 ? $stat['po']['allocated']['count'] . ' - ' . number_format($stat['po']['allocated']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-indigo-600 dark:tw-text-indigo-400" style="color: #4f46e5 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['in_process']['weight'] > 0 ? $stat['po']['in_process']['count'] . ' - ' . number_format($stat['po']['in_process']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-emerald-600 dark:tw-text-emerald-400" style="color: #059669 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['completed']['weight'] > 0 ? $stat['po']['completed']['count'] . ' - ' . number_format($stat['po']['completed']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-purple-600 dark:tw-text-purple-400" style="color: #9333ea !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['for_approval']['weight'] > 0 ? $stat['po']['for_approval']['count'] . ' - ' . number_format($stat['po']['for_approval']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-text-orange-600 dark:tw-text-orange-400 tw-font-bold" style="color: #ea580c !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{!! $stat['po']['overdue']['weight'] > 0 ? $stat['po']['overdue']['count'] . ' - ' . number_format($stat['po']['overdue']['weight'], 2) : '' !!}</td>
                                <td class="tw-text-center tw-text-[14px] tw-font-bold dark:tw-bg-indigo-900/10 tw-text-indigo-800 dark:tw-text-indigo-300" style="color: #3730a3 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ number_format($stat['po_total_weight'], 2) }}</td>

                                <td class="tw-text-center tw-font-black tw-text-blue-700 dark:tw-text-blue-400 tw-py-3 tw-text-xs" style="color: #1d4ed8 !important; background-color: {{ $loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;">{{ number_format($stat['total_weight'], 3) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="14" class="tw-text-center tw-py-4 tw-text-gray-500 dark:tw-text-gray-400">{{ __('messages.no_data_found') }}</td>
                            </tr>
HTML;

// 3. Find the broken part in modalContent and replace it
$searchPattern = '/<th class="tw-text-center tw-text-\[14px\] tw-font-bold">FOR APPROVAL \(C\/W\)<\/th>\s*@endforelse/s';

if (preg_match($searchPattern, $modalContent)) {
    $newModalContent = preg_replace($searchPattern, $correctBody, $modalContent);
    $content = str_replace($modalContent, $newModalContent, $content);
} else {
    // If preg_match fails, try a simpler approach
    die("Pattern match failed in modal content.");
}

file_put_contents($file, $content);
echo "Table Fixed Successfully.\n";
