@extends('layouts.app')

@section('content')
<div class="panel-container">
    <div class="panel-wrapper">
        
        {{-- Header Panel --}}
        <div class="panel-header-zone">
            <div>
                <a href="{{ route('panitia.index') }}" class="btn-back">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Portal Panitia
                </a>
                <h1 class="panel-title">🎯 Keaktifan Forum</h1>
                <p class="panel-subtitle">Ketuk nama peserta di bawah untuk membagikan atau memotong poin skor.</p>
            </div>
        </div>

        {{-- Flash Session Notifications --}}
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">⚠️ {{ $errors->first() }}</div>
        @endif

        {{-- Tata Letak Utama (Grid Responsif) --}}
        <div class="main-layout-grid">
            
            {{-- BAGIAN KIRI: TABEL PEMILIHAN PESERTA --}}
            <div class="card card-left">
                <div class="sticky-search-box">
                    <input type="text" id="searchPeserta" placeholder="🔍 Cari nama peserta atau kelompok...">
                </div>

                <div class="scrollable-table-wrapper">
                    <table class="modern-table" id="tablePeserta">
                        <thead>
                            <tr>
                                <th>Nama Peserta (Ketuk untuk Pilih)</th>
                                <th style="text-align: center; width: 90px;">Kelompok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedPesertas = $pesertas->groupBy('kelompok');
                            @endphp

                            @forelse($groupedPesertas as $noKelompok => $daftarPeserta)
                                <tr class="group-row">
                                    <td colspan="2">
                                        🌿 KELOMPOK {{ $noKelompok ?? 'TANPA KELOMPOK' }}
                                    </td>
                                </tr>
                                @foreach($daftarPeserta as $p)
                                    <tr class="peserta-row" 
                                        data-id="{{ $p->id }}" 
                                        data-name="{{ $p->name }}"
                                        data-kelompok="{{ $p->kelompok ?? '-' }}">
                                        <td class="peserta-name-cell">
                                            <span class="avatar-dot"></span>
                                            {{ $p->name }}
                                        </td>
                                        <td class="peserta-group-cell">
                                            {{ $p->kelompok ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="2" class="empty-state">Tidak ada data peserta ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BAGIAN KANAN: FORM INPUT SKOR --}}
            <div id="form-skor-section" class="card card-right-drawer">
                <div class="drawer-header">
                    <div class="drawer-handle"></div>
                    <button type="button" id="closeDrawer" class="btn-close-drawer">✕ Close</button>
                </div>

                <h2 class="form-title">📝 Input Skor Poin</h2>
                
                <form action="{{ route('panitia.keaktifan.store') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="peserta_id" id="selected_peserta_id" required>

                    {{-- Card Preview Peserta Terpilih --}}
                    <div id="peserta_preview_card" class="preview-card-active">
                        <span id="preview_text">
                            ⚠️ Silakan pilih salah satu nama peserta terlebih dahulu.
                        </span>
                    </div>

                    <div class="form-group">
                        <label>Nominal Poin Skor</label>
                        {{-- SEKARANG MENGGUNAKAN DROPDOWN SESUAI REKUES --}}
                        <select name="poin" required>
                            <option value="" disabled selected>-- Pilih Nominal Poin --</option>
                            <option value="15">+15 (Sangat Aktif)</option>
                            <option value="10">+10 (Aktif)</option>
                        </select>
                        <small>*Pilih salah satu nominal poin keaktifan di atas.</small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan Aktivitas</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: Aktif menanggapi argumen pada sesi tanya jawab" required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" id="btnSubmitPoin" disabled class="btn-submit-disabled">
                            Simpan & Kirim Poin
                        </button>
                        <button type="button" id="btnCancelMobile" class="btn-cancel-mobile">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <hr class="section-divider">

        {{-- BAGIAN BAWAH: LOG RIWAYAT POIN --}}
        <div class="card card-log-history">
            <div class="log-header">
                <h2 class="form-title" style="margin: 0;">📜 Riwayat Log Poin Keaktifan</h2>
            </div>
            <div class="scrollable-table-wrapper" style="max-height: 400px;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Waktu Input</th>
                            <th>Nama Peserta</th>
                            <th>Poin</th>
                            <th>Keterangan</th>
                            <th>Panitia (Penanggung Jawab)</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatLog as $log)
                            <tr>
                                <td style="padding: 1rem; color: var(--primary); font-weight: 600;">
                                    {{ date('d M Y, H:i', strtotime($log->created_at)) }} WIB
                                </td>
                                <td style="padding: 1rem; color: var(--primary); font-weight: 700;">
                                    {{ $log->nama_peserta ?? 'Peserta Terhapus' }}
                                </td>
                                <td style="padding: 1rem; font-weight: 800;">
                                    <span class="{{ $log->poin > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $log->poin > 0 ? '+' . $log->poin : $log->poin }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: var(--primary); font-weight: 500;">
                                    {{ $log->keterangan }}
                                </td>
                                <td style="padding: 1rem; color: var(--primary);">
                                    <strong>{{ $log->nama_panitia ?? 'Sistem/Panitia' }}</strong>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <form action="{{ route('panitia.keaktifan.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus log poin ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-log">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">Belum ada riwayat pemberian poin keaktifan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">
                {{ $riwayatLog->links() }}
            </div>
        </div>

    </div>
