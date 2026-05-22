@extends('layouts.app')

@section('content')
    {{-- CSS Responsif & Glass Effect --}}
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 1.5rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .summary-grid-mobile { grid-template-columns: 1fr !important; }
            .form-grid-mobile { grid-template-columns: 1fr !important; }
            .header-mobile { flex-direction: column !important; align-items: flex-start !important; }
        }
    </style>

    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #d2c296 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1200px; margin:0 auto;">

            {{-- Header --}}
            <div class="header-mobile" style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; margin-bottom:1rem; transition:0.3s;"
                        onmouseover="this.style.opacity='1'; this.style.transform='translateX(-5px)'"
                        onmouseout="this.style.opacity='0.7'; this.style.transform='translateX(0)'">
                        <span style="margin-right:8px;">←</span> Kembali ke Portal
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Kas <span style="color:#6b705c; font-style:italic;">Event</span>
                    </h1>
                </div>
                <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
                    <a href="{{ route('panitia.kas.export', request()->only('jenis', 'divisi')) }}"
                        style="padding:0.8rem 1.5rem; background: rgba(255, 255, 255, 0.3); color:#002f45; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.4); transition: 0.3s;">
                        ⬇ Export Excel
                    </a>
                    @if (auth()->user()->role === 'bendahara' || auth()->user()->role === 'admin')
                        <button onclick="toggleForm()"
                            style="padding:0.8rem 1.5rem; background:#002f45; color:#d2c296; border-radius:1rem; border:none; cursor:pointer; font-size:0.875rem; font-weight:700; box-shadow: 0 10px 15px rgba(0,0,0,0.1);">
                            + Tambah Transaksi
                        </button>
                    @endif
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="summary-grid-mobile" style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-bottom:2.5rem;">
                {{-- Saldo Card --}}
                <div class="glass-card" style="padding:1.5rem; background: rgba(0, 47, 69, 0.85);">
                    <div style="color:#d2c296; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.15em; margin-bottom:0.5rem; font-weight:700;">Saldo Saat Ini</div>
                    <div style="color:white; font-size:2rem; font-weight:800; font-family:'Playfair Display',serif;">
                        Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Pemasukan Card --}}
                <div class="glass-card" style="padding:1.5rem; border-left: 5px solid #2f855a;">
                    <div style="color:#2f855a; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.15em; margin-bottom:0.5rem; font-weight:700;">Total Pemasukan</div>
                    <div style="color:#002f45; font-size:1.75rem; font-weight:800;">
                        Rp {{ number_format($totalMasuk, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Pengeluaran Card --}}
                <div class="glass-card" style="padding:1.5rem; border-left: 5px solid #c53030;">
                    <div style="color:#c53030; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.15em; margin-bottom:0.5rem; font-weight:700;">Total Pengeluaran</div>
                    <div style="color:#002f45; font-size:1.75rem; font-weight:800;">
                        Rp {{ number_format($totalKeluar, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Form Tambah Transaksi (Glass Style) --}}
            <div id="form-transaksi" style="display:none; margin-bottom:2.5rem;">
                <div class="glass-card" style="padding:2.5rem; background: rgba(255, 255, 255, 0.4);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                        <h3 style="color:#002f45; font-weight:800; font-size:1.25rem; margin:0;">📝 Input Transaksi Baru</h3>
                        <button onclick="toggleForm()" style="background:rgba(0,0,0,0.1); border:none; width:30px; height:30px; border-radius:50%; cursor:pointer;">×</button>
                    </div>

                    <form method="POST" action="{{ route('panitia.kas.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid-mobile" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">Tanggal</label>
                                <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required style="width:100%; padding:0.8rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; background:rgba(255,255,255,0.5);">
                            </div>
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">Jenis</label>
                                <select name="jenis" id="jenis-select" required onchange="handleJenisChange(this.value)" style="width:100%; padding:0.8rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; background:rgba(255,255,255,0.5);">
                                    <option value="">-- Pilih --</option>
                                    <option value="masuk">⬆ Uang Masuk</option>
                                    <option value="keluar">⬇ Uang Keluar</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">Nominal (Rp)</label>
                                <input type="number" name="nominal" required style="width:100%; padding:0.8rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; background:rgba(255,255,255,0.5);">
                            </div>
                        </div>

                        <div class="form-grid-mobile" style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
                            <div id="divisi-section" style="display:none;">
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">Divisi</label>
                                <select name="divisi" id="divisi-select" style="width:100%; padding:0.8rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; background:rgba(255,255,255,0.5);">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach ($divisiList as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">PIC</label>
                                <input type="text" name="pic" required style="width:100%; padding:0.8rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; background:rgba(255,255,255,0.5);">
                            </div>
                        </div>

                        <div style="margin-bottom:1.5rem;">
                            <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">Keterangan</label>
                            <textarea name="keterangan" required rows="2" style="width:100%; padding:0.8rem; border:1px solid rgba(0,0,0,0.1); border-radius:0.75rem; background:rgba(255,255,255,0.5);"></textarea>
                        </div>

                        <div style="margin-bottom:2rem;">
                            <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase;">Bukti Transaksi</label>
                            <input type="file" name="bukti_file" style="font-size:0.8rem; color:#002f45;">
                        </div>

                        <div style="display:flex; gap:1rem;">
                            <button type="submit" style="flex:2; padding:1rem; background:#002f45; color:white; border:none; border-radius:1rem; font-weight:700; cursor:pointer;">Simpan Transaksi</button>
                            <button type="button" onclick="toggleForm()" style="flex:1; padding:1rem; background:rgba(0,0,0,0.05); color:#002f45; border:none; border-radius:1rem; font-weight:700; cursor:pointer;">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table Transaksi (Glass Style) --}}
            <div class="glass-card" style="overflow:hidden;">
                <div style="background: rgba(0, 47, 69, 0.85); padding: 1.25rem 2rem; color: #d2c296; font-weight: 800; letter-spacing: 0.1em; font-size: 0.9rem;">
                    RIWAYAT TRANSAKSI
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:1000px;">
                        <thead>
                            <tr style="background: rgba(255, 255, 255, 0.15);">
                                <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">No</th>
                                <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Tanggal</th>
                                <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Jenis</th>
                                <th style="padding:1.25rem 1rem; text-align:right; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Nominal</th>
                                <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Divisi</th>
                                <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Keterangan</th>
                                <th style="padding:1.25rem 1rem; text-align:right; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Saldo</th>
                                <th style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksi as $i => $t)
                                <tr style="border-bottom:1px solid rgba(0,0,0,0.05); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:1.25rem 1rem; color:#002f45; opacity:0.6; font-family:monospace;">{{ $i + 1 }}</td>
                                    <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.85rem; font-weight:600;">{{ $t->tanggal->format('d/m/Y') }}</td>
                                    <td style="padding:1.25rem 1rem;">
                                        <span style="padding:0.3rem 0.6rem; border-radius:0.5rem; font-size:0.65rem; font-weight:800; {{ $t->jenis === 'masuk' ? 'background:rgba(198,246,213,0.7); color:#22543d;' : 'background:rgba(254,215,215,0.7); color:#822727;' }}">
                                            {{ strtoupper($t->jenis) }}
                                        </span>
                                    </td>
                                    <td style="padding:1.25rem 1rem; text-align:right; font-weight:800; color:{{ $t->jenis === 'masuk' ? '#2f855a' : '#c53030' }};">
                                        {{ $t->jenis === 'masuk' ? '+' : '-' }} {{ number_format($t->nominal, 0, ',', '.') }}
                                    </td>
                                    <td style="padding:1.25rem 1rem; font-size:0.8rem; color:#002f45;">{{ $t->divisi ?? '-' }}</td>
                                    <td style="padding:1.25rem 1rem; font-size:0.8rem; color:#002f45; max-width:200px;">{{ $t->keterangan }}</td>
                                    <td style="padding:1.25rem 1rem; text-align:right; font-weight:700; color:#002f45; font-size:0.85rem;">{{ number_format($t->saldo_berjalan, 0, ',', '.') }}</td>
                                    <td style="padding:1.25rem 1rem; text-align:center;">
                                        <div style="display:flex; gap:0.5rem; justify-content:center;">
                                            @if ($t->bukti_file)
                                                <a href="{{ Storage::url($t->bukti_file) }}" target="_blank" style="text-decoration:none;">📄</a>
                                            @endif
                                            @if (auth()->user()->role === 'bendahara' || auth()->user()->role === 'admin')
                                                <form method="POST" action="{{ route('panitia.kas.destroy', $t->id) }}" onsubmit="return confirm('Hapus transaksi?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="background:none; border:none; color:#c53030; cursor:pointer; font-weight:bold;">×</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('form-transaksi');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function handleJenisChange(val) {
            const div = document.getElementById('divisi-section');
            const sel = document.getElementById('divisi-select');
            if (val === 'keluar') {
                div.style.display = 'block';
                sel.required = true;
            } else {
                div.style.display = 'none';
                sel.required = false;
                sel.value = '';
            }
        }
    </script>
@endsection