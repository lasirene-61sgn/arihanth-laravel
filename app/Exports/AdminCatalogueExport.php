<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminCatalogueExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function query()
    {
        $query = Product::query()->where('design_status', 'Accepted');

        // --- SELECTION FILTER ---
        if ($this->request->filled('selected_ids')) {
            $ids = explode(',', $this->request->selected_ids);
            return $query->whereIn('id', $ids);
        }

        if ($this->request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $this->request->product_name . '%');
        }
        if ($this->request->filled('bp_code')) {
            $query->where('bp_code', 'like', '%' . $this->request->bp_code . '%');
        }

        return $query;
    }

    public function headings(): array {
        return ["Design Code", "Product Code", "Product Name", "Weight Range", "BP Code"];
    }

    public function map($product): array {
        return [
            $product->design_code,
            $product->product_code,
            $product->product_name,
            $product->weight_from . ' - ' . $product->weight_to,
            $product->bp_code,
        ];
    }
}