<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;

class PanitiaImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private array $imported = [];
    private array $skipped  = [];

    public function model(array $row)
    {
        // Lewati baris yang NIM-nya sudah ada di database
        if (User::where('nim', trim($row['nim']))->exists()) {
            $this->skipped[] = trim($row['nim']) . ' (' . trim($row['nama']) . ') — NIM sudah terdaftar';
            return null;
        }

        // Lewati baris yang email-nya sudah ada
        if (!empty($row['email']) && User::where('email', trim($row['email']))->exists()) {
            $this->skipped[] = trim($row['nim']) . ' (' . trim($row['nama']) . ') — email sudah terdaftar';
            return null;
        }

        $nim   = trim($row['nim']);
        $email = !empty($row['email']) ? strtolower(trim($row['email'])) : strtolower($nim) . '@panitia.pkkmb';

        $this->imported[] = $nim . ' — ' . trim($row['nama']);

        return new User([
            'name'                 => trim($row['nama']),
            'nim'                  => $nim,
            'angkatan'             => trim($row['angkatan']),
            'divisi'               => trim($row['divisi']),
            'email'                => $email,
            'password'             => Hash::make($nim),   // password awal = NIM
            'role'                 => 'panitia',
            'must_change_password' => true,               // wajib ganti password
        ]);
    }

    public function getImported(): array { return $this->imported; }
    public function getSkipped(): array  { return $this->skipped; }
}