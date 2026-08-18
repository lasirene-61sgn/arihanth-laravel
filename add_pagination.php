<?php
function addPaginationToTabs($file) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    
    // Check if already paginated
    if (strpos($content, '$allBuyers->links(') !== false) {
        return;
    }
    
    // We can just add the links before the closing </div> of each tab pane.
    // However, it's safer to add it right after </table></div>
    
    $replacements = [
        'buyers' => '$allBuyers->links("pagination::bootstrap-5")',
        'craftsmen' => '$allCraftsmen->links("pagination::bootstrap-5")',
        'craftsman-staff' => '$allCraftsmanStaff->links("pagination::bootstrap-5")',
        'admins' => '$allAdmins->links("pagination::bootstrap-5")',
        'key-users' => '$allKeyUsers->links("pagination::bootstrap-5")',
        'users' => '$allUsers->links("pagination::bootstrap-5")',
    ];
    
    foreach ($replacements as $tabId => $linksCode) {
        $searchStr = '<div class="tab-pane fade'; // we will find the specific tab pane
        // Actually, we can just replace '</table>\n                </div>' with '</table>\n                </div>\n                <div class="tw-mt-4">\n                    {{ ' . $linksCode . ' }}\n                </div>'
        // But we need to do it only for the specific tab.
        
        // A better approach is to use regex for each tab.
        $pattern = '/(id="' . $tabId . '".*?<\/table>\s*<\/div>)/is';
        $content = preg_replace($pattern, '$1' . "\n                <div class=\"tw-mt-4 tw-px-6\">\n                    {{ $linksCode }}\n                </div>", $content);
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}

addPaginationToTabs('d:/pulic_html/resources/views/super-admin/freeze-account/index.blade.php');
addPaginationToTabs('d:/pulic_html/resources/views/admin/freeze-account/index.blade.php');
