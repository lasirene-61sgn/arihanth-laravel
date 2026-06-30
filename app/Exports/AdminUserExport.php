<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminUserExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $query = User::with('buyer');

        if ($this->request->filled('filter_user_code')) {
            $query->where('user_code', 'like', '%' . $this->request->filter_user_code . '%');
        }
        // ... include other filter logic here ...

        return $query;
    }

    public function headings(): array {
        return ['User Code', 'Full Name', 'Email', 'Mobile', 'City', 'Status', 'BP Code', 'Business Name'];
    }

    public function map($u): array {
        return [
            $u->user_code,
            $u->full_name,
            $u->email_id,
            $u->mobile_no,
            $u->city,
            $u->status,
            $u->buyer->bp_code ?? 'N/A',
            $u->buyer->business_name ?? 'N/A'
        ];
    }
}