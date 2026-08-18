<?php
function processFile($file) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);

    if (strpos($content, "'id' => 'craftsman-staff'") !== false) {
        return; // already processed
    }

    $tab_insert = "                        ['id' => 'craftsman-staff', 'label' => 'Craftsman Staff', 'icon' => 'bi-people', 'count' => \$allCraftsmanStaff->count()],\n";
    $content = str_replace("['id' => 'craftsmen'", $tab_insert . "                        ['id' => 'craftsmen'", $content);

    $stats_insert = "                ['label' => 'Total Staff', 'val' => \$allCraftsmanStaff->count(), 'frozen' => \$frozenCraftsmanStaff->count(), 'icon' => 'bi-people', 'color' => 'orange'],\n";
    $content = str_replace("['label' => 'Total Craftsmen'", $stats_insert . "                ['label' => 'Total Craftsmen'", $content);

    $count_insert = " + \$frozenCraftsmanStaff->count()";
    $content = preg_replace('/(\$frozenCraftsmen->count\(\))/', '$1' . $count_insert, $content);

    $html = <<<EOT
            <!-- Craftsman Staff Tab -->
            <div class="tab-pane fade" id="craftsman-staff" role="tabpanel">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-border-collapse">
                        <thead>
                            <tr class="tw-bg-gray-50/50">
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Code</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Name</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Contact</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100">Status</th>
                                <th class="tw-px-6 tw-py-4 tw-text-xs tw-font-bold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-border-b tw-border-gray-100 tw-text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse(\$allCraftsmanStaff as \$staff)
                                <tr class="hover:tw-bg-gray-50/50 tw-transition-colors">
                                    <td class="tw-px-6 tw-py-4">
                                        <span class="tw-font-bold tw-text-gray-900 tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded tw-text-xs">{{ \$staff->staff_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ \$staff->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        <div class="tw-text-sm tw-text-gray-900">{{ \$staff->email ?? 'N/A' }}</div>
                                        <div class="tw-text-xs tw-text-gray-500">{{ \$staff->mobile ?? 'N/A' }}</div>
                                    </td>
                                    <td class="tw-px-6 tw-py-4">
                                        @if(!\$staff->is_active)
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-800">
                                                <i class="bi bi-snow tw-mr-1"></i> Frozen
                                            </span>
                                        @else
                                            <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                                <i class="bi bi-check-circle tw-mr-1"></i> {{ __('messages.active') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="tw-px-6 tw-py-4 tw-text-right">
                                        @if(!\$staff->is_active)
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-green-600 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-green-700 tw-transition-colors unfreeze-btn" 
                                                    data-model-type="craftsman_staff" 
                                                    data-model-id="{{ \$staff->id }}">
                                                <i class="bi bi-unlock"></i> {{ __('messages.unfreeze') }}
                                            </button>
                                        @else
                                            <button class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-bg-amber-500 tw-text-white tw-text-xs tw-font-medium tw-rounded-md hover:tw-bg-amber-600 tw-transition-colors freeze-btn" 
                                                    data-model-type="craftsman_staff" 
                                                    data-model-id="{{ \$staff->id }}">
                                                <i class="bi bi-lock"></i> {{ __('messages.freeze') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-text-sm">No craftsman staff in system</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

EOT;
    $content = str_replace('<!-- Admins Tab -->', $html . '            <!-- Admins Tab -->', $content);
    file_put_contents($file, $content);
}

processFile('d:/pulic_html/resources/views/super-admin/freeze-account/index.blade.php');
processFile('d:/pulic_html/resources/views/admin/freeze-account/index.blade.php');
