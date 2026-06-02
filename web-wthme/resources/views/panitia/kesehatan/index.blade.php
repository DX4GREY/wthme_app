@extends('layouts.app')

@section('content')
    {{-- Main Background --}}
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #d2c296 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1400px; margin:0 auto;">

            {{-- Header & Action Bar --}}
            <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; margin-bottom:1rem; transition:0.3s;"
                        onmouseover="this.style.opacity='1'; this.style.transform='translateX(-5px)'"
                        onmouseout="this.style.opacity='0.7'; this.style.transform='translateX(0)'">
                        <span style="margin-right:8px;">←</span> Kembali
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Data Medis & Kesehatan <span style="color:#6b705c; font-style:italic;">Peserta</span>
                    </h1>
                </div>

                <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                    {{-- Form Filter Kelompok & Pita --}}
                    <form action="{{ route('panitia.kesehatan.index') }}" method="GET" style="display:flex; gap:0.5rem; background: rgba(255, 255, 255, 0.2); padding: 0.5rem; border-radius: 1rem; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3);">
                        <select name="kelompok" style="padding:0.6rem 1rem; border-radius:0.75rem; border:none; font-size:0.875rem; background:white; color:#002f45; outline:none; cursor:pointer;">
                            <option value="">Semua Kelompok</option>
                            @foreach ($kelompokList as $k)
                                <option value="{{ $k }}" {{ request('kelompok') == $k ? 'selected' : '' }}>Kelompok {{ $k }}</option>
                            @endforeach
                        </select>

                        {{-- Filter Berdasarkan Warna Pita --}}
                        <select name="pita" style="padding:0.6rem 1rem; border-radius:0.75rem; border:none; font-size:0.875rem; background:white; color:#002f45; outline:none; cursor:pointer;">
                            <option value="">Semua Kondisi</option>
                            <option value="Tanpa Pita" {{ request('pita') == 'Tanpa Pita' ? 'selected' : '' }}>🟢 Normal (Tanpa Pita)</option>
                            <option value="Kuning" {{ request('pita') == 'Kuning' ? 'selected' : '' }}>🟡 Pita Kuning (Sakit Ringan/Sedang)</option>
                            <option value="Merah" {{ request('pita') == 'Merah' ? 'selected' : '' }}>🔴 Pita Merah (Penyakit Serius)</option>
                        </select>

                        <button type="submit" style="background:#002f45; color:white; border:none; padding:0.6rem 1.2rem; border-radius:0.75rem; cursor:pointer; font-weight:600; font-size:0.875rem;">
                            Filter
                        </button>
                    </form>

                    <a href="{{ route('panitia.export.kesehatan') }}"
                        style="padding:0.8rem 1.5rem; background: rgba(0, 47, 69, 0.9); color:#d2c296; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; backdrop-filter: blur(10px); transition:0.3s; display:flex; align-items:center; gap:8px;">
                        <span>⬇</span> Export Excel
                    </a>
                </div>
            </div>

            @if ($semuaRiwayat->isEmpty())
                <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(20px); border-radius: 2rem; padding: 5rem 2rem; text-align: center; border: 1px solid rgba(255, 255, 255, 0.3);">
                    <div style="font-size:5rem; margin-bottom:1.5rem;">🩺</div>
                    <h3 style="color:#002f45;">Data Riwayat Medis Belum Tersedia</h3>
                </div>
            @else
                @foreach ($semuaRiwayat as $kelompok => $dataKesehatan)
                    <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius: 1.5rem; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.4); margin-bottom: 2.5rem; box-shadow: 0 15px 35px rgba(0,0,0,0.05);">
                        
                        <div style="background: rgba(0, 47, 69, 0.85); padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color:#d2c296; font-weight:800; letter-spacing: 0.1em; font-size:0.9rem;">KELOMPOK {{ $kelompok }}</span>
                            <span style="color:white; background:rgba(255,255,255,0.15); padding:0.4rem 1rem; border-radius:2rem; font-size:0.8rem;">
                                {{ $dataKesehatan->count() }} Peserta Mengisi
                            </span>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width:100%; border-collapse:collapse; min-width: 1400px;">
                                <thead>
                                    <tr style="background: rgba(255, 255, 255, 0.1);">
                                        <th style="width: 16%; padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; border-bottom: 1px solid rgba(0,0,0,0.05);">Nama & NIM</th>
                                        <th style="width: 17%; padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Kontak & Alamat</th>
                                        <th style="width: 15%; padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Riwayat Alergi/Penyakit</th>
                                        <th style="width: 13%; padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Konsumsi Obat</th>
                                        <th style="width: 13%; padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Riwayat Cedera</th>
                                        <th style="width: 13%; padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Alergi Makanan</th>
                                        <th style="width: 11%; padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Info Tambahan</th>
                                        <th style="width: 5%; padding:1.25rem 1.5rem; text-align:center; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataKesehatan as $r)
                                        <tr style="border-bottom:1px solid rgba(0,0,0,0.03); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='transparent'">
                                            
                                            {{-- Nama, NIM, dan Indikator Silinder Blok Warna Murni --}}
                                            <td style="padding:1.25rem 1.5rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    
                                                    {{-- LOGIK CEK AKSES: Ambil data user yang sedang login saat ini --}}
                                                    @php
                                                        $canUpdate = false;
                                                        if (Auth::check()) {
                                                            $user = Auth::user();
                                                            // Sesuaikan nama kolom database Anda (misal kolom 'role' dan kolom 'divisi')
                                                            if ($user->role === 'admin' || $user->divisi === 'P3K') {
                                                                $canUpdate = true;
                                                            }
                                                        }
                                                    @endphp

                                                    {{-- Dropdown Silinder Blok Warna Polos dengan Hak Akses Terproteksi --}}
                                                    <select onchange="updatePita(this, {{ $r->id }})" 
                                                            title="{{ $canUpdate ? 'Klasifikasi Pita Kesehatan' : 'Anda tidak memiliki akses mengubah status medis' }}"
                                                            {{ !$canUpdate ? 'disabled' : '' }}
                                                            style="padding: 0; border-radius: 50px; width: 32px; height: 20px; border: 1px solid rgba(0,47,69,0.3); outline: none; transition: 0.2s; -webkit-appearance: none; -moz-appearance: none; appearance: none; display: inline-flex; align-items: center; justify-content: center; color: transparent; font-size: 0px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
                                                            cursor: {{ $canUpdate ? 'pointer' : 'not-allowed' }};
                                                            opacity: {{ $canUpdate ? '1' : '0.85' }};
                                                            background-color: {{ $r->warna_pita == 'Merah' ? '#ffccd5' : ($r->warna_pita == 'Kuning' ? '#fef3c7' : '#e2e8f0') }};">
                                                        <option value="" style="background:#white; color:#4a5568; font-size: 0.85rem;" {{ json_encode($r->warna_pita) == 'null' || $r->warna_pita == '' ? 'selected' : '' }}>🟢 Normal (STD)</option>
                                                        <option value="Kuning" style="background:#white; color:#92400e; font-size: 0.85rem;" {{ $r->warna_pita == 'Kuning' ? 'selected' : '' }}>🟡 Ringan/Sedang (KNG)</option>
                                                        <option value="Merah" style="background:#white; color:#c53030; font-size: 0.85rem;" {{ $r->warna_pita == 'Merah' ? 'selected' : '' }}>🔴 Serius/Kronis (MRH)</option>
                                                    </select>

                                                    {{-- Nama Peserta --}}
                                                    <div style="color:#002f45; font-weight:700; font-size:0.9rem;">{{ $r->nama }}</div>
                                                </div>
                                                
                                                {{-- NIM --}}
                                                <div style="color:#002f45; font-size:0.75rem; opacity:0.6; font-family:monospace; margin-top: 4px; margin-left: 44px;">{{ $r->nim }}</div>
                                            </td>

                                            {{-- Kontak & Alamat --}}
                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.8rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal;">
                                                <div><strong>HP:</strong> <span style="font-weight: 400;">{{ $r->no_telp ?? '-' }}</span></div>
                                                <div style="margin-top:3px;"><strong>Ortu:</strong> <span style="font-weight: 400;">{{ $r->no_telp_ortu ?? '-' }}</span></div>
                                                <div style="margin-top:6px; font-size:0.75rem; opacity:0.8; line-height:1.4; font-weight: 400;">{{ $r->alamat_rumah ?? '-' }}</div>
                                            </td>

                                            {{-- Riwayat Penyakit --}}
                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.8rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal; line-height:1.4; font-weight: 400;">
                                                {{ $r->riwayat_penyakit ?? '-' }}
                                            </td>

                                            {{-- Obat Rutin --}}
                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.8rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal; line-height:1.4; font-weight: 400;">
                                                {{ $r->obat_rutin ?? '-' }}
                                            </td>

                                            {{-- Riwayat Cedera --}}
                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.8rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal; line-height:1.4; font-weight: 400;">
                                                {{ $r->riwayat_cedera ?? '-' }}
                                            </td>

                                            {{-- Alergi Makanan --}}
                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.8rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal; line-height:1.4; font-weight: 400;">
                                                {{ $r->alergi_makanan ?? '-' }}
                                            </td>

                                            {{-- Catatan Tambahan --}}
                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.8rem; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal; line-height:1.4; font-weight: 400;">
                                                @if ($r->keterangan_tambahan && $r->keterangan_tambahan != '-')
                                                    {{ $r->keterangan_tambahan }}
                                                @else
                                                    <span style="color:#aaa;">-</span>
                                                @endif
                                            </td>

                                            {{-- Berkas Bukti File --}}
                                            <td style="padding:1.25rem 1.5rem; text-align:center; vertical-align: top;">
                                                @if($r->bukti_kesehatan)
                                                    <a href="{{ asset('storage/' . $r->bukti_kesehatan) }}" target="_blank" 
                                                       style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#002f45; color:#d2c296; border-radius:0.5rem; text-decoration:none; font-weight:bold; box-shadow:0 4px 10px rgba(0,47,69,0.2); transition:0.2s;"
                                                       title="Lihat Berkas Bukti" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                        📷
                                                    </a>
                                                @else
                                                    <span style="color:#ccc; font-size:0.85rem; font-weight: 400;">Tidak Ada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>

    {{-- Script AJAX Real-Time Update --}}
    <script>
    function updatePita(selectElement, id) {
        const warna = selectElement.value;
        
        if (warna === 'Merah') {
            selectElement.style.backgroundColor = '#ffccd5';
        } else if (warna === 'Kuning') {
            selectElement.style.backgroundColor = '#fef3c7';
        } else {
            selectElement.style.backgroundColor = '#e2e8f0';
        }

        fetch(`/panitia/kesehatan/${id}/update-pita`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ warna_pita: warna })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memproses ke server.');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                alert('Gagal memperbarui status: ' + (data.message || 'Terjadi kesalahan.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal terhubung ke server. Periksa koneksi Anda.');
        });
    }
    </script>
@endsection