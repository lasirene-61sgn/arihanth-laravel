<?php

$files = [
    'd:\pulic_html\resources\views\admin\work-order\edit.blade.php',
    'd:\pulic_html\resources\views\super-admin\work-order\edit.blade.php'
];

$styles_to_add = <<<'EOD'
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
EOD;

$scripts_to_add = <<<'EOD'
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('#bp_code').length) {
            $('#bp_code').select2({
                placeholder: "Select BP Code",
                allowClear: true,
                width: '100%'
            });
            
            // If there's an existing onchange listener, select2 might not trigger it properly unless we do this
            $('#bp_code').on('select2:select', function (e) {
                this.dispatchEvent(new Event('change'));
            });
        }
    });
</script>
EOD;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Add styles if not present
        if (strpos($content, 'select2.min.css') === false) {
            $content = preg_replace("/(@section\('styles'\))/s", "$1\n$styles_to_add", $content);
        }
        
        // Add scripts if not present
        if (strpos($content, 'select2.min.js') === false) {
            // Find the first <script> block at the bottom and insert right before it
            // Or just insert right before @endsection
            $content = preg_replace("/(<script>)/s", "$scripts_to_add\n$1", $content, 1);
        }
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
