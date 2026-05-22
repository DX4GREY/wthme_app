<?php

namespace Database\Seeders;

use App\Models\LinkResource;
use Illuminate\Database\Seeder;

class LinkResourceSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            ['nama' => 'Home Drive Event', 'url' => 'https://drive.google.com/...', 'ikon' => 'folder', 'untuk' => 'panitia', 'urutan' => 1],
            ['nama' => 'Gantt Chart Event', 'url' => 'https://drive.google.com/...', 'ikon' => 'chart-bar', 'untuk' => 'panitia', 'urutan' => 2],
            ['nama' => 'RAB Event', 'url' => 'https://drive.google.com/...', 'ikon' => 'currency-dollar', 'untuk' => 'panitia', 'urutan' => 3],
        ];

        foreach ($links as $link) {
            LinkResource::create($link);
        }
    }
}