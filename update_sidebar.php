<?php
function processSidebar($file, $routePrefix) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    if (strpos($content, $routePrefix . '.business-partner.craftsman-staff.index') !== false) {
        return;
    }
    
    $search = "route('{$routePrefix}.business-partner.craftman')";
    
    $staffLink = <<<EOT
                            <li class="tw-pl-8">
                                <a href="{{ route('{$routePrefix}.business-partner.craftsman-staff.index') }}" 
                                   class="tw-block tw-py-2 tw-text-sm tw-text-white/80 hover:tw-text-white hover:tw-translate-x-1 tw-transition-all tw-duration-200 {{ request()->routeIs('{$routePrefix}.business-partner.craftsman-staff.*') ? 'tw-text-white tw-font-medium' : '' }}">
                                    Craftsman Staff
                                </a>
                            </li>
EOT;
    
    $pos = strpos($content, $search);
    if ($pos !== false) {
        $liEnd = strpos($content, "</li>", $pos);
        if ($liEnd !== false) {
            $liEnd += 5;
            $content = substr_replace($content, "\n" . $staffLink, $liEnd, 0);
            file_put_contents($file, $content);
            echo "Updated $file\n";
        }
    } else {
        echo "Could not find $search in $file\n";
    }
}

processSidebar('d:/pulic_html/resources/views/super-admin/layouts/app.blade.php', 'super-admin');
processSidebar('d:/pulic_html/resources/views/admin/layouts/app.blade.php', 'admin');
