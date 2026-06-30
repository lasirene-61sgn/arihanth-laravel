<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductAdminExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $query = Product::with(['category', 'subcategory']);

        // --- SELECTION FILTER ---
        if ($this->request->filled('selected_ids')) {
            $ids = explode(',', $this->request->selected_ids);
            return $query->whereIn('id', $ids)->latest();
        }

        if ($this->request->filled('filter_name')) {
            $query->where('product_name', 'like', '%' . $this->request->filter_name . '%');
        }
        if ($this->request->filled('filter_code')) {
            $query->where('product_code', 'like', '%' . $this->request->filter_code . '%');
        }
        if ($this->request->filled('filter_bp_code')) {
            $query->where('bp_code', 'like', '%' . $this->request->filter_bp_code . '%');
        }

        return $query->latest();
    }

    public function headings(): array {
        return ['Product Code', 'Product Name', 'Category', 'Sub Category', 'Type', 'BP Code', 'Created At'];
    }

    public function map($p): array {
        return [
            $p->product_code,
            $p->product_name,
            $p->category->name ?? 'N/A',
            $p->subcategory->name ?? 'N/A',
            $p->type,
            $p->bp_code,
            $p->created_at->format('d-m-Y')
        ];
    }
}