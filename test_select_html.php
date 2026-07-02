<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workOrder = App\Models\WorkOrder::find(1247);
$categories = App\Models\ProductCategory::all();

$html = '';
$html .= '<select id="product_category_id">';
foreach($categories as $category) {
    $selected = (old('product_category_id') == $category->id || $workOrder->product_category_id == $category->id || $workOrder->product_category == $category->name) ? 'selected' : '';
    $html .= '<option value="'.$category->id.'" '.$selected.'>'.$category->name.'</option>';
}
$html .= '</select>';
file_put_contents('test_select.html', $html);
echo "Done\n";
