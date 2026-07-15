@extends('layouts.app')

@section('content')
    <style>
        /* Animasi Fade In untuk Card */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Container Utama Slider */
        .slider-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        /* Wrapper untuk menggeser konten */
        .slider-wrapper {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Tiap item pengumuman */
        .slide-item {
            min-width: 100%;
            padding: 25px;
            box-sizing: border-box;
        }

        /* Titik navigasi */
        .dot-container {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(0, 47, 69, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            width: 20px;
            background: #002f45;
            border-radius: 10px;
        }
    </style>

    {{-- Background Wrapper --}}
    <div
        style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">

        {{-- BANNER IMPERSONASI ADMIN --}}
        @if (session()->has('impersonator_id'))
        <div style="max-width:800px; margin:0 auto 1.5rem auto;">
            <div style="background:#002f45; border-radius:1rem; padding:0.75rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:0.75rem; box-shadow:0 4px 15px rgba(0,47,69,0.15);">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <span style="font-size:1.25rem;">🛡️</span>
                    <div>
                        <span style="color:#d2c296; font-weight:700; font-size:0.85rem;">Mode Impersonasi Admin</span>
                        <span style="color:#bdd1d3; font-size:0.75rem; display:block;">Anda melihat portal sebagai <strong style="color:white;">{{ auth()->user()->name }}</strong> ({{ auth()->user()->nim }})</span>
                    </div>
                </div>
                <form action="{{ route('impersonasi.leave') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" style="background:#ef4444; color:white; border:none; padding:0.5rem 1rem; border-radius:0.75rem; font-size:0.8rem; font-weight:700; cursor:pointer; transition:0.3s; white-space:nowrap;"
                        onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        ✕ Keluar
                    </button>
                </form>
            </div>
        </div>
        @endif
        <div style="max-width:800px; margin:0 auto;">

            {{-- Header --}}
            <div style="margin-bottom:2.5rem; text-align: center;">
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Portal <span style="color:#6b705c; font-style:italic;">Peserta</span>
                </h1>
                <div
                    style="display: inline-block; padding: 0.5rem 1.5rem; background: rgba(255,255,255,0.3); backdrop-filter: blur(10px); border-radius: 2rem; border: 1px solid rgba(255,255,255,0.4); margin-top: 10px;">
                    <p style="color:#002f45; font-size:0.95rem; margin:0; font-weight: 500;">
                        Selamat Datang, <span style="font-weight: 700;">{{ auth()->user()->name }}</span> — Kelompok
                        {{ auth()->user()->kelompok }}
                    </p>
                </div>
            </div>

            {{-- 🏆 BOX TOTAL POIN & PERINGKAT PESERTA (SIMPEL & BULAT) 🏆 --}}
            <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 1.5rem; flex-wrap: wrap;">

                {{-- Box Skor XP --}}
                <div
                    style="display: inline-flex; align-items: center; gap: 10px; background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 0.5rem 1.5rem; border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 4px 20px rgba(0, 47, 69, 0.04);">
                    <span style="font-size: 1.15rem;">🏆</span>
                    <span
                        style="color: #002f45; font-size: 0.9rem; font-weight: 600; font-family: 'Inter', sans-serif; letter-spacing: -0.01em;">
                        Skor Anda:
                        <span
                            style="color: #fff; background: #6b705c; font-weight: 800; font-size: 0.95rem; padding: 3px 12px; border-radius: 20px; margin-left: 6px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                            {{ $totalPoin ?? 0 }} XP
                        </span>
                    </span>
                </div>

                {{-- Box Peringkat Global --}}
                <div
                    style="display: inline-flex; align-items: center; gap: 10px; background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 0.5rem 1.5rem; border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 4px 20px rgba(0, 47, 69, 0.04);">
                    <span style="font-size: 1.15rem;">🏅</span>
                    <span
                        style="color: #002f45; font-size: 0.9rem; font-weight: 600; font-family: 'Inter', sans-serif; letter-spacing: -0.01em;">
                        Peringkat Anda:
                        <span
                            style="color: #fff; background: #002f45; font-weight: 800; font-size: 0.95rem; padding: 3px 12px; border-radius: 20px; margin-left: 6px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                            #{{ $myRank ?? '-' }}
                        </span>
                    </span>
                </div>

            </div>

            {{-- SLIDER PENGUMUMAN DINAMIS --}}
            @if ($pengumuman->count() > 0)
                <div class="slider-container">
                    <div class="slider-wrapper" id="sliderWrapper">
                        @foreach ($pengumuman as $info)
                            <div class="slide-item">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <span
                                            style="font-size: 0.65rem; background: #002f45; color: white; padding: 4px 12px; border-radius: 50px; font-weight: 800; letter-spacing: 0.5px; display: inline-block; margin-bottom: 10px;">
                                            {{ strtoupper($info->kategori) }}
                                        </span>

                                        <h4
                                            style="margin: 0 0 12px 0; color: #002f45; font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700;">
                                            {{ $info->judul }}
                                        </h4>

                                        @if ($info->konten)
                                            <p
                                                style="color: #002f45; opacity: 0.8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 15px; font-family: 'Inter', sans-serif;">
                                                {! nl2br(e(Str::limit($info->konten, 200))) !}
                                            </p>
                                        @endif

                                        @if ($info->url_link)
                                            <a href="{{ $info->url_link }}" target="_blank"
                                                style="display: inline-flex; align-items: center; gap: 8px; background: #002f45; color: white; text-decoration: none; padding: 10px 18px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; transition: 0.3s; box-shadow: 0 4px 15px rgba(0, 47, 69, 0.2);"
                                                onmouseover="this.style.background='#d2c296'; this.style.color='#002f45';"
                                                onmouseout="this.style.background='#002f45'; this.style.color='white';">
                                                Buka Tautan <span style="font-size: 1rem;">↗</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Indikator Dots --}}
                    @if ($pengumuman->count() > 1)
                        <div class="dot-container">
                            @foreach ($pengumuman as $index => $info)
                                <div class="dot {{ $index == 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Grid Menu --}}
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:1.5rem;">
                @php
                    $menus = [
                        [
                            'route' => route('peserta.face.register'),
                            'icon' => '🤳',
                            'title' => 'Daftar Wajah',
                            'desc' => $user->face_registered
                                ? 'Wajah sudah terdaftar ✓'
                                : 'Belum daftar wajah untuk absensi',
                            'badge' => !$user->face_registered ? ['val' => 'Wajib', 'color' => '#ef4444'] : null,
                        ],
                        [
                            'route' => route('peserta.absen'),
                            'icon' => '📷',
                            'title' => 'Absensi Kegiatan',
                            'desc' => 'Scan QR Code untuk konfirmasi kehadiran',
                            'badge' => null,
                        ],
                        [
                            'route' => route('peserta.tugas'),
                            'icon' => '📚',
                            'title' => 'Pengumpulan Tugas',
                            'desc' =>
                                $tugasBelum > 0
                                    ? $tugasBelum . ' tugas belum dikumpulkan'
                                    : 'Semua tugas sudah dikumpulkan ✓',
                            'badge' => $tugasBelum > 0 ? ['val' => $tugasBelum, 'color' => '#ef4444'] : null,
                        ],
                        // [
                        //     'route' => route('peserta.barang'),
                        //     'icon' => '📦',
                        //     'title' => 'Pengumpulan Barang',
                        //     'desc' => 'Input & update barang bawaan kelompok',
                        //     'badge' => null,
                        // ],
                        [
                            'route' => route('peserta.p3k'),
                            'icon' => '📦',
                            'title' => 'Pengumpulan Barang Peserta',
                            'desc' => 'Pengumpulan barang Logistik, Konsumsi, P3K',
                            'badge' => null,
                        ],
                        [
                            'route' => route('peserta.riwayat'),
                            'icon' => '🏥',
                            'title' => 'Riwayat Kesehatan',
                            'desc' => 'Isi formulir kondisi kesehatan Anda',
                            'badge' => !$sudahIsiRiwayat ? ['val' => 'Wajib', 'color' => '#f59e0b'] : null,
                        ],
                        [
                            'route' => route('peserta.quest.index'),
                            'icon' => '🧭',
                            'title' => 'Quest Lab Elektro',
                            'desc' => 'Jelajahi 4 Lab Elektro, kumpulkan selfie & menangkan bonus hingga 200 XP!',
                            'badge' => null,
                        ],
                        [
                            'route' => route('peserta.kekeluargaan.index'),
                            'icon' => '👥',
                            'title' => 'Quest Kekeluargaan',
                            'desc' => 'Foto bersama rekan angkatan. Klaim +5 XP per teman!',
                            'badge' => null,
                        ],
                        // [
                        //     'route' => route('peserta.capture.index'),
                        //     'icon' => '📸',
                        //     'title' => 'Quest Capture Moment',
                        //     'desc' => 'Upload foto kebersamaan kelompok & menangkan hingga 200 XP!',
                        //     'badge' => null,
                        // ],
                        // [
                        //     'route' => route('peserta.meet.index'), // Silakan sesuaikan nama route menu barunya di sini
                        //     'icon' => '✨', // Silakan sesuaikan emoji icon sesuai keinginan
                        //     'title' => 'Quest Meet The KBM Elektro',
                        //     'desc' => 'Bertemu dan Foto Bersama KBM Elektro.',
                        //     'badge' => null, // Badge hijau penanda fitur baru (opsional, ganti null jika tidak dipakai)
                        // ],
                    ];
                @endphp

                @foreach ($menus as $menu)
                    <a href="{{ $menu['route'] }}"
                        style="text-decoration:none; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 1.75rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 1.25rem; position: relative; box-shadow: 0 8px 32px 0 rgba(0, 47, 69, 0.05);"
                        onmouseover="this.style.background='rgba(255, 255, 255, 0.4)'; this.style.transform='translateY(-5px)';"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.25)'; this.style.transform='translateY(0)';">
                        @if ($menu['badge'])
                            <span
                                style="position:absolute; top:1rem; right:1.25rem; background:{{ $menu['badge']['color'] }}; color:white; font-size:0.7rem; font-weight:700; padding:0.25rem 0.75rem; border-radius:999px;">
                                {{ $menu['badge']['val'] }}
                            </span>
                        @endif

                        <div
                            style="font-size: 2.2rem; background: rgba(255,255,255,0.5); width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; border-radius: 1rem;">
                            {{ $menu['icon'] }}
                        </div>

                        <div>
                            <h3 style="color:#002f45; font-weight:700; font-size:1.1rem; margin:0 0 0.25rem 0;">
                                {{ $menu['title'] }}</h3>
                            <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin:0;">{{ $menu['desc'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- 🌟 TOMBOL: Akses Balik ke Dashboard Pilihan Peran Utama 🌟 --}}
            <div style="margin-top: 2.5rem;">
                <a href="{{ url('/dashboard') }}"
                    style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 1.1rem; background: #002f45; border-radius: 1.25rem; text-decoration: none; color: #e0decd; font-weight: 600; font-size: 1rem; box-shadow: 0 4px 15px rgba(0,47,69,0.15); transition: all 0.3s ease;"
                    onmouseover="this.style.background='#001f2e'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'">
                    <span>🏠</span> Kembali ke Beranda Pilihan Portal Utama
                </a>
            </div>

            {{-- Footer Info --}}
            <div
                style="margin-top:1.5rem; background: rgba(0, 47, 69, 0.05); border-radius: 1.5rem; padding: 1.25rem 2rem; border: 1px solid rgba(0, 47, 69, 0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; gap: 2rem;">
                    <div>
                        <div
                            style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em;">
                            NIM</div>
                        <div style="color:#002f45; font-weight:700;">{{ auth()->user()->nim }}</div>
                    </div>
                    <div>
                        <div
                            style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em;">
                            Kelompok</div>
                        <div style="color:#002f45; font-weight:700;">{{ auth()->user()->kelompok }}</div>
                    </div>
                </div>
                <div style="color: #002f45; opacity: 0.6; font-style: italic; font-size: 0.8rem; font-weight: 700;">
                    ElektroJoss</div>
            </div>
        </div>
    </div>
    {{-- SCRIPTS --}}
    <script>
        let currentIndex = 0;
        const wrapper = document.getElementById('sliderWrapper');
        const dots = document.querySelectorAll('.dot');
        const slides = document.querySelectorAll('.slide-item');
        const totalSlides = slides.length;

        function updateSlider() {
            if (!wrapper) return;
            wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;

            // Update dots
            dots.forEach((dot, index) => {
                if (index === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }

        function goToSlide(index) {
            currentIndex = index;
            updateSlider();
        }

        // Jalankan Autoplay jika pengumuman > 1
        if (totalSlides > 1) {
            setInterval(nextSlide, 5000); // Geser tiap 5 detik
        }
    </script>
@endsection
