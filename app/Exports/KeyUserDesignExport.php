<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KeyUserDesignExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
{
    $query = Product::whereNotNull('design_code')->where('design_status', 'Accepted');

    if ($this->request->filled('filter_design_code')) {
        $query->where('design_code', 'like', '%' . $this->request->filter_design_code . '%');
    }
    if ($this->request->filled('filter_product_code')) {
        $query->where('product_code', 'like', '%' . $this->request->filter_product_code . '%');
    }
    if ($this->request->filled('filter_name')) {
        $query->where('product_name', 'like', '%' . $this->request->filter_name . '%');
    }
    if ($this->request->filled('filter_category')) {
        $query->whereHas('category', function($q) {
            $q->where('name', 'like', '%' . $this->request->filter_category . '%');
        });
    }
    if ($this->request->filled('filter_subcategory')) {
        $query->whereHas('subcategory', function($q) {
            $q->where('name', 'like', '%' . $this->request->filter_subcategory . '%');
        });
    }

    return $query;
}

    public function headings(): array
    {
        return ['Design Code', 'Product Name', 'Category', 'Type', 'Weight From', 'Weight To'];
    }

    public function map($design): array
    {
        return [
            $design->design_code,
            $design->product_name,
            $design->category->name ?? 'N/A',
            $design->type,
            $design->weight_from,
            $design->weight_to,
        ];
    }
}