</div>

{{-- INTERACTIVE JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('.peserta-row');
        const hiddenInput = document.getElementById('selected_peserta_id');
        const previewCard = document.getElementById('peserta_preview_card');
        const previewText = document.getElementById('preview_text');
        const submitBtn = document.getElementById('btnSubmitPoin');
        const searchInput = document.getElementById('searchPeserta');
        
        // Elemen Kontrol Drawer Mobile
        const formSection = document.getElementById('form-skor-section');
        const closeDrawer = document.getElementById('closeDrawer');
        const btnCancelMobile = document.getElementById('btnCancelMobile');

        // Fungsi Menutup Form Mobile
        function hideFormDrawer() {
            formSection.classList.remove('drawer-open');
            rows.forEach(r => {
                r.classList.remove('selected-row');
            });
        }

        closeDrawer.addEventListener('click', hideFormDrawer);
        btnCancelMobile.addEventListener('click', hideFormDrawer);

        // 1. Logika Klik Baris untuk Memilih Peserta & Membuka Form
        rows.forEach(row => {
            row.addEventListener('click', function () {
                rows.forEach(r => r.classList.remove('selected-row'));

                this.classList.add('selected-row');

                const pesertaId = this.getAttribute('data-id');
                const pesertaName = this.getAttribute('data-name');
                const pesertaKelompok = this.getAttribute('data-kelompok');

                hiddenInput.value = pesertaId;
                
                // Update teks preview form
                previewText.innerHTML = `👤 Terpilih: <strong style="color: #fff;">${pesertaName}</strong> <br> <span style="font-size:0.8rem; color:#bdd1d3;">(Kelompok ${pesertaKelompok})</span>`;
                previewCard.style.background = '#002f45';
                previewCard.style.borderColor = '#002f45';

                // Aktifkan tombol submit
                submitBtn.disabled = false;
                submitBtn.className = "btn-submit-ready";

                // Pemicu Animasi Slide Up Form di Mobile
                formSection.classList.add('drawer-open');
                
                // Auto fokus dialihkan ke dropdown pilihan poin
                setTimeout(() => {
                    document.querySelector('select[name="poin"]').focus();
                }, 300);
            });
        });

        // 2. Fitur Pencarian Cepat
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const groupRows = document.querySelectorAll('.group-row');

            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const grp = row.getAttribute('data-kelompok').toLowerCase();
                if (name.includes(filter) || grp.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            if(filter !== '') {
                groupRows.forEach(gr => gr.style.display = 'none');
            } else {
                groupRows.forEach(gr => gr.style.display = '');
            }
        });
    });
