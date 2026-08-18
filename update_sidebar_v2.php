<?php
function updateSidebar($file, $routePrefix) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    
    // First remove the old one if it exists
    $oldLinkPattern = '/\s*<li class="tw-pl-8">\s*<a href="\{\{\s*route\(\'' . $routePrefix . '\.business-partner\.craftsman-staff\.index\'\)\s*\}\}".*?<\/li>/is';
    $content = preg_replace($oldLinkPattern, '', $content);
    
    // Check if new separately added link exists
    if (strpos($content, $routePrefix . '.business-partner.craftsman-staff"') !== false && strpos($content, '<i class="bi bi-people tw-text-lg"></i>') !== false) {
        return; // Already added separately
    }
    
    // Prepare the standalone link HTML
    $standaloneLink = <<<EOT

                    <li class="tw-mb-1">
                        <a class="flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition-colors {{ request()->routeIs('{$routePrefix}.business-partner.craftsman-staff*') ? 'bg-white/20 font-bold text-white' : 'text-white/70 hover:text-white' }}"
                            href="{{ route('{$routePrefix}.business-partner.craftsman-staff') }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-people tw-text-lg"></i>
                                <span>Craftsman Staff</span>
                            </div>
                        </a>
                    </li>
EOT;

    // For SuperAdmin
    if ($routePrefix === 'super-admin') {
        $standaloneLink = str_replace('flex items-center', 'tw-flex tw-items-center', $standaloneLink);
        $standaloneLink = str_replace('justify-between', 'tw-justify-between', $standaloneLink);
        $standaloneLink = str_replace('px-4 py-3', 'tw-px-4 tw-py-3', $standaloneLink);
        $standaloneLink = str_replace('rounded-lg', 'tw-rounded-lg', $standaloneLink);
        $standaloneLink = str_replace('hover:bg-white/10', 'hover:tw-bg-white/10', $standaloneLink);
        $standaloneLink = str_replace('transition-colors', 'tw-transition-colors', $standaloneLink);
        $standaloneLink = str_replace('bg-white/20', 'tw-bg-white/20', $standaloneLink);
        $standaloneLink = str_replace('font-bold', 'tw-font-bold', $standaloneLink);
        $standaloneLink = str_replace('text-white', 'tw-text-white', $standaloneLink);
        $standaloneLink = str_replace('text-white/70', 'tw-text-white/70', $standaloneLink);
        $standaloneLink = str_replace('hover:text-white', 'hover:tw-text-white', $standaloneLink);
        $standaloneLink = str_replace('gap-3', 'tw-gap-3', $standaloneLink);
        $standaloneLink = str_replace('class="flex', 'class="tw-flex', $standaloneLink);
    }
    
    // Find the end of Business Partner block to insert the standalone link
    // The business partner block is usually a <li> with x-data="{ expanded: ... }"
    $bpStartPos = strpos($content, 'business-partner.*');
    if ($bpStartPos !== false) {
        // Find the closing </li> of the business partner block
        $liEndPos = strpos($content, '</li>', $bpStartPos);
        if ($liEndPos !== false) {
            $liEndPos += 5; // move past </li>
            
            // Insert standalone link
            $content = substr_replace($content, $standaloneLink, $liEndPos, 0);
            
            file_put_contents($file, $content);
            echo "Successfully updated $file\n";
            return;
        }
    }
    
    echo "Could not find insertion point in $file\n";
}

updateSidebar('d:/pulic_html/resources/views/super-admin/layouts/app.blade.php', 'super-admin');
updateSidebar('d:/pulic_html/resources/views/admin/layouts/app.blade.php', 'admin');
