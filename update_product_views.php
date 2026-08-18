<?php
$adminFile = 'd:/pulic_html/resources/views/admin/product/index.blade.php';
$content = file_get_contents($adminFile);

$filterHtml = <<<EOT
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
                                        @foreach(\$categories as \$category)
                                            <option value="{{ \$category->id }}" {{ request('filter_category') == \$category->id ? 'selected' : '' }}>{{ \$category->name }}</option>
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

$startPos = strpos($content, '<div class="grid grid-cols-2 gap-4">');
if ($startPos !== false) {
    // Find end of this div block
    $endPos = strpos($content, '<div class="pt-4 flex items-center justify-end gap-2 border-t border-gray-100">');
    if ($endPos !== false) {
        $content = substr_replace($content, $filterHtml . "\n                            ", $startPos, $endPos - $startPos);
    }
}

file_put_contents($adminFile, $content);
echo "Admin product view updated.\n";

// Same for SuperAdmin
$superAdminFile = 'd:/pulic_html/resources/views/super-admin/product/index.blade.php';
$saContent = file_get_contents($superAdminFile);
if (strpos($saContent, '<div class="grid grid-cols-2 gap-4">') !== false) {
    $saStart = strpos($saContent, '<div class="grid grid-cols-2 gap-4">');
    $saEnd = strpos($saContent, '<div class="pt-4 flex items-center justify-end gap-2 border-t border-gray-100">');
    if ($saStart !== false && $saEnd !== false) {
        $saContent = substr_replace($saContent, $filterHtml . "\n                            ", $saStart, $saEnd - $saStart);
    }
} else {
    // Maybe SuperAdmin has a different design, wait let's just replace it or use regex
    // I'll check SuperAdmin file separately later.
}
file_put_contents($superAdminFile, $saContent);
