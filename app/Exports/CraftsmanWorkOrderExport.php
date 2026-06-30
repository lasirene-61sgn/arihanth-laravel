<?php

namespace App\Exports;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CraftsmanWorkOrderExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $craftsman = Auth::guard('craftsman')->user();
        $query = WorkOrder::where('allocated_craftsman_bp_code', $craftsman->craftman_code);

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('status')) {
            $query->where('craftsman_status', $this->request->status);
        }

        if ($this->request->filled('sort_by')) {
            $sortBy = $this->request->sort_by;
            $sortOrder = $this->request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Work Order Number',
            'Customer Name',
            'Product Name',
            'Product Code',
            'Quantity',
            'Due Date',
            'Status',
            'Allocated Date'
        ];
    }

    public function map($order): array
    {
        return [
            $order->work_order_number,
            $order->customer_name,
            $order->product_name ?? $order->product_code,
            $order->product_code,
            $order->quantity,
            $order->due_date ? $order->due_date->format('d-m-Y') : 'N/A',
            ucfirst($order->craftsman_status),
            $order->created_at->format('d-m-Y')
        ];
    }
}
