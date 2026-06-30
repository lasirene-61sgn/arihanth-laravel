<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CraftsmanDesignExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $query = Product::whereNotNull('design_code')->where('design_status', 'Accepted');

        if ($this->request->filled('filter_design_code')) {
            $query->where('design_code', 'like', '%' . $this->request->filter_design_code . '%');
        }
        if ($this->request->filled('filter_product_name')) {
            $query->where('product_name', 'like', '%' . $this->request->filter_product_name . '%');
        }
        if ($this->request->filled('filter_product_code')) {
            $query->where('product_code', 'like', '%' . $this->request->filter_product_code . '%');
        }

        return $query;
    }

    public function headings(): array {
        return ['Design Code', 'Product Code', 'Product Name', 'Category', 'Subcategory', 'Type', 'Weight From', 'Weight To'];
    }

    public function map($d): array {
        return [
            $d->design_code, 
            $d->product_code, 
            $d->product_name, 
            $d->category->name ?? 'N/A', 
            $d->subcategory->name ?? 'N/A', 
            $d->type, 
            $d->weight_from, 
            $d->weight_to
        ];
    }
}