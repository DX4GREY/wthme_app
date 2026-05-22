@extends('layouts.app')

@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Background Ambient --}}
    <div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Inter', sans-serif;">
        <div style="max-width: 1200px; margin: 0 auto;">

            {{-- Header Section --}}
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 3rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <a href="{{ route('panitia.index') }}" style="color: #002f45; opacity: 0.5; text-decoration: none; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.5rem;">
                        ← Kembali ke Dashboard
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Mentoring <span style="color:#6b705c; font-style:italic;">Kelompok</span>
                    </h1>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('panitia.mentoring.rekap') }}" class="glass-btn primary">
                        📊 Rekap Seluruh Kelompok
                    </a>
                </div>
            </div>

            {{-- Breadcrumb / Kelompok Info --}}
            @if (isset($kelompok))
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; margin-bottom: 2rem;">
                    <a href="{{ route('panitia.mentoring.index') }}" class="glass-btn secondary">
                        ← Ganti Kelompok
                    </a>
                    <div style="padding: 0.6rem 1.25rem; background: rgba(0,47,69,0.9); color: #d2c296; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 700;">
                        Kelompok {{ $kelompok }}
                    </div>
                    <a href="{{ route('panitia.mentoring.export', $kelompok) }}" class="glass-btn success">
                        ⬇ Export Excel
                    </a>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="glass-alert">
                    ✅ {{ session('success') }}
                </div>
            @endif

            {{-- KONDISI 1: JIKA KELOMPOK SUDAH DIPILIH --}}
            @if (isset($kelompok))
                
                {{-- Form Input (Admin/Bendahara Only) --}}
                @if (auth()->user()->isMentor())
                    <div class="glass-card main-form">
                        <h3 class="card-subtitle">📝 Catat Pertemuan Baru</h3>
                        <form method="POST" action="{{ route('panitia.mentoring.store', $kelompok) }}">
                            @csrf
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                                <div>
                                    <label class="input-label">Nama Kegiatan / Materi *</label>
                                    <input type="text" name="nama_kegiatan" required placeholder="Misal: Adab Penuntut Ilmu" class="glass-input">
                                </div>
                                <div>
                                    <label class="input-label">Tanggal *</label>
                                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="glass-input">
                                </div>
                            </div>

                            <div class="glass-table-container">
                                <p class="table-intro">Presensi Anggota Kelompok {{ $kelompok }}</p>
                                <table class="glass-table">
                                    <thead>
                                        <tr>
                                            <th>NAMA PESERTA</th>
                                            <th>NIM</th>
                                            <th style="width: 150px;">KEHADIRAN</th>
                                            <th>CATATAN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($peserta as $p)
                                            <tr>
                                                <td style="font-weight: 700;">{{ $p->name }}</td>
                                                <td style="opacity: 0.6;">{{ $p->nim }}</td>
                                                <td>
                                                    <select name="kehadiran[{{ $p->id }}]" required class="glass-select">
                                                        <option value="Hadir">Hadir</option>
                                                        <option value="Izin">Izin</option>
                                                        <option value="Alpha">Alpha</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="keterangan[{{ $p->id }}]" placeholder="..." class="glass-input-sm">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                                <button type="submit" class="glass-btn submit-btn">
                                    💾 Simpan Laporan Mentoring
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="glass-alert info">
                        ℹ️ <strong>Mode Lihat:</strong> Anda hanya dapat memantau riwayat mentoring kelompok ini.
                    </div>
                @endif

                {{-- Riwayat Tabel --}}
                <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.5rem; margin-top:4rem; margin-bottom:1.5rem;">
                    📜 Riwayat Detail Pertemuan
                </h3>

                @forelse($mentorings as $men)
                    <div class="glass-card history-card">
                        <div class="history-header">
                            <div>
                                <span class="history-title">📌 {{ $men->nama_kegiatan }}</span>
                                <span class="history-date">📅 {{ date('d M Y', strtotime($men->tanggal)) }}</span>
                            </div>
                            @if (auth()->user()->role === 'bendahara' || auth()->user()->role === 'admin')
                                <form method="POST" action="{{ route('panitia.mentoring.destroy', $men->id) }}" onsubmit="return confirm('Hapus seluruh catatan kegiatan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="delete-link">🗑️ Hapus</button>
                                </form>
                            @endif
                        </div>

                        <div class="glass-table-container no-bg">
                            <table class="glass-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIM</th>
                                        <th>Gender</th>
                                        <th>Kehadiran</th>
                                        <th>Catatan</th>
                                        <th style="text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($men->details as $det)
                                        <tr>
                                            <td style="font-weight: 600;">{{ $det->peserta->name ?? 'User Terhapus' }}</td>
                                            <td style="font-size: 0.75rem; opacity: 0.7;">{{ $det->peserta->nim ?? '-' }}</td>
                                            <td style="font-size: 0.75rem;">{{ $det->peserta->gender ?? '-' }}</td>
                                            <td>
                                                <span class="badge-status {{ strtolower($det->kehadiran) }}">
                                                    {{ strtoupper($det->kehadiran) }}
                                                </span>
                                            </td>
                                            <td style="font-style: italic; font-size: 0.8rem; opacity: 0.7;">{{ $det->keterangan ?? '—' }}</td>
                                            <td style="text-align: center;">
                                                @if (auth()->user()->role === 'bendahara' || auth()->user()->role === 'admin')
                                                    <button onclick="openEditModal('{{ $det->id }}', '{{ $det->kehadiran }}', '{{ $det->keterangan }}')" class="edit-mini-btn">
                                                        ✏️ Edit
                                                    </button>
                                                @else
                                                    <span style="font-size: 0.7rem; opacity: 0.3;">🔒</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="glass-card empty-state">
                        <p>Belum ada riwayat mentoring untuk kelompok ini.</p>
                    </div>
                @endforelse

            {{-- KONDISI 2: PILIH KELOMPOK --}}
            @else
                <div class="glass-card hero-selection">
                    <div class="hero-icon">👥</div>
                    <h2 class="hero-title">Pilih Kelompok Mentoring</h2>
                    <p class="hero-desc">Silakan pilih kelompok untuk mengelola data kehadiran.</p>
                </div>

                <div class="selection-grid">
                    @forelse($listKelompok as $k)
                        <a href="{{ route('panitia.mentoring.kelompok', $k) }}" class="group-card">
                            <span class="group-label">Kelompok</span>
                            <span class="group-number">{{ $k }}</span>
                            <div class="group-btn">Buka Presensi →</div>
                        </a>
                    @empty
                        <div class="glass-card empty-state" style="grid-column: 1/-1;">
                            <p>Belum ada data kelompok peserta terdaftar.</p>
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>

    {{-- MODAL EDIT GLASS --}}
    <div id="editModal" class="glass-modal-overlay">
        <div class="glass-card modal-content">
            <h3 class="card-subtitle">Update Kehadiran</h3>
            <form id="editForm" method="POST" action="">
                @csrf @method('PUT')
                <div style="margin-bottom:1.5rem;">
                    <label class="input-label">Status Kehadiran</label>
                    <select name="kehadiran" id="modalKehadiran" class="glass-input">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Alpha">Alpha</option>
                    </select>
                </div>
                <div style="margin-bottom:2rem;">
                    <label class="input-label">Catatan</label>
                    <input type="text" name="keterangan" id="modalKeterangan" class="glass-input">
                </div>
                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" class="glass-btn submit-btn" style="flex:1;">Simpan</button>
                    <button type="button" onclick="closeEditModal()" class="glass-btn secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Base Glass Styles */
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 2rem; padding: 2rem; }
        .card-subtitle { color: #002f45; font-weight: 800; font-size: 1.1rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        
        /* Form Elements */
        .input-label { display: block; font-size: 0.7rem; font-weight: 700; color: #002f45; opacity: 0.6; margin-bottom: 0.5rem; text-transform: uppercase; }
        .glass-input { width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 47, 69, 0.1); padding: 0.75rem 1rem; border-radius: 1rem; color: #002f45; outline: none; }
        .glass-input:focus { border-color: #002f45; background: white; }
        .glass-input-sm { width: 100%; background: transparent; border: none; border-bottom: 1px solid rgba(0, 47, 69, 0.1); font-size: 0.8rem; padding: 0.25rem; outline: none; }
        .glass-select { background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 47, 69, 0.1); padding: 0.4rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 700; color: #002f45; }

        /* Buttons */
        .glass-btn { text-decoration: none; padding: 0.7rem 1.5rem; border-radius: 1rem; font-size: 0.85rem; font-weight: 700; transition: 0.3s; border: none; cursor: pointer; display: inline-flex; align-items: center; }
        .glass-btn.primary { background: #002f45; color: #d2c296; }
        .glass-btn.secondary { background: rgba(255, 255, 255, 0.5); color: #002f45; }
        .glass-btn.success { background: #6b705c; color: white; }
        .glass-btn.submit-btn { background: #002f45; color: white; box-shadow: 0 10px 20px rgba(0,47,69,0.2); }
        .glass-btn:hover { transform: translateY(-3px); opacity: 0.9; }

        /* Tables */
        .glass-table-container { background: rgba(255, 255, 255, 0.3); border-radius: 1.5rem; padding: 1.5rem; overflow-x: auto; }
        .glass-table-container.no-bg { background: transparent; padding: 0; }
        .table-intro { font-size: 0.75rem; font-weight: 800; color: #002f45; opacity: 0.5; margin-bottom: 1rem; text-transform: uppercase; }
        .glass-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .glass-table th { text-align: left; font-size: 0.65rem; color: #002f45; opacity: 0.5; padding: 1rem; border-bottom: 2px solid rgba(0, 47, 69, 0.05); }
        .glass-table td { padding: 1rem; font-size: 0.85rem; color: #002f45; border-bottom: 1px solid rgba(0, 47, 69, 0.03); }

        /* Badges */
        .badge-status { padding: 0.25rem 0.6rem; border-radius: 0.5rem; font-size: 0.65rem; font-weight: 800; }
        .badge-status.hadir { background: #dcfce7; color: #166534; }
        .badge-status.izin { background: #fff7ed; color: #9a3412; }
        .badge-status.alpha { background: #fee2e2; color: #991b1b; }

        /* History & Selection Grid */
        .history-card { margin-bottom: 2rem; }
        .history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .history-title { font-weight: 800; color: #002f45; font-size: 1rem; }
        .history-date { font-size: 0.8rem; font-weight: 600; opacity: 0.5; margin-left: 1rem; }
        .delete-link { background: none; border: none; color: #991b1b; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
        .edit-mini-btn { background: rgba(0,47,69,0.05); border: none; padding: 0.3rem 0.6rem; border-radius: 0.5rem; font-size: 0.75rem; color: #002f45; font-weight: 700; cursor: pointer; }

        .selection-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; }
        .group-card { text-decoration: none; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px); border: 1px solid white; border-radius: 2rem; padding: 2.5rem; text-align: center; transition: 0.3s; }
        .group-card:hover { transform: translateY(-10px); background: white; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .group-label { display: block; font-size: 0.75rem; font-weight: 700; color: #002f45; opacity: 0.4; text-transform: uppercase; letter-spacing: 0.1em; }
        .group-number { display: block; font-family: 'Playfair Display', serif; font-size: 3.5rem; font-weight: 900; color: #002f45; margin: 0.5rem 0; }
        .group-btn { display: inline-block; padding: 0.5rem 1.25rem; background: #e0decd; color: #002f45; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 800; }

        /* Modal */
        .glass-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 47, 69, 0.6); backdrop-filter: blur(5px); z-index: 9999; align-items: center; justify-content: center; }
        .modal-content { width: 100%; max-width: 400px; background: white; }

        .glass-alert { padding: 1rem 1.5rem; background: rgba(220, 252, 231, 0.6); border: 1px solid #86efac; border-radius: 1rem; color: #166534; margin-bottom: 2rem; font-weight: 600; font-size: 0.9rem; }
        .hero-selection { text-align: center; padding: 4rem 2rem; margin-bottom: 3rem; background: rgba(0, 47, 69, 0.9); }
        .hero-icon { font-size: 5rem; opacity: 0.1; position: absolute; right: 2rem; top: 1rem; }
        .hero-title { font-family: 'Playfair Display', serif; color: #d2c296; font-size: 2.2rem; margin-bottom: 0.5rem; }
        .hero-desc { color: #bdd1d3; opacity: 0.8; }
    </style>

    <script>
        function openEditModal(id, kehadiran, keterangan) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            form.action = `/panitia/mentoring/detail/${id}`; // Sesuaikan URL Route
            document.getElementById('modalKehadiran').value = kehadiran;
            document.getElementById('modalKeterangan').value = keterangan === '—' ? '' : keterangan;
            modal.style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
@endsection