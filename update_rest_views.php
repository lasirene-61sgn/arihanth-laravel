<?php
$adminFilterHtml = <<<EOT
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Name</label>
                                    <input type="text" name="filter_name" value="{{ request('filter_name') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Code</label>
                                    <input type="text" name="filter_code" value="{{ request('filter_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Design Code</label>
                                    <input type="text" name="filter_design_code" value="{{ request('filter_design_code') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Category</label>
                                    <select name="filter_category" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Categories</option>
                                        @foreach(\$categories as \$cat)
                                            <option value="{{ \$cat->id }}" {{ request('filter_category') == \$cat->id ? 'selected' : '' }}>{{ \$cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Subcategory</label>
                                    <select name="filter_subcategory" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Subcategories</option>
                                        @foreach(\$subCategories as \$sub)
                                            <option value="{{ \$sub->id }}" {{ request('filter_subcategory') == \$sub->id ? 'selected' : '' }}>{{ \$sub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">BP Code (Buyer)</label>
                                    <select name="filter_bp_code" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Buyers</option>
                                        @foreach(\$buyers as \$buyer)
                                            <option value="{{ \$buyer->bp_code }}" {{ request('filter_bp_code') == \$buyer->bp_code ? 'selected' : '' }}>{{ \$buyer->bp_code }} - {{ \$buyer->business_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Craftsman Code</label>
                                    <select name="filter_craftsman" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-magenta-500">
                                        <option value="">All Craftsmen</option>
                                        @foreach(\$craftsmen as \$craftsman)
                                            <option value="{{ \$craftsman->craftman_code }}" {{ request('filter_craftsman') == \$craftsman->craftman_code ? 'selected' : '' }}>{{ \$craftsman->craftman_code }} - {{ \$craftsman->business_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
EOT;

$saFilterHtml = <<<EOT
    <div id="filterSection" class="tw-hidden tw-mb-6 tw-p-6 tw-bg-white dark:tw-bg-gray-800 tw-rounded-2xl tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-shadow-sm">
        <div class="tw-mb-4 tw-flex tw-justify-between tw-items-center">
            <h3 class="tw-text-sm tw-font-black tw-text-gray-800 dark:tw-text-white tw-uppercase tw-tracking-wider">Advanced Filters</h3>
            <button onclick="toggleSection('filterSection')" class="tw-text-gray-400 hover:tw-text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="GET" action="{{ url()->current() }}" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
            
            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">Product Name</label>
                <input type="text" name="filter_name" value="{{ request('filter_name') }}" class="form-control tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
            </div>
            
            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">Product Code</label>
                <input type="text" name="filter_code" value="{{ request('filter_code') }}" class="form-control tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
            </div>
            
            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">Design Code</label>
                <input type="text" name="filter_design_code" value="{{ request('filter_design_code') }}" class="form-control tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
            </div>

            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">Category</label>
                <select name="filter_category" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
                    <option value="">All Categories</option>
                    @foreach(\$categories as \$cat)
                        <option value="{{ \$cat->id }}" {{ request('filter_category') == \$cat->id ? 'selected' : '' }}>{{ \$cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-space-y-1">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">Sub Category</label>
                <select name="filter_subcategory" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
                    <option value="">All Sub Categories</option>
                    @foreach(\$subCategories as \$sub)
                        <option value="{{ \$sub->id }}" {{ request('filter_subcategory') == \$sub->id ? 'selected' : '' }}>{{ \$sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-space-y-1 lg:tw-col-span-2">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">BP Code (Buyer)</label>
                <select name="filter_bp_code" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
                    <option value="">All Buyers</option>
                    @foreach(\$buyers as \$buyer)
                        <option value="{{ \$buyer->bp_code }}" {{ request('filter_bp_code') == \$buyer->bp_code ? 'selected' : '' }}>{{ \$buyer->bp_code }} - {{ \$buyer->business_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tw-space-y-1 lg:tw-col-span-2">
                <label class="tw-text-[10px] tw-font-bold tw-text-gray-500 tw-uppercase">Craftsman Code</label>
                <select name="filter_craftsman" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
                    <option value="">All Craftsmen</option>
                    @foreach(\$craftsmen as \$craftsman)
                        <option value="{{ \$craftsman->craftman_code }}" {{ request('filter_craftsman') == \$craftsman->craftman_code ? 'selected' : '' }}>{{ \$craftsman->craftman_code }} - {{ \$craftsman->business_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="tw-col-span-1 md:tw-col-span-2 lg:tw-col-span-4 tw-flex tw-justify-end tw-gap-2 tw-mt-2">
                <a href="{{ url()->current() }}" class="tw-px-6 tw-py-2 tw-rounded-xl tw-bg-gray-100 hover:tw-bg-gray-200 tw-text-gray-700 tw-text-xs tw-font-bold tw-no-underline">RESET</a>
                <button type="submit" class="tw-bg-brand tw-text-white tw-px-6 tw-py-2 tw-rounded-xl tw-text-xs tw-font-bold">APPLY FILTERS</button>
            </div>
        </form>
    </div>
EOT;

$filesToUpdate = [
    'd:/pulic_html/resources/views/admin/design/index.blade.php' => 'admin',
    'd:/pulic_html/resources/views/admin/catalogue/index.blade.php' => 'admin',
    'd:/pulic_html/resources/views/super-admin/design/index.blade.php' => 'superadmin',
    'd:/pulic_html/resources/views/super-admin/catalogue/index.blade.php' => 'superadmin',
];

foreach ($filesToUpdate as $file => $type) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    if ($type === 'admin') {
        $startPos = strpos($content, '<div class="grid grid-cols-2 gap-4">');
        if ($startPos !== false) {
            $endPos = strpos($content, '<div class="flex gap-3 pt-4 border-t border-gray-100">');
            if ($endPos === false) {
                 $endPos = strpos($content, '<div class="pt-4 flex items-center justify-end gap-2 border-t border-gray-100">');
            }
            if ($endPos !== false) {
                $content = substr_replace($content, $adminFilterHtml . "\n                            ", $startPos, $endPos - $startPos);
            }
        }
    } else {
        $start = strpos($content, '<div id="filterSection"');
        if ($start !== false) {
            $end = strpos($content, '</div>', strpos($content, '</form>', $start)) + 6;
            if ($end !== false) {
                $content = substr_replace($content, $saFilterHtml, $start, $end - $start);
                
                // Add JS for filter retention
                $jsAdd = "<?php if(request()->anyFilled(['filter_name', 'filter_code', 'filter_design_code', 'filter_category', 'filter_subcategory', 'filter_bp_code', 'filter_craftsman'])): ?>\n<script>document.addEventListener('DOMContentLoaded', function() { document.getElementById('filterSection').classList.remove('tw-hidden'); });</script>\n<?php endif; ?>";
                
                if (strpos($content, '<?php if(request()->anyFilled') === false) {
                    $content = str_replace('@endsection', $jsAdd . "\n@endsection", $content);
                }
            }
        }
    }

    file_put_contents($file, $content);
    echo "Updated $file\n";
}
