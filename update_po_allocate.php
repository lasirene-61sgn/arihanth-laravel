<?php
$files = [
    'admin' => "e:/public_html/resources/views/admin/purchase-order/allocate.blade.php",
    'super_admin' => "e:/public_html/resources/views/super-admin/purchase-order/allocate.blade.php"
];

foreach ($files as $guard => $path) {
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        continue;
    }
    
    $c = file_get_contents($path);

    $allocateSearch = '<div class="col-md-6 mb-3">
                                <label for="allocated_craftsman_code" class="form-label">Select Craftsman</label>';
    
    // Replace by adding another col-md-6 for the craftsman_due_date.
    // The structure is `<div class="row"><div class="col-md-6 mb-3">...</div></div>`
    $replaceSearch = '</select>
                            </div>
                        </div>';
    $replaceWith = '</select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="craftsman_due_date" class="form-label">Craftsman Due Date</label>
                                <input type="date" class="form-control" id="craftsman_due_date" name="craftsman_due_date" value="{{ old(\'craftsman_due_date\') }}">
                            </div>
                        </div>';

    $c = str_replace(str_replace("\n", "\r\n", $replaceSearch), str_replace("\n", "\r\n", $replaceWith), $c);
    $c = str_replace($replaceSearch, $replaceWith, $c);

    file_put_contents($path, $c);
    echo "Updated allocate blade for $guard\n";
}
