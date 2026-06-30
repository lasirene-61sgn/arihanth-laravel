<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BuyerProductExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;
    protected $bp_code;

    public function __construct($request, $bp_code)
    {
        $this->request = $request;
        $this->bp_code = $bp_code;
    }

    public function query()
    {
        $query = Product::query()->where('bp_code', $this->bp_code);

        // Apply same filters as the Index page
        if ($this->request->filled('search')) {
            $query->where('product_name', 'like', '%' . $this->request->search . '%');
        }

        if ($this->request->filled('category')) {
            $query->where('category_id', $this->request->category);
        }

        // Apply Sorting
        $sort = $this->request->get('sort', 'created_at');
        $direction = $this->request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        return $query;
    }

    public function headings(): array
    {
        return ['Product Code', 'Product Name', 'Category', 'Subcategory', 'Type'];
    }

    public function map($product): array
    {
        return [
            $product->product_code,
            $product->product_name,
            $product->category->name ?? 'N/A',
            $product->subcategory->name ?? 'N/A',
            $product->type,
        ];
    }
}