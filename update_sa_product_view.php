<?php
$superAdminFile = 'd:/pulic_html/resources/views/super-admin/product/index.blade.php';
$saContent = file_get_contents($superAdminFile);

$saFilterHtml = <<<EOT
    <div id="filterSection" class="tw-hidden tw-mb-6 tw-p-6 tw-bg-white dark:tw-bg-gray-800 tw-rounded-2xl tw-border tw-border-gray-100 dark:tw-border-gray-700 tw-shadow-sm">
        <div class="tw-mb-4 tw-flex tw-justify-between tw-items-center">
            <h3 class="tw-text-sm tw-font-black tw-text-gray-800 dark:tw-text-white tw-uppercase tw-tracking-wider">Advanced Filters</h3>
            <button onclick="toggleSection('filterSection')" class="tw-text-gray-400 hover:tw-text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="GET" action="{{ route('super-admin.product.index') }}" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
            
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
                <select name="category_filter" class="form-select tw-rounded-xl tw-text-sm tw-bg-gray-50 dark:tw-bg-gray-700">
                    <option value="">All Categories</option>
                    @foreach(\$categories as \$cat)
                        <option value="{{ \$cat->id }}" {{ request('category_filter') == \$cat->id ? 'selected' : '' }}>{{ \$cat->name }}</option>
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
                <a href="{{ route('super-admin.product.index') }}" class="tw-px-6 tw-py-2 tw-rounded-xl tw-bg-gray-100 hover:tw-bg-gray-200 tw-text-gray-700 tw-text-xs tw-font-bold tw-no-underline">RESET</a>
                <button type="submit" class="tw-bg-brand tw-text-white tw-px-6 tw-py-2 tw-rounded-xl tw-text-xs tw-font-bold">APPLY FILTERS</button>
            </div>
        </form>
    </div>
EOT;

$start = strpos($saContent, '<div id="filterSection" class="tw-hidden tw-mb-4 tw-p-4');
$end = strpos($saContent, '</div>', strpos($saContent, '</form>', $start)) + 6;

if ($start !== false && $end !== false) {
    $saContent = substr_replace($saContent, $saFilterHtml, $start, $end - $start);
}

// Ensure filter toggle handles memory of opened state if requested
$jsAdd = "<?php if(request()->anyFilled(['filter_name', 'filter_code', 'filter_design_code', 'category_filter', 'filter_subcategory', 'filter_bp_code', 'filter_craftsman'])): ?>
<script>document.addEventListener('DOMContentLoaded', function() { document.getElementById('filterSection').classList.remove('tw-hidden'); });</script>
<?php endif; ?>";

if (strpos($saContent, '<?php if(request()->anyFilled') === false) {
    $saContent = str_replace('@endsection', $jsAdd . "\n@endsection", $saContent);
}

file_put_contents($superAdminFile, $saContent);
echo "SuperAdmin product view updated.\n";
