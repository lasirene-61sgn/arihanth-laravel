<?php
$file = 'E:/arihanth/resources/views/super-admin/dashboard.blade.php';
$content = file_get_contents($file);

// Find the @forelse for topPicksCraftsmanFull and check if it has @endforelse
$search = '/@forelse\(\$topPicksCraftsmanFull as \$code => \$stat\)(.*?)<\/tbody>/s';
if (preg_match($search, $content, $matches)) {
    if (strpos($matches[1], '@endforelse') === false) {
        // Need to add @endforelse before </tbody>
        $newMatch = str_replace('</tbody>', "@endforelse\n                        </tbody>", $matches[0]);
        $content = str_replace($matches[0], $newMatch, $content);
        file_put_contents($file, $content);
        echo "Fixed missing @endforelse.\n";
    } else {
        echo "@endforelse already exists.\n";
    }
} else {
    echo "Top Picks Craftsman table not found or already closed.\n";
}
