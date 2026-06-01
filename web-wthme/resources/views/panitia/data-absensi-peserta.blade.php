@extends('layouts.app')

@section('content')
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1200px; margin:0 auto;">

            {{-- Header & Action Bar --}}
            <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; margin-bottom:1rem; transition:0.3s; font-weight:600;"
                        onmouseover="this.style.opacity='1'; this.style.transform='translateX(-5px)'"
                        onmouseout="this.style.opacity='0.7'; this.style.transform='translateX(0)'">
                        <span style="margin-right:8px;">←</span> Kembali ke Dashboard
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Rekap Matriks <span style="color:#6b705c; font-style:italic;">Kehadiran Global</span>
                    </h1>
                </div>

                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('panitia.export.peserta') }}"
                        style="padding:0.8rem 1.5rem; background: #002f45; color:#e0decd; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; display:inline-flex; align-items:center; gap:10px; box-shadow: 0 4px 15px rgba(0,47,69,0.2); transition: 0.3s;"
                        onmouseover="this.style.background='#6b705c'; this.style.color='white'" onmouseout="this.style.background='#002f45'; this.style.color='#e0decd'">
                        <span>📊</span> Download Rekap Excel (.xlsx)
                    </a>
                </div>
            </div>

            {{-- PANEL MATRIKS UTAMA --}}
            <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius: 2rem; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 20px 40px rgba(0,0,0,0.04);">
                
                {{-- Pembungkus Fitur Scroll Horizontal Otomatis jika Kolom Sesi Terlalu Banyak --}}
                <div style="overflow-x: auto; width: 100%;">
                    <table style="width:100%; border-collapse:collapse; min-width: 900px;">
                        <thead>
                            {{-- BARIS HEADER UTAMA TABEL (NAVY GELAP PREMIUM LIKE EXCEL) --}}
                            <tr style="background: #002f45;">
                                <th style="padding:1.25rem 1rem; text-align:center; color:white; font-size:0.75rem; font-weight:800; text-transform:uppercase; border: 1px solid rgba(0,0,0,0.15); width: 5px;">No</th>
                                <th style="padding:1.25rem 1.5rem; text-align:left; color:white; font-size:0.75rem; font-weight:800; text-transform:uppercase; border: 1px solid rgba(0,0,0,0.15); min-width: 200px;">Nama Lengkap Peserta</th>
                                <th style="padding:1.25rem 1rem; text-align:center; color:white; font-size:0.75rem; font-weight:800; text-transform:uppercase; border: 1px solid rgba(0,0,0,0.15); width: 90px;">NIM</th>
                                <th style="padding:1.25rem 1rem; text-align:center; color:white; font-size:0.75rem; font-weight:800; text-transform:uppercase; border: 1px solid rgba(0,0,0,0.15); width: 70px;">Angkatan</th>
                                
                                {{-- Judul Kolom Dinamis Bergeser Ke Kanan Mengikuti Jumlah Sesi --}}
                                @foreach($sesiList as $sesi)
                                    <th style="padding:1.25rem 1rem; text-align:center; color:#d2c296; font-size:0.7rem; font-weight:800; text-transform:uppercase; border: 1px solid rgba(0,0,0,0.15); min-width: 140px; line-height: 1.2;">
                                        {{ $sesi->nama_sesi }}
                                        <div style="font-size: 0.6rem; color: white; opacity: 0.6; font-weight: 400; margin-top: 3px;">{{ $sesi->created_at->format('d/m/Y') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            
                            {{-- LOOP LEVEL 1: MEMECAH GRUP KELOMPOK KEBAWAH --}}
                            @forelse($matrixData as $noKelompok => $daftarPeserta)
                                
                                {{-- BARIS SEKAT PEMBATAS KELOMPOK (Membelah tabel secara horizontal) --}}
                                <tr style="background: rgba(0, 47, 69, 0.08);">
                                    <td colspan="{{ 4 + $sesiList->count() }}" style="padding: 1rem 1.5rem; font-weight: 800; color: #002f45; font-size: 0.9rem; letter-spacing: 0.05em; border-bottom: 2px solid #002f45;">
                                        🌿 KELOMPOK {{ $noKelompok ?? 'TANPA KELOMPOK' }} 
                                        <span style="font-weight: 500; font-size: 0.75rem; opacity: 0.7; margin-left: 8px;">(Total {{ $daftarPeserta->count() }} Anggota)</span>
                                    </td>
                                </tr>

                                {{-- LOOP LEVEL 2: MENAMPILKAN DATA ANGGOTA KELOMPOK TERKAIT --}}
                                @foreach($daftarPeserta as $index => $user)
                                    <tr style="border-bottom:1px solid rgba(0,0,0,0.05); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.45)'" onmouseout="this.style.background='transparent'">
                                        
                                        {{-- No Urut yang mereset kembali dari 1 di setiap kelompok baru --}}
                                        <td style="padding:1rem; text-align:center; color:#002f45; opacity:0.6; font-family:monospace; font-weight:600; border-right: 1px solid rgba(0,0,0,0.02);">
                                            {{ $index + 1 }}
                                        </td>
                                        
                                        <td style="padding:1rem 1.5rem; color:#002f45; font-weight:700; font-size:0.9rem;">
                                            {{ $user->name }}
                                        </td>
                                        
                                        <td style="padding:1rem; text-align:center; color:#002f45; font-size:0.8rem; font-family:monospace; opacity:0.8;">
                                            {{ $user->nim }}
                                        </td>
                                        
                                        <td style="padding:1rem; text-align:center; color:#002f45; font-weight:600; font-size:0.85rem;">
                                            {{ $user->angkatan }}
                                        </td>

                                        {{-- LOOP LEVEL 3: MENGISI STATUS ABSENSI TIAP USER DI TIAP SESI SECARA HORIZONTAL --}}
                                        @foreach($sesiList as $sesi)
                                            @php
                                                // Cari record absensi spesifik milik user ini pada sesi ini di collection ram
                                                $log = $logAbsensi->get($user->id)?->get($sesi->id)?->first();
                                                
                                                // Penentuan default value
                                                $status = 'tidak_hadir';
                                                if ($log) {
                                                    $status = ($log->status === 'hadir' && !$log->waktu_absen) ? 'izin' : $log->status;
                                                }

                                                // Penentuan warna background pill dropdown pembaca awal
                                                $bgSelect = '#c53030'; // Merah
                                                if($status === 'hadir') $bgSelect = '#2f855a'; // Hijau
                                                if($status === 'izin') $bgSelect = '#ecc94b';  // Kuning
                                            @endphp

                                            <td style="padding:0.75rem 0.5rem; text-align:center; border-left: 1px solid rgba(0,0,0,0.02);">
                                                {{-- Dropdown Interaktif Warna-Warni Realtime Multi Sesi --}}
                                                <select class="status-select" 
                                                        data-user="{{ $user->id }}" 
                                                        data-session="{{ $sesi->id }}"
                                                        style="padding: 0.35rem 0.6rem; border-radius: 0.5rem; border: none; font-size: 0.75rem; font-weight: 700; color: white; cursor: pointer; outline: none; background-color: {{ $bgSelect }}; transition: 0.2s; width: 100%; max-width: 125px; text-align-last: center;">
                                                    <option value="hadir" {{ $status === 'hadir' ? 'selected' : '' }} style="background:#2f855a; color:white;">Hadir</option>
                                                    <option value="izin" {{ $status === 'izin' ? 'selected' : '' }} style="background:#ecc94b; color:#002f45;">Izin</option>
                                                    <option value="tidak_hadir" {{ $status === 'tidak_hadir' ? 'selected' : '' }} style="background:#c53030; color:white;">Alfa</option>
                                                </select>
                                            </td>
                                        @endforeach

                                    </tr>
                                @endforeach

                                {{-- Jarak Baris Kosong Pemisah Antar Kelompok Agar Nyaman Dibaca --}}
                                <tr style="height: 1.5rem;"><td colspan="{{ 4 + $sesiList->count() }}"></td></tr>

                            @empty
                                <tr>
                                    <td colspan="{{ 4 + $sesiList->count() }}" style="text-align: center; padding: 5rem 2rem;">
                                        <div style="font-size:4rem; margin-bottom:1rem;">👥</div>
                                        <h3 style="color:#002f45; font-weight:700; margin:0;">Tidak ada master data peserta terdaftar.</h3>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- AJAX MASTER CODES: MENANGKAP INPUT DARI DROPDOWN MANAPUN SECARA REALTIME TANPA RELOAD --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selects = document.querySelectorAll('.status-select');
            
            selects.forEach(select => {
                select.addEventListener('change', function () {
                    const status = this.value;
                    const userId = this.getAttribute('data-user');
                    const sessionId = this.getAttribute('data-session');

                    // Ganti warna background element select secara realtime di layar mengikuti opsi yang baru dipilih
                    if (status === 'hadir') {
                        this.style.backgroundColor = '#2f855a';
                    } else if (status === 'izin') {
                        this.style.backgroundColor = '#ecc94b';
                    } else {
                        this.style.backgroundColor = '#c53030';
                    }

                    // Tembak API via Fetch AJAX Laravel ke Database
                    fetch("{{ route('panitia.absensi.updateStatus') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            qr_session_id: sessionId,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Gagal memperbarui status absensi, silakan coba lagi.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan jaringan.');
                    });
                });
            });
        });
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,800;1,800&display=swap');

        /* Custom Desain Scrollbar Minimalis di Bagian Bawah Tabel */
        ::-webkit-scrollbar { width: 6px; height: 7px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.02); }
        ::-webkit-scrollbar-thumb { background: rgba(0, 47, 69, 0.25); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 47, 69, 0.4); }
    </style>
@endsection