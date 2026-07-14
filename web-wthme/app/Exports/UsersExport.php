<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(private readonly string $role)
    {
    }

    public function collection()
    {
        return User::query()
            ->where('role', $this->role)
            ->orderBy('name')
            ->get()
            ->values()
            ->map(fn (User $user, int $index) => [
                $index + 1,
                $user->name,
                $user->nim,
                $user->email,
                $user->angkatan,
                $user->kelompok,
                $user->divisi,
                $user->is_active ? 'Aktif' : 'Nonaktif',
                $user->deactivation_message,
            ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIM',
            'Email',
            'Angkatan',
            'Kelompok',
            'Divisi',
            'Status Akun',
            'Pesan Penonaktifan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002F45']],
            ],
        ];
    }
}
