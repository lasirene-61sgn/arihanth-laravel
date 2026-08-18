<?php

function fix_blade($filepath) {
    if (!file_exists($filepath)) return;
    $content = file_get_contents($filepath);

    // Split by <form method="GET">
    $parts = explode('<form method="GET">', $content);
    
    $newContent = $parts[0];
    
    for ($i = 1; $i < count($parts); $i++) {
        $part = $parts[$i];
        
        // Check which <select name="X"> is in this form.
        // A form usually ends with </form>
        $form_end_pos = strpos($part, '</form>');
        if ($form_end_pos !== false) {
            $form_content = substr($part, 0, $form_end_pos);
            $rest = substr($part, $form_end_pos);
            
            // Find select name
            if (preg_match('/<select name="([^"]+)"/', $form_content, $matches)) {
                $select_name = $matches[1];
                
                // Remove hidden input with the SAME name from the form content
                $form_content = preg_replace('/<input type="hidden" name="' . preg_quote($select_name, '/') . '"[^>]*>\s*/', '', $form_content);
            }
            
            $newContent .= '<form method="GET">' . $form_content . $rest;
        } else {
            $newContent .= '<form method="GET">' . $part;
        }
    }
    
    file_put_contents($filepath, $newContent);
    echo "Fixed $filepath\n";
}

fix_blade('d:\pulic_html\resources\views\super-admin\work-order\index.blade.php');
fix_blade('d:\pulic_html\resources\views\admin\work-order\index.blade.php');

