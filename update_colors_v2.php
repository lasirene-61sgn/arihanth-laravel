<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Update row colors to a more visible grey (#e5e7eb) for odd rows
$content = str_replace(
    "style=\"background-color: {{ \$loop->iteration % 2 != 0 ? '#f3f4f6' : '#ffffff' }} !important;\"",
    "style=\"background-color: {{ \$loop->iteration % 2 != 0 ? '#e5e7eb' : '#ffffff' }} !important;\"",
    $content
);

// 2. Remove interfering background colors from <td> tags that might be hiding the row color
$content = str_replace('tw-bg-blue-50/50', '', $content);
$content = str_replace('tw-bg-indigo-50/50', '', $content);
$content = str_replace('tw-bg-slate-50/50', '', $content);
$content = str_replace('tw-bg-emerald-50/50', '', $content);

file_put_contents($file, $content);
echo "Colors Updated.\n";
