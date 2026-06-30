<?php

namespace App\Exports;

use App\Models\KeyUser;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BuyerKeyUserExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = KeyUser::query();

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where('full_name', 'like', "%{$search}%")->orWhere('email_id', 'like', "%{$search}%");
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        $sort = $this->request->get('sort', 'created_at');
        $direction = $this->request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        return $query;
    }

    public function headings(): array
    {
        return ['User Code', 'Full Name', 'BP Code', 'Email', 'Mobile', 'Status'];
    }

    public function map($user): array
    {
        return [
            $user->user_code,
            $user->full_name,
            $user->bp_code,
            $user->email_id,
            $user->mobile_no,
            $user->status
        ];
    }
}