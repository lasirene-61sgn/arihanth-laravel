<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KeyUserCatalogueExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $userId = auth()->guard('key_user')->id() ?? auth()->guard('buyer')->id();
        $query = Product::where('created_by', $userId)->where('design_status', 'Accepted');

        if ($this->request->filled('filter_design_code')) {
            $query->where('design_code', 'like', '%' . $this->request->filter_design_code . '%');
        }
        // ... add other filters same as controller ...

        return $query;
    }

    public function headings(): array {
        return ['Design Code', 'Product Code', 'Product Name', 'Category', 'Subcategory', 'Weight Range', 'Created Date'];
    }

    public function map($p): array {
        return [$p->design_code, $p->product_code, $p->product_name, $p->category->name ?? 'N/A', $p->subcategory->name ?? 'N/A', $p->weight_from.'-'.$p->weight_to.'g', $p->updated_at->format('d-m-Y')];
    }
}