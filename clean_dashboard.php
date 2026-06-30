<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// Let's use preg_replace to remove any leastPicksClientsModal that DOES NOT have "modal-xl" (which is the old one)
$content = preg_replace('/<!-- Least Picks Clients Modal -->\s*<div class="modal fade" id="leastPicksClientsModal" tabindex="-1" aria-hidden="true">\s*<div class="modal-dialog">\s*<div class="modal-content dark:tw-bg-slate-900.*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s', '', $content);

file_put_contents($file, $content);
echo "Cleaned up.\n";
