<?php

namespace App\Imports;

use App\Models\MasterAbangKbm;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterAbangImport implements ToModel, WithHeadingRow
{
    private $importedCount = 0;
    private $skippedCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip jika kolom nama kosong
        if (empty(trim($row['nama']))) {
            return null;
        }

        // Cek duplikasi berdasarkan Nama dan Angkatan
        $existing = MasterAbangKbm::where('name', trim($row['nama']))
            ->where('angkatan', trim($row['angkatan']))
            ->first();

        if ($existing) {
            $this->skippedCount++;
            return null;
        }

        $this->importedCount++;

        return new MasterAbangKbm([
            'name'     => trim($row['nama']),
            'angkatan' => trim($row['angkatan']),
        ]);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}