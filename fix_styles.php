<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Make modals fully wide on all screens
$content = preg_replace('/<div class="modal-dialog modal-xl( roboto-font)?"/', '<div class="modal-dialog modal-xl$1" style="max-width: 98%;"', $content);
$content = preg_replace('/<div class="modal-dialog modal-lg"/', '<div class="modal-dialog modal-xl" style="max-width: 98%;"', $content);
$content = preg_replace('/<div class="modal-dialog"/', '<div class="modal-dialog modal-xl" style="max-width: 98%;"', $content);

// 2. Fix Top Picks Craftsman Row Style
$craftsmanRow = <<<'HTML'
                            @forelse($topPicksCraftsmanFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#f3f4f6' : '#ffffff' }} !important;" class="hover:tw-bg-blue-50/50 tw-transition-colors">
HTML;
$content = preg_replace('/@forelse\(\$topPicksCraftsmanFull as \$code => \$stat\)\s*<tr[^>]*>/', $craftsmanRow, $content);

// 3. Fix Least Picks Craftsman Row Style
$leastCraftsmanRow = <<<'HTML'
                            @forelse($leastPicksCraftsmanFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#f3f4f6' : '#ffffff' }} !important;" class="hover:tw-bg-gray-100 tw-transition-colors">
HTML;
$content = preg_replace('/@forelse\(\$leastPicksCraftsmanFull as \$code => \$stat\)\s*<tr[^>]*>/', $leastCraftsmanRow, $content);

// 4. Fix Top Picks Clients Row Style
$topClientsRow = <<<'HTML'
                            @forelse($topPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#f3f4f6' : '#ffffff' }} !important;" class="hover:tw-bg-emerald-50/50 tw-transition-colors">
HTML;
$content = preg_replace('/@forelse\(\$topPicksClientsFull as \$code => \$stat\)\s*<tr[^>]*>/', $topClientsRow, $content);

// 5. Fix Least Picks Clients Row Style
$leastClientsRow = <<<'HTML'
                            @forelse($leastPicksClientsFull as $code => $stat)
                            <tr style="background-color: {{ $loop->iteration % 2 != 0 ? '#f3f4f6' : '#ffffff' }} !important;" class="hover:tw-bg-gray-100 tw-transition-colors">
HTML;
$content = preg_replace('/@forelse\(\$leastPicksClientsFull as \$code => \$stat\)\s*<tr[^>]*>/', $leastClientsRow, $content);

file_put_contents($file, $content);
echo "Styles Fixed.\n";
