<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder opsional: jalankan SEKALI setelah migrate untuk mengisi item
 * Logistik dan Konsumsi bawaan. Jika sudah ada data, seeder ini aman
 * (menggunakan insertOrIgnore supaya tidak duplikat).
 *
 * Cara pakai: php artisan db:seed --class=P3kBarangSeeder
 */
class P3kBarangSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $barangs = [
            // ── LOGISTIK – KELOMPOK ──────────────────────────────────────────
            ['nama_barang' => 'Termos',        'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 2,  'satuan' => 'buah'],
            ['nama_barang' => 'Galon',         'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 2,  'satuan' => 'buah'],
            ['nama_barang' => 'Senter',        'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 6,  'satuan' => 'buah'],
            ['nama_barang' => 'Tali Pramuka',  'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 1,  'satuan' => 'buah'],
            ['nama_barang' => 'Kardus',        'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 2,  'satuan' => 'buah'],
            ['nama_barang' => 'Tiker',         'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 4,  'satuan' => 'buah'],
            ['nama_barang' => 'Minyak Tanah',  'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 1,  'satuan' => 'liter'],
            ['nama_barang' => 'Trash Bag 60×100', 'tipe' => 'kelompok', 'menu' => 'logistik', 'jumlah_kebutuhan' => 1, 'satuan' => 'pack'],

            // ── KONSUMSI – KELOMPOK ──────────────────────────────────────────
            ['nama_barang' => 'Gelas Plastik', 'tipe' => 'kelompok', 'menu' => 'konsumsi', 'jumlah_kebutuhan' => 1, 'satuan' => 'pack'],
            ['nama_barang' => 'Air Mineral 1500mL', 'tipe' => 'kelompok', 'menu' => 'konsumsi', 'jumlah_kebutuhan' => 2, 'satuan' => 'buah'],

            // ── KONSUMSI – INDIVIDU ──────────────────────────────────────────
            ['nama_barang' => 'Madurasa',           'tipe' => 'individu', 'menu' => 'konsumsi', 'jumlah_kebutuhan' => 3, 'satuan' => 'sachet'],
            ['nama_barang' => 'Wedang Jahe',        'tipe' => 'individu', 'menu' => 'konsumsi', 'jumlah_kebutuhan' => 1, 'satuan' => 'sachet'],
            ['nama_barang' => 'Susu Kental Manis',  'tipe' => 'individu', 'menu' => 'konsumsi', 'jumlah_kebutuhan' => 1, 'satuan' => 'sachet'],
            ['nama_barang' => 'Roti Aoka',          'tipe' => 'individu', 'menu' => 'konsumsi', 'jumlah_kebutuhan' => 4, 'satuan' => 'bungkus'],

            // ── P3K – KELOMPOK ───────────────────────────────────────────────
            ['nama_barang' => 'Kayu Putih 60ml',    'tipe' => 'kelompok', 'menu' => 'p3k', 'jumlah_kebutuhan' => 1, 'satuan' => 'botol'],
            ['nama_barang' => 'Promaag',             'tipe' => 'kelompok', 'menu' => 'p3k', 'jumlah_kebutuhan' => 1, 'satuan' => 'strip'],
            ['nama_barang' => 'Salonpas',            'tipe' => 'kelompok', 'menu' => 'p3k', 'jumlah_kebutuhan' => 1, 'satuan' => 'bungkus'],
            ['nama_barang' => 'Hot in Cream 60ml',  'tipe' => 'kelompok', 'menu' => 'p3k', 'jumlah_kebutuhan' => 1, 'satuan' => 'buah'],

            // ── P3K – INDIVIDU ───────────────────────────────────────────────
            ['nama_barang' => 'Tolak Angin',         'tipe' => 'individu', 'menu' => 'p3k', 'jumlah_kebutuhan' => 3, 'satuan' => 'sachet'],
        ];

        foreach ($barangs as $item) {
            DB::table('p3k_barang_kebutuhan')->insertOrIgnore(array_merge($item, [
                'keterangan' => null,
                'aktif'      => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
