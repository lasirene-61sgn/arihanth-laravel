<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CraftsmanPurchaseOrderExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $craftsman = Auth::guard('craftsman')->user();
        $query = PurchaseOrder::where('allocated_craftsman_code', $craftsman->craftman_code);

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('purchase_order_code', 'like', "%{$search}%");
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
            'Purchase Order Code',
            'Items Count',
            'Allocated Date',
            'Status'
        ];
    }

    public function map($order): array
    {
        return [
            $order->purchase_order_code,
            $order->items ? count($order->items) : 0,
            $order->updated_at->format('d-m-Y'),
            ucfirst($order->craftsman_status)
        ];
    }
}
