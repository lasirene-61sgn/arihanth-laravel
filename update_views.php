<?php

// 1. Update Admin index.blade.php
$adminFile = 'd:/pulic_html/resources/views/admin/purchase-order/index.blade.php';
$adminContent = file_get_contents($adminFile);

$categoryFields = <<<EOT

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Category</label>
                        <select name="category_filter" class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                            <option value="">All Categories</option>
                            @foreach(\$categories as \$category)
                                <option value="{{ \$category->id }}" {{ request('category_filter') == \$category->id ? 'selected' : '' }}>{{ \$category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sub Category Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Sub Category</label>
                        <select name="sub_category_filter" class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-magenta-500 focus:border-magenta-500 text-sm transition-all">
                            <option value="">All Sub Categories</option>
                            @foreach(\$subCategories as \$subCategory)
                                <option value="{{ \$subCategory->id }}" {{ request('sub_category_filter') == \$subCategory->id ? 'selected' : '' }}>{{ \$subCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
EOT;

// Insert category and sub category filters after Design Code Filter
if (strpos($adminContent, '<!-- Category Filter -->') === false) {
    $insertPos = strpos($adminContent, '<!-- Craftsman Filter -->');
    if ($insertPos !== false) {
        $adminContent = substr_replace($adminContent, $categoryFields . "\n\n                    ", $insertPos, 0);
    }
}

// Add overdue option to Status Filter
if (strpos($adminContent, '<option value="overdue"') === false) {
    $adminContent = str_replace('<option value="rejected" {{ request(\'filter_status\') == \'rejected\' ? \'selected\' : \'\' }}>Rejected</option>', '<option value="rejected" {{ request(\'filter_status\') == \'rejected\' ? \'selected\' : \'\' }}>Rejected</option>' . "\n                            <option value=\"overdue\" {{ request('filter_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>", $adminContent);
}

file_put_contents($adminFile, $adminContent);
echo "Admin view updated.\n";


// 2. Update SuperAdmin index.blade.php
$superAdminFile = 'd:/pulic_html/resources/views/super-admin/purchase-order/index.blade.php';
$superAdminContent = file_get_contents($superAdminFile);

$superAdminFilterDesign = <<<EOT
                            <div id="filterSection" class="bg-white tw-border tw-border-slate-200 tw-rounded-xl tw-shadow-sm tw-mb-6 tw-overflow-hidden" style="display: {{ request()->anyFilled(['search', 'filter_po_code', 'filter_craftsman', 'filter_design_code', 'filter_status', 'category_filter', 'sub_category_filter', 'filter_date_from', 'filter_date_to']) ? 'block' : 'none' }};">
                                <div class="tw-p-6">
                                    <h3 class="tw-text-sm tw-font-bold tw-text-slate-800 tw-uppercase tw-tracking-wider tw-mb-4">Advanced Filters</h3>
                                    <form action="{{ route('super-admin.purchase-order.index') }}" method="GET">
                                        <input type="hidden" name="tab" value="{{ request('tab', 'created') }}">
                                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-6">
                                            <!-- Search -->
                                            <div class="tw-col-span-1 md:tw-col-span-2 lg:tw-col-span-1">
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Search Orders</label>
                                                <div class="tw-relative">
                                                    <span class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-text-slate-400">
                                                        <i class="bi bi-search"></i>
                                                    </span>
                                                    <input type="text" name="search" value="{{ request('search') }}" 
                                                           class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all" 
                                                           placeholder="PO Code / Items...">
                                                </div>
                                            </div>

                                            <!-- PO Code Filter -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">PO Code</label>
                                                <input type="text" name="filter_po_code" value="{{ request('filter_po_code') }}" 
                                                       class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                            </div>

                                            <!-- Design Code Filter -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Design Code</label>
                                                <input type="text" name="design_code_filter" value="{{ request('design_code_filter') }}" 
                                                       class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all"
                                                       placeholder="e.g. DS0001">
                                            </div>

                                            <!-- Category Filter -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Category</label>
                                                <select name="category_filter" class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                                    <option value="">All Categories</option>
                                                    @foreach(\$categories as \$category)
                                                        <option value="{{ \$category->id }}" {{ request('category_filter') == \$category->id ? 'selected' : '' }}>{{ \$category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Sub Category Filter -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Sub Category</label>
                                                <select name="sub_category_filter" class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                                    <option value="">All Sub Categories</option>
                                                    @foreach(\$subCategories as \$subCategory)
                                                        <option value="{{ \$subCategory->id }}" {{ request('sub_category_filter') == \$subCategory->id ? 'selected' : '' }}>{{ \$subCategory->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Craftsman Filter -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Craftsman Code</label>
                                                <select name="filter_craftsman" class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                                    <option value="">All Craftsmen</option>
                                                    @foreach(\$craftsmen as \$c)
                                                        <option value="{{ \$c->craftman_code }}" {{ request('filter_craftsman') == \$c->craftman_code ? 'selected' : '' }}>{{ \$c->craftman_code }} - {{ \$c->business_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Status Filter -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">Status</label>
                                                <select name="filter_status" 
                                                        class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                                    <option value="">All Statuses</option>
                                                    <option value="created" {{ request('filter_status') == 'created' ? 'selected' : '' }}>Created</option>
                                                    <option value="allocated" {{ request('filter_status') == 'allocated' ? 'selected' : '' }}>Allocated</option>
                                                    <option value="in_process" {{ request('filter_status') == 'in_process' ? 'selected' : '' }}>In Process</option>
                                                    <option value="for_approval" {{ request('filter_status') == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                                                    <option value="completed" {{ request('filter_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="rejected" {{ request('filter_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    <option value="overdue" {{ request('filter_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                </select>
                                            </div>

                                            <!-- Date From -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">From Date</label>
                                                <input type="date" name="filter_date_from" value="{{ request('filter_date_from') }}" 
                                                       class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                            </div>

                                            <!-- Date To -->
                                            <div>
                                                <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-500 tw-uppercase tw-mb-2">To Date</label>
                                                <input type="date" name="filter_date_to" value="{{ request('filter_date_to') }}" 
                                                       class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg tw-bg-slate-50 focus:tw-bg-white focus:tw-ring-2 focus:tw-ring-magenta-500 focus:tw-border-magenta-500 tw-text-sm tw-transition-all">
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="tw-col-span-1 lg:tw-col-span-2 tw-flex tw-items-end tw-gap-3 tw-pt-2">
                                                <button type="submit" class="tw-flex-1 tw-bg-maroon hover:tw-bg-maroon-dark tw-text-white tw-font-bold tw-py-2 tw-px-4 tw-rounded-lg tw-transition-colors tw-text-sm tw-shadow-sm">
                                                    Apply Filters
                                                </button>
                                                <a href="{{ route('super-admin.purchase-order.index', ['tab' => request('tab', 'created')]) }}" 
                                                   class="tw-flex-1 tw-bg-slate-100 hover:tw-bg-slate-200 tw-text-slate-700 tw-font-bold tw-py-2 tw-px-4 tw-rounded-lg tw-transition-colors tw-text-sm tw-text-center tw-border tw-border-slate-200 tw-no-underline">
                                                    Reset
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
EOT;

$startPos = strpos($superAdminContent, '<div id="filterSection" class="row mb-3" style="display: none;">');
$endPos = strpos($superAdminContent, '</div>', strpos($superAdminContent, '</div>', strpos($superAdminContent, '</div>', strpos($superAdminContent, '<div id="filterSection" class="row mb-3" style="display: none;">') + 5) + 5) + 5) + 6;
// The existing filter section is a massive nested div, so I will find the exact bounds.
if ($startPos !== false) {
    // Actually, let's just find '<div id="filterSection"' and end at the exact '<!-- Filter Section -->' closing
    // I can do a preg_replace or more exact replace.
    // Let's use preg_replace
    $superAdminContent = preg_replace('/<div id="filterSection".*?<\/form>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/is', $superAdminFilterDesign . "\n                        </div>\n                </div>", $superAdminContent);
    // Let me do a safer approach, I will just replace from `<div id="filterSection" class="row mb-3"` to the end of that row block.
}

// Safer approach: 
$start = strpos($superAdminContent, '<div id="filterSection" class="row mb-3" style="display: none;">');
$end = strpos($superAdminContent, '</div>', strpos($superAdminContent, '</div>', strpos($superAdminContent, '</div>', strpos($superAdminContent, '</div>', strpos($superAdminContent, '</div>', $start) + 1) + 1) + 1) + 1) + 6;

if ($start !== false) {
    $superAdminContent = substr_replace($superAdminContent, $superAdminFilterDesign, $start, $end - $start);
}

// Ensure JS toggle is correct
$superAdminContent = str_replace("$('#filterSection').slideToggle();", "var el = document.getElementById('filterSection'); if(el.style.display === 'none') { el.style.display = 'block'; } else { el.style.display = 'none'; }", $superAdminContent);

file_put_contents($superAdminFile, $superAdminContent);
echo "SuperAdmin view updated.\n";
