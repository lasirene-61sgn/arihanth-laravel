<?php
$file = 'E:/arihanth/resources/views/super-admin/work-order/index.blade.php';
$content = file_get_contents($file);

$replace = '<a href="{{ route(\'super-admin.work-order.copy\', $order) }}" class="tw-p-2 tw-text-emerald-600 hover:tw-bg-emerald-50 tw-rounded-lg tw-transition-colors" title="Copy">
                                                <i class="bi bi-copy"></i>
                                            </a>
                                            <form action="{{ route(\'super-admin.work-order.destroy\', $order) }}" method="POST" class="tw-inline-block" onsubmit="return confirm(\'Are you sure you want to delete this completed order?\');">
                                                @csrf @method(\'DELETE\')
                                                <button type="submit" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>';

$searchRegex = '/<a href="\{\{ route\(\'super-admin\.work-order\.copy\', \$order\) \}\}" class="tw-p-2 tw-text-emerald-600 hover:tw-bg-emerald-50 tw-rounded-lg tw-transition-colors" title="Copy">\s*<i class="bi bi-copy"><\/i>\s*<\/a>/';

$content = preg_replace($searchRegex, $replace, $content);
file_put_contents($file, $content);
echo "Fixed.\n";
