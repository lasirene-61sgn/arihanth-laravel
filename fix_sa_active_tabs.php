<?php
$path = 'e:/public_html/resources/views/super-admin/purchase-order/index.blade.php';
$c = file_get_contents($path);

// Replace button active class
$c = str_replace(
    '<button class="nav-link {{ $index == 0 ? \'active\' : \'\' }}"',
    '<button class="nav-link {{ request(\'tab\', \'created\') == $tab[\'id\'] ? \'active\' : \'\' }}"',
    $c
);

// Replace aria-selected
$c = str_replace(
    'aria-selected="{{ $index == 0 ? \'true\' : \'false\' }}">',
    'aria-selected="{{ request(\'tab\', \'created\') == $tab[\'id\'] ? \'true\' : \'false\' }}">',
    $c
);

// Replace tab pane active class
$c = str_replace(
    '<div class="tab-pane fade {{ $index == 0 ? \'show active\' : \'\' }}"',
    '<div class="tab-pane fade {{ request(\'tab\', \'created\') == $tab[\'id\'] ? \'show active\' : \'\' }}"',
    $c
);

file_put_contents($path, $c);
echo "SuperAdmin tabs active state fixed.\n";
