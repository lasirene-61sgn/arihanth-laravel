<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KeyUserUserExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::with(['buyer']);

        // Filter by Search (Name/Email/User Code)
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('user_code', 'like', "%{$search}%");
            });
        }

        // Filter by BP Code
        if ($this->request->filled('filter_bp_code')) {
            $query->where('bp_code', 'like', '%' . $this->request->filter_bp_code . '%');
        }

        // Filter by User Code
        if ($this->request->filled('filter_user_code')) {
            $query->where('user_code', 'like', '%' . $this->request->filter_user_code . '%');
        }

        // Filter by Mobile
        if ($this->request->filled('filter_mobile')) {
            $query->where('mobile_no', 'like', '%' . $this->request->filter_mobile . '%');
        }

        // Filter by Status
        if ($this->request->filled('filter_status')) {
            $query->where('status', $this->request->filter_status);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'User Code',
            'BP Code',
            'Full Name',
            'Email',
            'Mobile No',
            'Business Partner',
            'Status',
            'Created Date'
        ];
    }

    public function map($user): array
    {
        return [
            $user->user_code,
            $user->bp_code,
            $user->full_name,
            $user->email,
            $user->mobile_no,
            $user->buyer->business_name ?? 'N/A',
            $user->status ? 'Active' : 'Inactive',
            $user->created_at->format('d-m-Y')
        ];
    }
}