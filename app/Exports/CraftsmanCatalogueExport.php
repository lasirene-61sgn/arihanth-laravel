<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CraftsmanCatalogueExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $craftsmanId = Auth::guard('craftsman')->id();
        $query = Product::where('created_by', $craftsmanId)
                        ->where('design_status', 'Accepted')
                        ->whereNotNull('design_code');

        // Apply filters here same as controller...
        return $query;
    }

    public function headings(): array {
        return ['Design Code', 'Product Code', 'Product Name', 'Category', 'Weight From', 'Weight To', 'Date Accepted'];
    }

    public function map($p): array {
        return [
            $p->design_code, 
            $p->product_code, 
            $p->product_name, 
            $p->category->name ?? 'N/A', 
            $p->weight_from, 
            $p->weight_to, 
            $p->updated_at->format('d-m-Y')
        ];
    }
}