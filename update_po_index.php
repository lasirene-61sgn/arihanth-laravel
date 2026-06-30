<?php
$files = [
    'admin' => "e:/public_html/resources/views/admin/purchase-order/index.blade.php",
    'super_admin' => "e:/public_html/resources/views/super-admin/purchase-order/index.blade.php"
];

foreach ($files as $guard => $path) {
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        continue;
    }
    
    $c = file_get_contents($path);

    // 1. Add completed_filter for Admin
    if ($guard === 'admin') {
        $adminFilterSearch = '@if($tab[\'id\'] == \'for_approval\' && $tab[\'data\']->count() > 0)
                        <button type="button" 
                                class="bulk-approve-btn hidden inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                            <i class="bi bi-check-all mr-1.5"></i> Bulk Approve
                        </button>
                    @endif';
        $adminFilterReplace = $adminFilterSearch . '

                    @if($tab[\'id\'] == \'completed\')
                    <form method="GET" action="{{ route(\'admin.purchase-order.index\') }}" class="inline-block" id="completed-filter-form">
                        <input type="hidden" name="tab" value="completed">
                        <select name="completed_filter" onchange="document.getElementById(\'completed-filter-form\').submit();" class="border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-magenta-500 font-bold text-slate-600 bg-white shadow-sm">
                            <option value="">All Time</option>
                            <option value="day" {{ request(\'completed_filter\') == \'day\' ? \'selected\' : \'\' }}>Today</option>
                            <option value="week" {{ request(\'completed_filter\') == \'week\' ? \'selected\' : \'\' }}>This Week</option>
                            <option value="month" {{ request(\'completed_filter\') == \'month\' ? \'selected\' : \'\' }}>This Month</option>
                        </select>
                    </form>
                    @endif';
        $c = str_replace(str_replace("\n", "\r\n", $adminFilterSearch), str_replace("\n", "\r\n", $adminFilterReplace), $c);
        $c = str_replace($adminFilterSearch, $adminFilterReplace, $c);

        // Bulk allocate modal due date for Admin
        $adminModalSearch = '<p class="mt-2 text-xs text-slate-500 italic">This will move selected orders to "Allocated" status.</p>
                    </div>
                </div>';
        $adminModalReplace = '<p class="mt-2 text-xs text-slate-500 italic">This will move selected orders to "Allocated" status.</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-tight">Craftsman Due Date</label>
                        <input type="date" name="craftsman_due_date" class="w-full border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-magenta-500">
                    </div>
                </div>';
        $c = str_replace(str_replace("\n", "\r\n", $adminModalSearch), str_replace("\n", "\r\n", $adminModalReplace), $c);
        $c = str_replace($adminModalSearch, $adminModalReplace, $c);
    } else {
        // SuperAdmin filter
        $saFilterSearch = '@if($tab[\'id\'] == \'for_approval\' && $tab[\'data\']->count() > 0)
                            <button type="button" class="btn btn-sm btn-success bulk-approve-btn ms-2" style="display:none;">
                                <i class="bi bi-check-all"></i> {{ __(\'messages.bulk_approve_selected\') }}
                            </button>
                            @endif';
        $saFilterReplace = $saFilterSearch . '

                            @if($tab[\'id\'] == \'completed\')
                            <form method="GET" action="{{ route(\'super-admin.purchase-order.index\') }}" class="d-inline-block ms-2" id="completed-filter-form">
                                <input type="hidden" name="tab" value="completed">
                                <select name="completed_filter" onchange="document.getElementById(\'completed-filter-form\').submit();" class="form-select form-select-sm">
                                    <option value="">All Time</option>
                                    <option value="day" {{ request(\'completed_filter\') == \'day\' ? \'selected\' : \'\' }}>Today</option>
                                    <option value="week" {{ request(\'completed_filter\') == \'week\' ? \'selected\' : \'\' }}>This Week</option>
                                    <option value="month" {{ request(\'completed_filter\') == \'month\' ? \'selected\' : \'\' }}>This Month</option>
                                </select>
                            </form>
                            @endif';
        $c = str_replace(str_replace("\n", "\r\n", $saFilterSearch), str_replace("\n", "\r\n", $saFilterReplace), $c);
        $c = str_replace($saFilterSearch, $saFilterReplace, $c);

        // Bulk allocate modal due date for SuperAdmin
        $saModalSearch = '</select>
                        <div class="form-text">This will move selected orders to "Allocated" status.</div>
                    </div>
                </div>';
        $saModalReplace = '</select>
                        <div class="form-text">This will move selected orders to "Allocated" status.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Craftsman Due Date</label>
                        <input type="date" name="craftsman_due_date" class="form-control">
                    </div>
                </div>';
        $c = str_replace(str_replace("\n", "\r\n", $saModalSearch), str_replace("\n", "\r\n", $saModalReplace), $c);
        $c = str_replace($saModalSearch, $saModalReplace, $c);
    }

    file_put_contents($path, $c);
    echo "Updated index blade for $guard\n";
}
