<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CraftsmanProductExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $craftsman = Auth::guard('craftsman')->user();
        $query = Product::where('bp_code', $craftsman->craftman_code);

        if ($this->request->filled('filter_product_code')) {
            $query->where('product_code', 'like', '%' . $this->request->filter_product_code . '%');
        }
        if ($this->request->filled('filter_product_name')) {
            $query->where('product_name', 'like', '%' . $this->request->filter_product_name . '%');
        }

        return $query;
    }

    public function headings(): array {
        return ['Product Code', 'Product Name', 'Category', 'Subcategory', 'Weight From', 'Weight To', 'Created At'];
    }

    public function map($p): array {
        return [
            $p->product_code, 
            $p->product_name, 
            $p->category->name ?? 'N/A', 
            $p->subcategory->name ?? 'N/A', 
            $p->weight_from, 
            $p->weight_to, 
            $p->created_at->format('d-m-Y')
        ];
    }
}