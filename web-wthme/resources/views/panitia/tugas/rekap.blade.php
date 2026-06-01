@extends('layouts.app')

{{-- Menaruh custom scrollbar di tempat yang benar (head) --}}
@push('styles')
<style>
    /* Custom Scrollbar untuk Glassmorphism */
    .custom-scroll::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.05);
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: rgba(0, 47, 69, 0.2);
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 47, 69, 0.5);
    }
</style>
@endpush

@section('content')
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
    <div style="max-width:1400px; margin:0 auto;">

        {{-- Header Section --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <a href="{{ route('panitia.tugas.index') }}" style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span>←</span> Kembali ke Kelola Tugas
                </a>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.25rem; font-weight:800; margin:0;">📊 Rekap Pengumpulan</h1>
                <p style="color:#002f45; opacity:0.6; font-size:1rem; margin-top:0.25rem;">Pantau progres tugas peserta secara real-time</p>
            </div>
            
            <div style="display:flex; gap:1rem;">
                <a href="{{ route('panitia.tugas.export') }}"
                   style="padding:0.75rem 1.5rem; background:rgba(0, 47, 69, 0.9); backdrop-filter:blur(10px); color:#d2c296; border-radius:12px; text-decoration:none; font-size:0.9rem; font-weight:700; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:all 0.3s ease; display:flex; align-items:center; gap:0.5rem;">
                    ⬇ Export Excel
                </a>
            </div>
        </div>

        {{-- Statistik Cards (Glassmorphism) --}}
        @if($tugasList->isNotEmpty())
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1.25rem; margin-bottom:2.5rem;">
            @foreach($tugasList as $tugas)
                @php 
                    $stat = $statsPerTugas[$tugas->id] ?? ['sudah_kumpul' => 0, 'terlambat' => 0]; 
                    $total = $totalPeserta ?? \App\Models\User::where('role','peserta')->count();
                    $pct = $total > 0 ? min(100, round(($stat['sudah_kumpul'] / $total) * 100)) : 0;
                @endphp
                <div style="background:rgba(255, 255, 255, 0.7); backdrop-filter:blur(15px); border:1px solid rgba(255, 255, 255, 0.5); border-radius:1.25rem; padding:1.5rem; box-shadow:0 8px 32px rgba(0,0,0,0.05); transition:transform 0.3s ease;">
                    <div style="color:#002f45; font-size:0.85rem; font-weight:700; margin-bottom:1rem; line-height:1.4; height:2.5rem; overflow:hidden;">{{ $tugas->nama_tugas }}</div>
                    
                    <div style="display:flex; align-items:flex-end; gap:0.5rem; margin-bottom:0.75rem;">
                        <span style="color:#002f45; font-size:2rem; font-weight:800; line-height:1;">{{ $stat['sudah_kumpul'] }}</span>
                        <span style="color:#002f45; opacity:0.4; font-size:0.9rem; font-weight:600; padding-bottom:0.2rem;">/ {{ $total }} Peserta</span>
                    </div>

                    {{-- Fancy Progress Bar --}}
                    <div style="background:rgba(0,0,0,0.05); border-radius:999px; height:8px; margin-bottom:0.75rem; overflow:hidden;">
                        <div style="background:linear-gradient(90deg, #002f45, #004e72); height:100%; border-radius:999px; width:{{ $pct }}%; transition:width 1s ease-out;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:600;">
                        <span style="color:#16a34a;">{{ max(0, $stat['sudah_kumpul'] - $stat['terlambat']) }} Tepat Waktu</span>
                        @if($stat['terlambat'] > 0)
                            <span style="color:#dc2626;">{{ $stat['terlambat'] }} Terlambat</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Filter Section (Glassmorphism) --}}
        <div style="background:rgba(255, 255, 255, 0.4); backdrop-filter:blur(10px); border:1px solid rgba(255, 255, 255, 0.3); border-radius:1.25rem; padding:1.25rem; margin-bottom:2rem;">
            <form method="GET" action="{{ route('panitia.tugas.rekap') }}" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <label style="font-size:0.85rem; font-weight:700; color:#002f45;">Pilih Kelompok:</label>
                    <select name="kelompok" style="padding:0.6rem 1.25rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; font-size:0.9rem; color:#002f45; background:white; outline:none; min-width:180px; cursor:pointer;">
                        <option value="">Semua Kelompok</option>
                        @foreach($kelompokList as $k)
                            <option value="{{ $k }}" {{ $filterKelompok == $k ? 'selected' : '' }}>Kelompok {{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" style="padding:0.6rem 1.5rem; background:#002f45; color:white; border:none; border-radius:0.75rem; cursor:pointer; font-weight:600; transition:all 0.2s;">Filter</button>
                @if($filterKelompok)
                    <a href="{{ route('panitia.tugas.rekap') }}" style="color:#002f45; font-size:0.85rem; font-weight:600; text-decoration:underline;">Reset Filter</a>
                @endif
            </form>
        </div>

        {{-- Main Table Sections --}}
        @forelse($pesertaPerKelompok as $kelompok => $pesertaList)
            <div style="background:rgba(255, 255, 255, 0.6); backdrop-filter:blur(20px); border-radius:1.5rem; overflow:hidden; border:1px solid rgba(255, 255, 255, 0.5); box-shadow:0 10px 40px rgba(0,0,0,0.05); margin-bottom:3rem;">
                
                {{-- Kelompok Bar --}}
                <div style="background:rgba(0, 47, 69, 0.85); padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <span style="background:#d2c296; color:#002f45; padding:0.25rem 0.75rem; border-radius:0.5rem; font-weight:800; font-size:0.85rem;">Kelompok {{ $kelompok }}</span>
                        <span style="color:white; font-size:1.1rem; font-weight:600; font-family:'Playfair Display';">Daftar Pengumpulan Peserta</span>
                    </div>
                    <span style="color:rgba(255,255,255,0.7); font-size:0.85rem; font-weight:500;">{{ $pesertaList->count() }} Anggota</span>
                </div>

                {{-- Menambahkan class custom-scroll di pembungkus tabel --}}
                <div class="custom-scroll" style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:{{ 300 + ($tugasList->count() * 180) }}px;">
                        <thead>
                            <tr style="background:rgba(255,255,255,0.3);">
                                <th style="padding:1.25rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border-bottom:1px solid rgba(0,0,0,0.05);">Biodata Peserta</th>
                                @foreach($tugasList as $tugas)
                                    <th style="padding:1.25rem; text-align:center; border-left:1px solid rgba(0,0,0,0.03); border-bottom:1px solid rgba(0,0,0,0.05);">
                                        <div style="color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; margin-bottom:0.25rem;">{{ $tugas->nama_tugas }}</div>
                                        @if($tugas->deadline)
                                            <div style="font-weight:600; font-size:0.65rem; color:{{ $tugas->isTerlambat() ? '#dc2626' : '#b45309' }};">
                                                ⌛ {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M, H:i') }}
                                            </div>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesertaList as $peserta)
                                <tr style="transition:background 0.2s; border-bottom:1px solid rgba(0,0,0,0.03);" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:1.25rem;">
                                        <div style="color:#002f45; font-weight:700; font-size:1rem; margin-bottom:0.1rem;">{{ $peserta->name }}</div>
                                        <div style="color:#002f45; opacity:0.5; font-size:0.8rem; font-family:monospace; letter-spacing:1px;">{{ $peserta->nim }}</div>
                                    </td>

                                    @foreach($tugasList as $tugas)
                                        @php $p = $pengumpulanMap[$peserta->id][$tugas->id] ?? null; @endphp
                                        <td style="padding:1rem; text-align:center; border-left:1px solid rgba(0,0,0,0.03);">
                                            @if($p)
                                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:0.4rem; background:white; padding:0.75rem; border-radius:1rem; border:1px solid rgba(0,0,0,0.05); box-shadow:0 2px 8px rgba(0,0,0,0.02); min-width:140px;">
                                                    <span style="padding:0.2rem 0.6rem; border-radius:6px; font-size:0.65rem; font-weight:800; text-transform:uppercase;
                                                        {{ $p->status === 'tepat_waktu' ? 'background:#dcfce7; color:#166534;' : 'background:#fee2e2; color:#991b1b;' }}">
                                                        {{ $p->status === 'tepat_waktu' ? '✓ Tepat' : '⚠ Telat' }}
                                                    </span>
                                                    
                                                    <span style="font-size:0.7rem; color:#64748b; font-weight:600;">
                                                        {{ \Carbon\Carbon::parse($p->dikumpulkan_at)->format('d/m · H:i') }}
                                                    </span>

                                                    <div style="display:flex; align-items:center; gap:0.4rem; border-top:1px solid #f1f5f9; padding-top:0.4rem; width:100%; justify-content:center;">
                                                        {{-- Tombol Interaktif Baru untuk melihat multi-file via Modal --}}
                                                        <button type="button" 
                                                                onclick="openDetailModal('{{ $peserta->name }}', '{{ $tugas->nama_tugas }}', '{{ $p->id }}')"
                                                                style="background:#002f45; color:white; padding:0.25rem 0.6rem; border-radius:6px; border:none; font-size:0.7rem; font-weight:700; cursor:pointer; transition:transform 0.2s; display:flex; align-items:center; gap:0.2rem;"
                                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                            👁 Lihat File
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <div style="color:#cbd5e1; font-size:0.75rem; font-weight:600; letter-spacing:1px;">— BELUM —</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(0, 47, 69, 0.05);">
                                <td style="padding:1.25rem; color:#002f45; font-size:0.85rem; font-weight:800; text-transform:uppercase;">Ringkasan Kelompok</td>
                                @foreach($tugasList as $tugas)
                                    @php
                                        $count = $pesertaList->filter(fn($ps) => isset($pengumpulanMap[$ps->id][$tugas->id]))->count();
                                        $totalP = $pesertaList->count();
                                    @endphp
                                    <td style="padding:1.25rem; text-align:center; border-left:1px solid rgba(0,0,0,0.03);">
                                        <div style="font-size:1rem; font-weight:800; color:#002f45;">{{ $count }} <span style="font-size:0.75rem; opacity:0.4;">/ {{ $totalP }}</span></div>
                                        <div style="font-size:0.65rem; font-weight:700; color:{{ $count == $totalP ? '#16a34a' : '#64748b' }};">
                                            {{ $count == $totalP ? 'LENGKAP' : 'PENDING' }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div style="background:rgba(255,255,255,0.5); backdrop-filter:blur(10px); border-radius:1.5rem; padding:4rem; text-align:center; border:2px dashed rgba(0,0,0,0.1);">
                <div style="font-size:3rem; margin-bottom:1rem;">📭</div>
                <p style="color:#002f45; font-weight:600; font-size:1.1rem; opacity:0.5;">Belum ada data peserta atau tugas yang tersedia.</p>
            </div>
        @endforelse

    </div>
</div>

{{-- ==================== KOMPONEN MODAL DETAIL BERKAS (POP-UP) ==================== --}}
<div id="detailFileModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; transition: all 0.3s ease;">
    <div style="background:white; padding:2rem; border-radius:1.5rem; width:90%; max-width:500px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.15); border:1px solid rgba(0,0,0,0.05); position:relative;">
        
        {{-- Tombol Close X --}}
        <button onclick="closeDetailModal()" style="position:absolute; top:1.25rem; right:1.25rem; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8; transition:color 0.2s;" onmouseover="this.style.color='#002f45'" onmouseout="this.style.color='#94a3b8'">&times;</button>
        
        <h3 id="modalTitle" style="font-family:'Playfair Display',serif; color:#002f45; margin-top:0; margin-bottom:0.25rem; font-size:1.4rem; font-weight:800;">📁 Berkas Pengumpulan</h3>
        <p id="modalSubtitle" style="color:#64748b; font-size:0.85rem; margin-bottom:1.5rem; font-weight:600; letter-spacing: 0.3px;"></p>
        
        {{-- Container Tempat List File Dimuat --}}
        <div id="modalFileList" style="display:flex; flex-direction:column; gap:0.75rem; max-height:280px; overflow-y:auto; padding-right:0.25rem;" class="custom-scroll">
            </div>

        {{-- Opsi Action Button Bawah --}}
        <div style="margin-top:1.75rem; display:flex; gap:0.75rem;">
            <a id="downloadAllZip" href="#" style="flex:1.5; text-align:center; padding:0.75rem 1rem; background:#002f45; color:#d2c296; border-radius:12px; text-decoration:none; font-size:0.85rem; font-weight:700; display:flex; align-items:center; justify-content:center; gap:0.5rem; box-shadow:0 4px 12px rgba(0, 47, 69, 0.15);">
                📦 Download Zip (.zip)
            </a>
            <button onclick="closeDetailModal()" style="flex:1; padding:0.75rem 1rem; background:#f1f5f9; color:#475569; border-radius:12px; border:none; font-size:0.85rem; font-weight:700; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- LOGIC JAVASCRIPT MODAL & AJAX --}}
<script>
function openDetailModal(namaPeserta, namaTugas, pengumpulanId) {
    // 1. Set text identitas pengumpul di modal
    document.getElementById('modalSubtitle').innerText = namaPeserta + ' • ' + namaTugas;
    
    const container = document.getElementById('modalFileList');
    container.innerHTML = '<p style="font-size:0.85rem; color:#64748b; text-align:center; padding:2rem 0;">⏳ Mengambil data berkas...</p>';
    
    // 2. Set route dinamis untuk mendownload kolektif (.zip) pengumpulan ini
    document.getElementById('downloadAllZip').href = `/panitia/tugas/download-zip/${pengumpulanId}`;
    
    // 3. Tampilkan Pop-up Modal
    document.getElementById('detailFileModal').style.display = 'flex';

    // 4. Hit API Laravel untuk mengambil daftar file (menggunakan Fetch API)
    fetch(`/api/panitia/pengumpulan/${pengumpulanId}/files`)
        .then(res => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(data => {
            container.innerHTML = '';
            
            if(!data.files || data.files.length === 0) {
                container.innerHTML = '<p style="text-align:center; font-size:0.85rem; color:#94a3b8; padding:2rem 0;">Tidak ada file yang ditemukan.</p>';
                return;
            }
            
            // Render daftar file satu per satu ke dalam modal
            data.files.forEach(file => {
                container.innerHTML += `
                    <div style="display:flex; align-items:center; justify-content:space-between; background:#f8fafc; padding:0.75rem 1rem; border-radius:12px; border:1px solid #e2e8f0; gap:1rem;">
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:0.85rem; font-weight:700; color:#002f45; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="${file.nama_asli}">
                                ${file.nama_asli}
                            </div>
                            <div style="font-size:0.7rem; color:#94a3b8; font-weight:700; margin-top:0.15rem;">
                                ${file.ukuran ? file.ukuran : ''} • <span style="color:#002f45;">${file.ekstensi.toUpperCase()}</span>
                            </div>
                        </div>
                        <a href="/panitia/tugas/file/download/${file.id}" 
                           style="background:#002f45; color:white; padding:0.35rem 0.6rem; border-radius:8px; text-decoration:none; font-size:0.75rem; font-weight:bold; transition: background 0.2s;"
                           onmouseover="this.style.background='#004e72'" onmouseout="this.style.background='#002f45'">
                            ⬇
                        </a>
                    </div>
                `;
            });
        }).catch(() => {
            container.innerHTML = '<p style="text-align:center; font-size:0.85rem; color:#dc2626; padding:2rem 0;">⚠️ Gagal memuat berkas. Pastikan endpoint API sudah sesuai.</p>';
        });
}

function closeDetailModal() {
    document.getElementById('detailFileModal').style.display = 'none';
}

// Menutup modal otomatis jika panitia klik di luar kotak putih modal
window.onclick = function(event) {
    const modal = document.getElementById('detailFileModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
@endsection