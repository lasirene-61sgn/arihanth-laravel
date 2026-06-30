<?php

namespace App\Exports;

use App\Models\Buyer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class AdminBuyerExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Buyer::query();

        // Apply same filters as the Index page
        if ($this->request->filled('search')) {
            $searchTerm = $this->request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('bp_code', 'like', "%{$searchTerm}%")
                  ->orWhere('business_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('mobile', 'like', "%{$searchTerm}%");
            });
        }

        if ($this->request->filled('bp_code')) {
            $query->where('bp_code', 'like', "%{$this->request->bp_code}%");
        }

        if ($this->request->filled('business_name')) {
            $query->where('business_name', 'like', "%{$this->request->business_name}%");
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'BP Code',
            'Business Name',
            'Contact Person',
            'Mobile',
            'Email',
            'Created Date'
        ];
    }

    public function map($buyer): array
    {
        return [
            $buyer->bp_code,
            $buyer->business_name,
            $buyer->name,
            $buyer->mobile,
            $buyer->email,
            $buyer->created_at->format('d-m-Y'),
        ];
    }
}