@extends('layouts.app')

@section('content')
    <div
        style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1100px; margin:0 auto;">

            {{-- Header & Action Bar --}}

            <div
                style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; margin-bottom:1rem; transition:0.3s; font-weight:600;"
                        onmouseover="this.style.opacity='1'; this.style.transform='translateX(-5px)'"
                        onmouseout="this.style.opacity='0.7'; this.style.transform='translateX(0)'">
                        <span style="margin-right:8px;">←</span> Kembali ke Dashboard
                    </a>
                    <h1
                        style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Rekap <span style="color:#6b705c; font-style:italic;">Kehadiran</span>
                    </h1>
                    {{-- TOMBOL INI AKAN MUNCUL HANYA DI HALAMAN UTAMA (SAAT BELUM PILIH FOLDER) --}}
                    @if (!request('session_id'))
                        <div style="margin-top: 1.5rem;">
                            <a href="{{ route('panitia.export.peserta') }}"
                                style="padding:0.8rem 1.5rem; background: #6b705c; color:white; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; display:inline-flex; align-items:center; gap:10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.3s;"
                                onmouseover="this.style.background='#002f45'" onmouseout="this.style.background='#6b705c'">
                                <span>📊</span> Download Rekap Seluruh Sesi (.xlsx)
                            </a>
                        </div>
                    @endif
                </div>

                @if (request('session_id'))
                    <div style="display: flex; gap: 10px;">
                        <a href="{{ route('panitia.absensi.peserta') }}"
                            style="padding:0.8rem 1.5rem; background: rgba(255, 255, 255, 0.5); color:#002f45; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; transition:0.3s; border: 1px solid rgba(0,0,0,0.1);">
                            📂 Pilih Sesi Lain
                        </a>
                        <a href="{{ route('panitia.export.peserta', ['session_id' => request('session_id')]) }}"
                            style="padding:0.8rem 1.5rem; background: rgba(0, 47, 69, 0.9); color:#d2c296; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; backdrop-filter: blur(10px); transition:0.3s; display:flex; align-items:center; gap:10px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                            <span style="font-size: 1.1rem;">⬇</span> Export Excel
                        </a>
                    </div>
                @endif
            </div>

            {{-- TAMPILAN 1: PILIH SESI (FOLDER) --}}
            {{-- Di sini variabel $qr_sessions diganti menjadi $sesiList sesuai Controller --}}
            @if (!request('session_id'))
                <div
                    style="background: rgba(255,255,255,0.2); padding: 2rem; border-radius: 2rem; border: 1px solid rgba(255,255,255,0.4);">
                    <h3 style="color:#002f45; margin-bottom: 2rem; font-weight: 700;">Silahkan Pilih Sesi Absensi:</h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                        @forelse($sesiList as $sesi)
                            <a href="{{ request()->fullUrlWithQuery(['session_id' => $sesi->id]) }}"
                                style="text-decoration: none; transition: 0.3s;"
                                onmouseover="this.querySelector('.folder-card').style.transform='translateY(-10px)'; this.querySelector('.folder-card').style.background='rgba(255,255,255,0.8)'"
                                onmouseout="this.querySelector('.folder-card').style.transform='translateY(0)'; this.querySelector('.folder-card').style.background='rgba(255,255,255,0.5)'">

                                <div class="folder-card"
                                    style="background: rgba(255,255,255,0.5); padding: 2rem; border-radius: 1.5rem; border: 1px solid rgba(255,255,255,0.6); text-align: center; transition: 0.3s;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">📁</div>
                                    <h4 style="color:#002f45; margin:0; font-size: 1.2rem; font-weight: 800;">
                                        {{ $sesi->nama_sesi }}</h4>
                                    <p style="color:#6b705c; font-size: 0.85rem; margin-top: 0.5rem; font-weight: 600;">
                                        {{ $sesi->created_at->format('d M Y') }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                                <p style="color: #002f45; opacity: 0.6;">Belum ada sesi absensi yang dibuat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- TAMPILAN 2: DETAIL DATA ABSENSI --}}
            @else
                @php
                    // Kita ambil sesi yang dipilih dari variabel $sesiList
                    $selectedSesi = $sesiList->where('id', request('session_id'))->first();

                    // Karena Controller sudah melakukan grouping di awal, kita perlu meratakan data
                    // lalu memfilternya berdasarkan session_id yang dipilih user
                    $allData = $absensi->flatten();
                    $dataFiltered = $allData->where('qr_session_id', request('session_id'));
                    $groupedByKelompok = $dataFiltered->groupBy('kelompok');
                @endphp

                <div
                    style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(0,47,69,0.05); border-radius: 1rem; border-left: 5px solid #002f45;">
                    <p style="margin:0; font-size: 0.9rem; color: #002f45; opacity: 0.7;">Menampilkan data untuk:</p>
                    <h2 style="margin:0; color: #002f45; font-family: 'Playfair Display';">
                        {{ $selectedSesi->nama_sesi ?? 'Sesi Detail' }}</h2>
                </div>

                @if ($dataFiltered->isEmpty())
                    <div
                        style="background: rgba(255, 255, 255, 0.3); border-radius: 2rem; padding: 5rem 2rem; text-align: center; border: 1px solid rgba(255, 255, 255, 0.4);">
                        <div style="font-size:5rem; margin-bottom:1.5rem;">📋</div>
                        <h3 style="color:#002f45; font-weight:700;">Tidak ada peserta yang absen di sesi ini</h3>
                    </div>
                @else
                    @foreach ($groupedByKelompok as $noKelompok => $daftarPeserta)
                        <div
                            style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius: 1.5rem; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.4); margin-bottom: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">

                            <div
                                style="background: rgba(0, 47, 69, 0.85); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div
                                        style="width:10px; height:10px; background:#d2c296; border-radius:2px; transform: rotate(45deg);">
                                    </div>
                                    <span
                                        style="color:#d2c296; font-weight:800; letter-spacing: 0.1em; font-size:0.8rem; text-transform:uppercase;">KELOMPOK
                                        {{ $noKelompok ?? 'N/A' }}</span>
                                </div>
                                <span
                                    style="color:white; background:rgba(255,255,255,0.15); padding:0.3rem 0.8rem; border-radius:2rem; font-size:0.7rem; font-weight:600;">
                                    {{ $daftarPeserta->count() }} Peserta Hadir
                                </span>
                            </div>

                            <div style="overflow-x: auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background: rgba(255, 255, 255, 0.1);">
                                            <th
                                                style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                                No</th>
                                            <th
                                                style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                                Identitas Peserta</th>
                                            <th
                                                style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                                Angkatan</th>
                                            <th
                                                style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                                                Waktu Presensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($daftarPeserta as $i => $absen)
                                            <tr style="border-bottom:1px solid rgba(0,0,0,0.03); transition: 0.2s;"
                                                onmouseover="this.style.background='rgba(255,255,255,0.4)'"
                                                onmouseout="this.style.background='transparent'">
                                                <td
                                                    style="padding:1.25rem 1.5rem; color:#002f45; opacity:0.5; font-family:monospace; font-weight:600;">
                                                    {{ sprintf('%02d', $i + 1) }}</td>
                                                <td style="padding:1.25rem 1.5rem;">
                                                    <div style="color:#002f45; font-weight:700; font-size:0.95rem;">
                                                        {{ $absen->nama }}</div>
                                                    <div
                                                        style="color:#002f45; font-size:0.8rem; opacity:0.6; font-family:monospace;">
                                                        {{ $absen->nim }}</div>
                                                </td>
                                                <td
                                                    style="padding:1.25rem 1.5rem; color:#002f45; font-weight:600; font-size:0.85rem;">
                                                    {{ $absen->angkatan }}</td>
                                                <td style="padding:1.25rem 1.5rem;">
                                                    <div style="color:#002f45; font-size:0.8rem; font-weight:600;">
                                                        <span style="opacity:0.5; margin-right:4px;">⏰</span>
                                                        {{ \Carbon\Carbon::parse($absen->waktu_absen)->format('H:i') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endif
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,800;1,800&display=swap');

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 47, 69, 0.2);
            border-radius: 10px;
        }
    </style>
@endsection
