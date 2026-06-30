<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserCatalogueExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $userId = Auth::id();
        $query = Product::where('created_by', $userId)
                        ->where('design_status', 'Accepted')
                        ->whereNotNull('design_code');

        if ($this->request->filled('filter_design_code')) {
            $query->where('design_code', 'like', '%' . $this->request->filter_design_code . '%');
        }
        if ($this->request->filled('filter_product_name')) {
            $query->where('product_name', 'like', '%' . $this->request->filter_product_name . '%');
        }

        return $query;
    }

    public function headings(): array {
        return ['Design Code', 'Product Code', 'Product Name', 'Category', 'Type', 'Weight From', 'Weight To', 'Created Date'];
    }

    public function map($p): array {
        return [$p->design_code, $p->product_code, $p->product_name, $p->category->name ?? 'N/A', $p->type, $p->weight_from, $p->weight_to, $p->created_at->format('d-m-Y')];
    }
}