@extends('layouts.app')

@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Background Ambient --}}
    <div
        style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Inter', sans-serif;">
        <div style="max-width: 1000px; margin: 0 auto;">

            {{-- Header Section --}}
            <div style="text-align: center; margin-bottom: 4rem;">

                <span
                    style="display: inline-block; padding: 0.5rem 1.25rem; background: rgba(0,47,69,0.05); border-radius: 2rem; color: #002f45; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1rem;">
                    Management Dashboard
                </span>
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Portal <span style="color:#6b705c; font-style:italic;">Panitia</span>
                </h1>
                <p style="color: #002f45; opacity: 0.5; font-size: 1.1rem; margin-top: 0.75rem;">
                    Selalu semangat, {{ explode(' ', auth()->user()->name)[0] }}. Pantau dan kelola jalannya event di sini.
                </p>
            </div>

            {{-- Statistik Grid (Glassmorphism Style) --}}
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
                <div class="stat-glass">
                    <span class="stat-label">Peserta Hadir</span>
                    <span class="stat-value" style="margin-bottom: 0.5rem;">{{ $totalPesertaHadir }}</span>

                    {{-- Detail Gender & Rincian Kelompok Terfilter Sesi Aktif --}}
                    <div style="border-top: 1px solid rgba(0, 47, 69, 0.1); padding-top: 0.75rem; color: #002f45;">

                        {{-- Baris Akumulasi Total Gender Sesi Ini --}}
                        <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; margin-bottom: 0.75rem;">
                            <div>
                                <span style="opacity: 0.6;">Total L Hadir:</span>
                                <strong>{{ $pesertaHadirL }}</strong>
                            </div>
                            <div>
                                <span style="opacity: 0.6;">Total P Hadir:</span>
                                <strong>{{ $pesertaHadirP }}</strong>
                            </div>
                        </div>

                        {{-- Dropdown Toggle Detail Kelompok --}}
                        <div style="border-top: 1px solid rgba(0, 47, 69, 0.05); padding-top: 0.5rem;">
                            <button onclick="toggleDetailKelompok()" id="btn-toggle-kelompok"
                                style="background: none; border: none; padding: 0; color: #6b705c; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.25rem; width: 100%; transition: color 0.2s;">
                                <span>Rincian Hadir Per Kelompok</span>
                                <span id="arrow-kelompok"
                                    style="transition: transform 0.2s; display: inline-block;">▸</span>
                            </button>

                            {{-- Kontainer Dropdown Kelompok --}}
                            <div id="detail-kelompok-container"
                                style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, margin-top 0.3s ease-out; font-size: 0.8rem; display: flex; flex-direction: column; gap: 0.4rem;">

                                @if (isset($detailKelompok) && $detailKelompok->count() > 0)
                                    {{-- DI SINI DIUBAH: Menggunakan SORT_NATURAL agar urutan angkanya pas (1, 2, 3... 10) --}}
                                    @foreach (collect($detailKelompok)->sortBy('nama', SORT_NATURAL) as $kelompok)
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: center; background: rgba(0, 47, 69, 0.03); padding: 0.45rem 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(0,47,69,0.02);">
                                            <span style="font-weight: 600;">{{ $kelompok['nama'] }}</span>
                                            <div style="display: flex; gap: 0.75rem; opacity: 0.85;">
                                                <span>L: <strong>{{ $kelompok['L'] }}</strong></span>
                                                <span>P: <strong>{{ $kelompok['P'] }}</strong></span>
                                                <span
                                                    style="border-left: 1px solid rgba(0,47,69,0.15); padding-left: 0.5rem; color: #002f45; font-weight: 700;">
                                                    Total: {{ $kelompok['total'] }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="color: #6b705c; font-style: italic; padding: 0.5rem 0;">
                                        Belum ada data kelompok yang hadir di sesi ini.
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleDetailKelompok() {
                            const container = document.getElementById('detail-kelompok-container');
                            const arrow = document.getElementById('arrow-kelompok');
                            const btn = document.getElementById('btn-toggle-kelompok');

                            if (container.style.maxHeight === '0px' || !container.style.maxHeight) {
                                container.style.marginTop = '0.5rem';
                                container.style.maxHeight = container.scrollHeight + "px";
                                arrow.style.transform = 'rotate(90deg)';
                                btn.style.color = '#002f45';
                            } else {
                                container.style.maxHeight = '0px';
                                container.style.marginTop = '0px';
                                arrow.style.transform = 'rotate(0deg)';
                                btn.style.color = '#6b705c';
                            }
                        }
                    </script>
                </div>
                {{-- <div class="stat-glass">
                    <span class="stat-label">Panitia Hadir</span>
                    <span class="stat-value">{{ $totalPanitiaHadir }}</span>
                </div> --}}
            </div>

            <div class="main-layout-grid">
                {{-- Kolom Kiri: Menu Utama --}}
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    @php
                        $menus = [
                        [
                            'route' => 'panitia.suara.index',
                            'icon' => '📢',
                            'title' => 'Suara Peserta',
                            'desc' => 'Lihat saran, kritik & keluhan dari peserta',
                        ],
                            [
                                'route' => 'panitia.gantt.index',
                                'icon' => '📅',
                                'title' => 'Timeline & Gantt Chart',
                                'desc' => 'Pantau jadwal & planning event',
                                'role' => 'korlap',
                            ],
                            // [
                            //     'route' => 'panitia.absen',
                            //     'icon' => '📷',
                            //     'title' => 'Absen Saya',
                            //     'desc' => 'Scan QR kehadiran panitia',
                            // ],
                            [
                                'route' => 'panitia.absen.face.gate',
                                'icon' => '🤖',
                                'title' => 'Gate Absensi Wajah',
                                'desc' => 'Deteksi wajah peserta otomatis',
                            ],
                            [
                                'route' => 'panitia.qr.create',
                                'icon' => '🔲',
                                'title' => 'Generate QR Absensi',
                                'desc' => 'Buat QR sesi kegiatan baru',
                                'only_admin' => true,
                            ],
                            [
                                'route' => 'panitia.absensi.peserta',
                                'icon' => '📋',
                                'title' => 'Data Absensi Peserta',
                                'desc' => 'Rekap & export kehadiran peserta',
                            ],
                            // [
                            //     'route' => 'panitia.absensi.panitia',
                            //     'icon' => '📊',
                            //     'title' => 'Data Absensi Panitia',
                            //     'desc' => 'Rekap & export kehadiran panitia',
                            // ],
                            [
                                'route' => 'panitia.leaderboard.input',
                                'icon' => '✨',
                                'title' => 'Input Poin Peserta',
                                'desc' => 'Input poin absensi, tugas, and keaktifan leaderboard',
                                'restricted_to' => 'acara_and_admin',
                            ],
                            [
                                'route' => 'panitia.quest.index',
                                'icon' => '🧪',
                                'title' => 'Validasi Quest Lab',
                                'desc' => 'Cek dan setujui bukti selfie laboratorium peserta',
                                'restricted_to' => 'acara_and_admin',
                            ],
                            // 🟢 MENU BARU: VALIDASI SUBMISI MEET KBM (ACC/TOLAK BUKTI MEET)
                            [
                                'route' => 'panitia.meet.index',
                                'icon' => '👥',
                                'title' => 'Validasi Meet KBM',
                                'desc' => 'Antrean verifikasi acc/tolak bukti meet abang tingkat peserta',
                                'restricted_to' => 'acara_and_admin',
                            ],
                            [
                                'route' => 'panitia.kesehatan.index',
                                'icon' => '🩺',
                                'title' => 'Kesehatan Peserta',
                                'desc' => 'Pantau riwayat penyakit & alergi',
                            ],
                            [
                                'route' => 'panitia.p3k.index',
                                'icon' => '📦',
                                'title' => 'Pengumpulan Barang',
                                'desc' => 'Pantau status barang bawaan per kelompok',
                            ],
                            [
                                'route' => 'panitia.tugas.index',
                                'icon' => '📚',
                                'title' => 'Kelola Tugas Peserta',
                                'desc' => 'Buat & cek pengumpulan file',
                            ],
                            [
                                'route' => 'panitia.capture.index',
                                'icon' => '📚',
                                'title' => 'Kelola Capture Moment',
                                'desc' => 'Kelola capture moment peserta & panitia',
                            ],
                            [
                                'route' => 'panitia.info.peserta.index',
                                'icon' => '📢',
                                'title' => 'Broadcast Peserta',
                                'desc' => 'Kirim pengumuman/link ke portal peserta',
                            ],
                            // [
                            //     'route' => 'panitia.barang.index',
                            //     'icon' => '📦',
                            //     'title' => 'Pengumpulan Barang',
                            //     'desc' => 'Pantau status barang bawaan per kelompok',
                            // ],
                            // [
                            //     'route' => 'panitia.notulensi.index',
                            //     'icon' => '✍️',
                            //     'title' => 'Notulensi Rapat',
                            //     'desc' => 'Catat hasil & poin pembahasan',
                            // ],
                            [
                                'route' => 'panitia.mentoring.index',
                                'icon' => '🤝',
                                'title' => 'Sesi Mentoring',
                                'desc' => 'Kelola jadwal & progres mentoring',
                            ],
                        ];
                    @endphp

                    @foreach ($menus as $menu)
                        @php
                            $user = auth()->user();
                            $userRole = strtolower(trim($user->role ?? ''));
                            $userDivisi = strtolower(trim($user->divisi ?? ''));

                            $isAllowed = true;

                            // --- PINTU 1: CEK MENU BROADCAST ---
                            if ($menu['route'] === 'panitia.info.peserta.index') {
                                if ($userRole === 'admin' || $userDivisi === 'acara') {
                                    $isAllowed = true;
                                } else {
                                    $isAllowed = false;
                                }
                            }
                            // --- PINTU 2: CEK MENU AKSES TERBATAS (INPUT POIN, QUEST LAB, & MEET KBM) ---
                            elseif (isset($menu['restricted_to']) && $menu['restricted_to'] === 'acara_and_admin') {
                                if ($userRole === 'admin' || $userDivisi === 'acara') {
                                    $isAllowed = true;
                                } else {
                                    $isAllowed = false;
                                }
                            }
                            // --- PINTU 3: CEK MENU KHUSUS ADMIN LAINNYA ---
                            elseif (isset($menu['only_admin']) && $menu['only_admin'] === true) {
                                if ($userRole !== 'admin') {
                                    $isAllowed = false;
                                }
                            }
                        @endphp

                        {{-- Jika tidak diizinkan, bypass komponen render card --}}
                        @if (!$isAllowed)
                            @continue
                        @endif

                        <a href="{{ route($menu['route']) }}" class="action-card">
                            <div class="icon-box">{{ $menu['icon'] }}</div>
                            <div style="flex-grow: 1;">
                                <h4 class="card-title">{{ $menu['title'] }}</h4>
                                <p class="card-desc">{{ $menu['desc'] }}</p>
                            </div>
                        </a>
                    @endforeach

                    {{-- Special Bendahara Card  --}}
                    @if (auth()->user()->isPanitia())
                        <a href="{{ route('panitia.kas.index') }}" class="action-card bendahara-theme">
                            <div class="icon-box" style="background: rgba(210,194,150,0.2);">📒</div>
                            <div style="flex-grow: 1;">
                                <h4 class="card-title" style="color: #d2c296;">Kas Event</h4>
                                <p class="card-desc" style="color: #bdd1d3;">Catat arus keuangan kas</p>
                            </div>
                            <span class="role-badge"
                                style="background: rgba(210,194,150,0.2); color: #d2c296; border-color: rgba(210,194,150,0.3);">BENDAHARA</span>
                        </a>
                    @endif
                </div>

                {{-- Kolom Kanan: Sidebar --}}
                <div style="display: flex; flex-direction: column; gap: 2rem;">

                    {{-- Resources Glass Box --}}
                    <div class="sidebar-box">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h5 class="sidebar-title" style="margin:0;">DOKUMEN EVENT</h5>
                            <button onclick="document.getElementById('modalLink').style.display='flex'"
                                style="background: #002f45; color: white; border: none; border-radius: 5px; font-size: 0.6rem; padding: 2px 8px; cursor: pointer;">
                                + TAMBAH
                            </button>
                        </div>

                        {{-- List Link yang sudah ada --}}
                        @foreach ($links as $link)
                            <div style="position: relative;">
                                <a href="{{ $link->url }}" target="_blank" class="resource-link">
                                    <span style="font-size: 1.25rem;">
                                        @if ($link->ikon === 'folder')
                                            📁
                                        @elseif($link->ikon === 'chart-bar')
                                            📊
                                        @else
                                            🔗
                                        @endif
                                    </span>
                                    <div>
                                        <p class="res-name">{{ $link->nama }}</p>
                                        <p class="res-sub">Klik untuk akses ↗</p>
                                    </div>
                                </a>
                                {{-- Tombol Hapus --}}
                                <form action="{{ route('panitia.links.destroy', $link->id) }}" method="POST"
                                    style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%);">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        style="background:none; border:none; color:#ef4444; cursor:pointer; font-size: 0.8rem;"
                                        onclick="return confirm('Hapus link ini?')">🗑️</button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    {{-- Sederhana Modal untuk Input --}}
                    <div id="modalLink"
                        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding: 20px;">
                        <div style="background:white; padding:2rem; border-radius:1.5rem; width:100%; max-width:400px;">
                            <h3 style="margin-top:0; color:#002f45;">Tambah Link Baru</h3>
                            <form action="{{ route('panitia.links.store') }}" method="POST">
                                @csrf
                                <input type="text" name="nama" placeholder="Nama Dokumen (Contoh: Juklak Event)"
                                    required
                                    style="width:100%; padding:0.8rem; margin-bottom:1rem; border:1px solid #ddd; border-radius:0.5rem;">
                                <input type="url" name="url" placeholder="https://drive.google.com/..." required
                                    style="width:100%; padding:0.8rem; margin-bottom:1rem; border:1px solid #ddd; border-radius:0.5rem;">
                                <select name="ikon"
                                    style="width:100%; padding:0.8rem; margin-bottom:1rem; border:1px solid #ddd; border-radius:0.5rem;">
                                    <option value="link">🔗 Ikon Link Biasa</option>
                                    <option value="folder">📁 Ikon Folder Drive</option>
                                    <option value="chart-bar">📊 Ikon Spreadsheet/Chart</option>
                                </select>
                                <div style="display:flex; gap:10px;">
                                    <button type="submit"
                                        style="flex:1; background:#002f45; color:white; border:none; padding:0.8rem; border-radius:0.5rem; cursor:pointer;">Simpan</button>
                                    <button type="button"
                                        onclick="document.getElementById('modalLink').style.display='none'"
                                        style="flex:1; background:#eee; border:none; padding:0.8rem; border-radius:0.5rem; cursor:pointer;">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Active Sessions --}}
                    @if ($qrSessions->count() > 0)
                        <div class="sidebar-box">
                            <h5 class="sidebar-title">QR SESI TERAKHIR</h5>
                            @foreach ($qrSessions->take(3) as $qr)
                                <div class="qr-item">
                                    <div style="overflow: hidden;">
                                        <p class="qr-name">{{ $qr->nama_sesi }}</p>
                                        <p class="qr-sub">{{ ucfirst($qr->untuk) }} · {{ $qr->session_code }}</p>
                                    </div>
                                    <a href="{{ route('panitia.qr.show', $qr->session_code) }}" class="qr-btn">LIHAT</a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Grid Layout */
        .main-layout-grid {
            display: grid;
            grid-template-columns: 1.4fr 0.6fr;
            gap: 2.5rem;
        }

        /* Stat Cards */
        .stat-glass {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 1.5rem 2rem;
            border-radius: 2rem;
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #002f45;
            opacity: 0.6;
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: #002f45;
            margin-top: 0.25rem;
        }

        /* Action Cards */
        .action-card {
            text-decoration: none;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 1.5rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .action-card:hover {
            transform: translateY(-5px);
            background: white;
            box-shadow: 0 20px 40px rgba(0, 47, 69, 0.05);
            border-color: #002f45;
        }

        .bendahara-theme {
            background: #002f45 !important;
            border: none;
        }

        .bendahara-theme:hover {
            box-shadow: 0 20px 40px rgba(0, 47, 69, 0.3);
        }

        .icon-box {
            width: 52px;
            height: 52px;
            background: #e0decd;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .card-title {
            color: #002f45;
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
        }

        .card-desc {
            color: #002f45;
            opacity: 0.5;
            font-size: 0.85rem;
            margin: 2px 0 0 0;
        }

        .role-badge {
            font-size: 0.6rem;
            font-weight: 800;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            background: rgba(0, 47, 69, 0.05);
            color: #002f45;
            border: 1px solid rgba(0, 47, 69, 0.1);
        }

        /* Sidebar Boxes */
        .sidebar-box {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border-radius: 2rem;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .sidebar-title {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            color: #002f45;
            opacity: 0.4;
            margin-bottom: 1.5rem;
        }

        .resource-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 1.25rem;
            text-decoration: none;
            background: rgba(0, 47, 69, 0.03);
            transition: 0.3s;
            margin-bottom: 0.5rem;
        }

        .resource-link:hover {
            background: rgba(0, 47, 69, 0.08);
        }

        .res-name {
            color: #002f45;
            font-weight: 700;
            font-size: 0.9rem;
            margin: 0;
        }

        .res-sub {
            color: #002f45;
            opacity: 0.4;
            font-size: 0.75rem;
            margin: 0;
        }

        .qr-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 47, 69, 0.05);
        }

        .qr-name {
            color: #002f45;
            font-weight: 700;
            font-size: 0.85rem;
            margin: 0;
        }

        .qr-sub {
            color: #002f45;
            opacity: 0.4;
            font-size: 0.7rem;
            margin: 0;
        }

        .qr-btn {
            text-decoration: none;
            font-size: 0.65rem;
            font-weight: 800;
            background: #002f45;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            transition: 0.3s;
        }

        /* Responsive */
        @media (max-width: 850px) {
            .main-layout-grid {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 1.75rem;
            }
        }
    </style>

    {{-- Floating Back Button --}}
    <a href="/dashboard"
        style="position: fixed; bottom: 2rem; left: 2rem; z-index: 100; text-decoration: none; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); padding: 0.75rem 1.25rem; border-radius: 999px; border: 1px solid rgba(0, 47, 69, 0.1); color: #002f45; font-size: 0.85rem; font-weight: 700; box-shadow: 0 10px 25px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 0.5rem; transition: 0.3s;"
        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)';"
        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)';">
        <span>⬅️</span> Dashboard Utama
    </a>
@endsection
