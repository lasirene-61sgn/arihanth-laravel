<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function collection() {
        $query = User::with(['createdBy', 'buyer']);
        
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email_id', 'like', "%$search%")
                  ->orWhere('user_code', 'like', "%$search%");
            });
        }

        if ($this->request->filled('status_filter')) {
            $query->where('status', $this->request->status_filter);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array {
        return ['User Code', 'Full Name', 'Email', 'Mobile', 
        'City', 'State', 'Country', 'Pincode', 'Aadhar Number',
         'Status', 'Created By', 'Business Partner'];
    }

    public function map($user): array {
        return [
            $user->user_code,
            $user->full_name,
            $user->email_id,
            $user->mobile_no,
            $user->city,
            $user->state,
            $user->country,
            $user->pincode,
            $user->aadhar_number,
            ucfirst($user->status),
            $user->createdBy->full_name ?? 'System',
            $user->buyer ? $user->buyer->bp_code . ' - ' . $user->buyer->business_name : 'N/A'
        ];
    }
}