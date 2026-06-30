<?php

namespace App\Exports;

use App\Models\KeyUser;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminKeyUserExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) { $this->request = $request; }

    public function query()
    {
        $query = KeyUser::query();

        if ($this->request->filled('filter_user_code')) {
            $query->where('user_code', 'like', '%' . $this->request->filter_user_code . '%');
        }
        if ($this->request->filled('filter_name')) {
            $query->where('full_name', 'like', '%' . $this->request->filter_name . '%');
        }

        return $query;
    }

    public function headings(): array {
        return ['User Code', 'Full Name', 'Email', 'Mobile', 'Status', 'Created At'];
    }

    public function map($u): array {
        return [
            $u->user_code,
            $u->full_name,
            $u->email_id,
            $u->mobile_no,
            $u->status ? 'Active' : 'Inactive',
            $u->created_at->format('d-m-Y')
        ];
    }
}