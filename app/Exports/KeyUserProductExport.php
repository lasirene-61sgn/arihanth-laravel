<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KeyUserProductExport implements FromQuery, WithHeadings, WithMapping
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
        $query = Product::where('bp_code', $this->bp_code);

        // Advanced Filters
        if ($this->request->filled('filter_name')) {
            $query->where('product_name', 'like', '%' . $this->request->filter_name . '%');
        }
        if ($this->request->filled('filter_code')) {
            $query->where('product_code', 'like', '%' . $this->request->filter_code . '%');
        }
        if ($this->request->filled('type')) {
            $query->where('type', $this->request->type);
        }
        if ($this->request->filled('order_type')) {
            $query->where('order_type', $this->request->order_type);
        }
        if ($this->request->filled('status')) {
            $query->where('open_close', $this->request->status);
        }
        
        // Inside your Export class query() method...
if ($this->request->filled('filter_category')) {
    $query->where('product_category', 'like', '%' . $this->request->filter_category . '%');
}

if ($this->request->filled('filter_subcategory')) {
    $query->where('product_subcategory', 'like', '%' . $this->request->filter_subcategory . '%');
}

        return $query->orderBy($this->request->get('sort', 'created_at'), $this->request->get('direction', 'desc'));
    }

    public function headings(): array
    {
        return ['Product Code', 'Name', 'Type', 'Order Type', 'Status', 'Created At'];
    }

    public function map($product): array
    {
        return [
            $product->product_code,
            $product->product_name,
            $product->type,
            $product->order_type,
            $product->open_close,
            $product->created_at->format('d-m-Y'),
        ];
    }
}