</script>

<style>
    /* Variabel Warna Global & Core */
    :root {
        --primary: #002f45;
        --accent: #d2c296;
        --bg-gradient: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);
        --card-bg: rgba(255, 255, 255, 0.3);
        --border-color: rgba(255, 255, 255, 0.45);
    }

    /* Container Dasar */
    .panel-container {
        min-height: calc(100vh - 64px);
        padding: 1rem;
        background: var(--bg-gradient);
        font-family: 'Segoe UI', Roboto, sans-serif;
        box-sizing: border-box;
    }
    .panel-wrapper {
        max-width: 1300px;
        margin: 0 auto;
    }

    /* Header Teroptimasi UX */
    .panel-header-zone {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .btn-back {
        color: var(--primary);
        text-decoration: none;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-weight: 700;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }
    .panel-title {
        font-size: 1.6rem;
        color: var(--primary);
        font-weight: 800;
        margin: 0;
    }
    .panel-subtitle {
        color: var(--primary);
        opacity: 0.7;
        font-size: 0.85rem;
        margin: 0.25rem 0 0 0;
        font-weight: 500;
    }

    /* Alerts */
    .alert {
        padding: 0.85rem 1.25rem;
        border-radius: 1rem;
        margin-bottom: 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }
    .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #166534; }
    .alert-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #991b1b; }

    /* Layout Utama Grid */
    .main-layout-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    /* Desain Kartu Glassmorphism */
    .card {
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 1.25rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .card-log-history {
        margin-top: 1.5rem;
        background: rgba(255, 255, 255, 0.5);
    }
    .log-header {
        padding: 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Pembatas Section */
    .section-divider {
        margin: 2.5rem 0 1.5rem 0;
        border: 0;
        height: 1px;
        background: rgba(0, 47, 69, 0.1);
    }

    /* Area Search Box Sticky */
    .sticky-search-box {
        position: sticky;
        top: 0;
        background: rgba(240,240,240,0.4);
        backdrop-filter: blur(10px);
        padding: 0.85rem;
        z-index: 20;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .sticky-search-box input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(0, 47, 69, 0.15);
        border-radius: 0.85rem;
        font-size: 0.9rem;
        color: var(--primary);
        background: white;
        font-weight: 600;
        box-sizing: border-box;
        outline: none;
    }

    /* Tabel Pembahanan */
    .scrollable-table-wrapper {
        max-height: calc(100vh - 260px);
        overflow-y: auto;
    }
    .modern-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .modern-table th {
        background: var(--primary);
        color: white;
        padding: 0.85rem 1rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.8rem;
        letter-spacing: 0.02em;
    }
    .group-row td {
        background: rgba(0, 47, 69, 0.07);
        padding: 0.5rem 1rem;
        font-weight: 800;
        color: var(--primary);
        font-size: 0.75rem;
    }
    .peserta-row {
        border-bottom: 1px solid rgba(0, 47, 69, 0.04);
        cursor: pointer;
        background: rgba(255,255,255,0.15);
        transition: background 0.15s;
    }
    
    .peserta-name-cell {
        padding: 1rem;
        color: var(--primary);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .avatar-dot {
        width: 8px;
        height: 8px;
        background: rgba(0,47,69,0.2);
        border-radius: 50%;
    }
    .peserta-group-cell {
        padding: 1rem;
        text-align: center;
        color: var(--primary);
        font-weight: 600;
        opacity: 0.7;
    }

    /* State Terpilih */
    .selected-row {
        background: var(--primary) !important;
    }
    .selected-row .peserta-name-cell { color: white !important; }
    .selected-row .peserta-group-cell { color: var(--accent) !important; opacity: 1; }
    .selected-row .avatar-dot { background: var(--accent); }

    /* Form Mode Drawer Header */
    .drawer-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.5rem 1rem 0 1rem;
    }
    .drawer-handle {
        width: 40px;
        height: 5px;
        background: rgba(0,0,0,0.2);
        border-radius: 10px;
        margin-bottom: 0.5rem;
    }
    .btn-close-drawer {
        align-self: flex-end;
        background: rgba(0,0,0,0.06);
        border: none;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--primary);
        cursor: pointer;
    }

    /* Form Inner Elements */
    .form-title {
        margin: 0 1.25rem 1rem 1.25rem;
        font-size: 1.2rem;
        color: var(--primary);
        font-weight: 800;
    }
    .preview-card-active {
        background: rgba(0,0,0,0.04);
        border: 1px dashed rgba(0,47,69,0.2);
        padding: 0.85rem;
        border-radius: 0.85rem;
        margin: 0 1.25rem 1.25rem 1.25rem;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
    }
    .form-group {
        margin: 0 1.25rem 1.25rem 1.25rem;
    }
    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.4rem;
    }
    
    /* STYLING UNTUK SELECT DROPDOWN BIAR SENADA DENGAN THEME */
    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid rgba(0, 47, 69, 0.2);
        border-radius: 0.85rem;
        font-size: 0.9rem;
        color: var(--primary);
        background: white;
        font-weight: 600;
        box-sizing: border-box;
        outline: none;
    }
    .form-group small {
        display: block;
        font-size: 0.7rem;
        color: var(--primary);
        opacity: 0.6;
        margin-top: 0.3rem;
    }
    .form-actions {
        margin: 0 1.25rem 1.5rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    /* Warna Poin Custom */
    .text-success { color: #16a34a !important; }
    .text-danger { color: #dc2626 !important; }

    /* Tombol Aksi */
    .btn-submit-disabled {
        width: 100%;
        padding: 0.85rem;
        background: #b5b5b5;
        color: #fff;
        border: none;
        border-radius: 0.85rem;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: not-allowed;
    }
    .btn-submit-ready {
        width: 100%;
        padding: 0.85rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 0.85rem;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,47,69,0.2);
    }
    .btn-cancel-mobile {
        background: transparent;
        border: 1px solid rgba(0,0,0,0.15);
        padding: 0.75rem;
        border-radius: 0.85rem;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--primary);
        cursor: pointer;
    }
    .btn-delete-log {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 0.5rem;
        font-weight: 700;
        font-size: 0.75rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-delete-log:hover {
        background: #b91c1c;
    }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--primary);
        opacity: 0.5;
    }
    .pagination-wrapper {
        padding: 1rem;
        display: flex;
        justify-content: center;
    }

    /* ATURAN INTERFACE KHUSUS LAYAR MOBILE (< 768px) */
    @media (max-width: 767px) {
        .panel-header-zone {
            flex-direction: column;
            align-items: stretch;
        }
        
        .card-right-drawer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: white;
            border-radius: 1.5rem 1.5rem 0 0;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.15);
            border: none;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .card-right-drawer.drawer-open {
            transform: translateY(0);
        }
        
        .scrollable-table-wrapper {
            max-height: calc(100vh - 230px);
        }
    }

    /* ATURAN TAMPILAN UNTUK LAYAR DESKTOP (>= 768px) */
    @media (min-width: 768px) {
        .main-layout-grid {
            grid-template-columns: 1.2fr 1fr;
        }
        .panel-title {
            font-size: 2rem;
        }
        
        .drawer-header { display: none; }
        .btn-cancel-mobile { display: none; }
        .form-actions { flex-direction: row; }
        .form-title { margin: 1.25rem 1.25rem 1rem 1.25rem; }
        
        .card-right-drawer {
            position: sticky;
            top: 1rem;
        }
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(0, 47, 69, 0.2); border-radius: 10px; }
</style>
@endsection