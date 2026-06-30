<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserProductExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $user = auth()->guard('web')->user();
        $query = Product::where('bp_code', $user->bp_code);

        if ($this->request->filled('filter_product_code')) {
            $query->where('product_code', 'like', '%' . $this->request->filter_product_code . '%');
        }
        if ($this->request->filled('filter_product_name')) {
        $query->where('product_name', 'like', '%' . $this->request->filter_product_name . '%');
    }
        // ... add other filter logic similar to controller ...

        return $query;
    }

    public function headings(): array {
        return ['Product Code', 'Product Name', 'Type', 'Order Type', 'Status', 'Created At'];
    }

    public function map($p): array {
        return [$p->product_code, $p->product_name, $p->type, $p->order_type, $p->open_close, $p->created_at->format('d-m-Y')];
    }
}