<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class PesertaImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private array $imported = [];
    private array $skipped  = [];

    public function model(array $row)
    {
        $nim  = trim($row['nim']);
        $nama = trim($row['nama']);

        // 1. Cek apakah NIM sudah ada
        if (User::where('nim', $nim)->exists()) {
            $this->skipped[] = $nim . ' (' . $nama . ') — NIM sudah terdaftar';
            return null;
        }

        // 2. Format Email Otomatis (Gunakan domain yang sama agar seragam)
        // Jika Untirta membedakan email mhs, bisa ganti jadi @mhs.untirta.ac.id
        $email = strtolower($nim) . '@untirta.ac.id';

        $this->imported[] = $nim . ' — ' . $nama;

        return new User([
            'name'                 => $nama,
            'nim'                  => $nim,
            'angkatan'             => trim($row['angkatan']),
            'kelompok'             => trim($row['kelompok']), // Perbedaan: Ada Kelompok
            'gender'               => strtoupper(trim($row['gender'])), // Perbedaan: Ada Gender (L/P)
            'email'                => $email,
            'password'             => Hash::make($nim), // Password awal = NIM
            'role'                 => 'peserta',
            'must_change_password' => true,
        ]);
    }

    public function getImported(): array
    {
        return $this->imported;
    }
    public function getSkipped(): array
    {
        return $this->skipped;
    }
}
