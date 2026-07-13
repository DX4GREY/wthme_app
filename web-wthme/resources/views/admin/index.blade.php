@extends('layouts.app')

@section('content')
    <div style="max-width:1100px; margin:0 auto; padding:2rem 1.5rem;">
        <a href="{{ route('dashboard') }}"
            style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
            ← Kembali ke Dashboard
        </a>

        {{-- Header & Statistik Singkat --}}
        <div
            style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:700;">
                    Manajemen Pengguna
                </h1>
                <p style="color:#002f45; opacity:0.5; font-size:0.875rem;">
                    Panitia: <strong>{{ $totalPanitia }}</strong> | Peserta: <strong>{{ $totalPeserta }}</strong>
                </p>
            </div>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <a href="{{ route('admin.control-center') }}"
                    style="padding:0.6rem 1.25rem; background:#002f45; color:#fff; border-radius:0.6rem; text-decoration:none; font-size:0.875rem; font-weight:700;">
                    ⚙ Control Center
                </a>
                {{-- 🟢 PERBAIKAN DI SINI: Mengubah route lama ke route admin baru --}}
                <a href="{{ route('admin.abang.import') }}"
                    style="padding:0.6rem 1.25rem; background:#bdd1d3; color:#002f45; border-radius:0.6rem; 
                      text-decoration:none; font-size:0.875rem; font-weight:700; border:2px solid #bdd1d3;">
                    ⬆ Import Abang KBMS
                </a>
                
                <a href="{{ route('admin.import.peserta') }}"
                    style="padding:0.6rem 1.25rem; background:#d2c296; color:#002f45; border-radius:0.6rem; 
                      text-decoration:none; font-size:0.875rem; font-weight:700; border:2px solid #d2c296;">
                    ⬆ Import Peserta
                </a>
                <a href="{{ route('admin.import') }}"
                    style="padding:0.6rem 1.25rem; background:#002f45; color:#d2c296; border-radius:0.6rem; 
                      text-decoration:none; font-size:0.875rem; font-weight:700;">
                    ⬆ Import Panitia
                </a>
            </div>
        </div>

        {{-- Form Pencarian --}}
        <div
            style="margin-bottom: 2rem; background: #f4f7f8; padding: 1.5rem; border-radius: 1rem; border: 1px solid #bdd1d3;">
            <form action="{{ route('admin.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau NIM..."
                    style="flex: 1; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 2px solid #bdd1d3; font-size: 0.875rem; outline: none; transition: border-color 0.2s;">

                <button type="submit"
                    style="padding: 0.75rem 1.5rem; background: #002f45; color: white; border: none; border-radius: 0.6rem; cursor: pointer; font-weight: 600;">
                    🔍 Cari
                </button>

                @if (request('search'))
                    <a href="{{ route('admin.index') }}"
                        style="padding: 0.75rem 1.5rem; background: #bdd1d3; color: #002f45; border-radius: 0.6rem; text-decoration: none; font-weight: 600; font-size: 0.875rem;">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div
                style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.75rem; margin-bottom:1.5rem; border:1px solid #86efac; font-size:0.875rem;">
                {{ session('success') }}
            </div>
        @endif

        {{-- --- BAGIAN PANITIA --- --}}
        <h2
            style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.25rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
            <span>👥</span> Daftar Panitia
        </h2>
        <div style="background:white; border-radius:1rem; overflow:hidden; border:1px solid #bdd1d3; margin-bottom:3rem;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#002f45;">
                        <th
                            style="padding:0.875rem 1rem; text-align:left; color:#d2c296; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Nama / NIM</th>
                        <th
                            style="padding:0.875rem 1rem; text-align:left; color:#d2c296; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Divisi</th>
                        <th
                            style="padding:0.875rem 1rem; text-align:left; color:#d2c296; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Status PW</th>
                        <th
                            style="padding:0.875rem 1rem; text-align:center; color:#d2c296; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($panitiaList as $p)
                        <tr style="border-bottom:1px solid #e0decd; {{ $loop->even ? 'background:#f9f8f6;' : '' }}">
                            <td style="padding:0.875rem 1rem;">
                                <div style="color:#002f45; font-weight:600; font-size:0.875rem;">{{ $p->name }}</div>
                                <div style="color:#002f45; opacity:0.5; font-size:0.75rem;">{{ $p->nim }}</div>
                            </td>
                            <td style="padding:0.875rem 1rem;">
                                <span
                                    style="background:#e0decd; color:#002f45; padding:0.25rem 0.6rem; border-radius:0.4rem; font-size:0.7rem; font-weight:700; text-transform:uppercase;">
                                    {{ $p->divisi }}
                                </span>
                            </td>
                            <td style="padding:0.875rem 1rem;">
                                {!! $p->must_change_password
                                    ? '<span style="color:#d97706; font-size:0.75rem;">⚠ Default</span>'
                                    : '<span style="color:#16a34a; font-size:0.75rem;">✓ Aman</span>' !!}
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:center;">
                                <div style="display:flex; gap:0.4rem; justify-content:center;">
                                    <a href="{{ route('admin.panitia.edit', $p->id) }}"
                                        style="padding:0.3rem 0.6rem; background:#bdd1d3; color:#002f45; border-radius:0.4rem; text-decoration:none; font-size:0.7rem; font-weight:600;">Edit</a>
                                    <form method="POST" action="{{ route('admin.panitia.reset', $p->id) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Reset PW Panitia ke NIM?')"
                                            style="padding:0.3rem 0.6rem; background:#d2c296; color:#002f45; border:none; border-radius:0.4rem; cursor:pointer; font-size:0.7rem; font-weight:600;">Reset</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:2rem; text-align:center; opacity:0.5;">Belum ada data panitia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- --- BAGIAN PESERTA --- --}}
        <h2
            style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.25rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
            <span>🎓</span> Daftar Peserta
        </h2>
        <div style="background:white; border-radius:1rem; overflow:hidden; border:1px solid #bdd1d3;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#d2c296;">
                        <th
                            style="padding:0.875rem 1rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Nama / NIM</th>
                        <th
                            style="padding:0.875rem 1rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Kelompok</th>
                        <th
                            style="padding:0.875rem 1rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Gender</th>
                        <th
                            style="padding:0.875rem 1rem; text-align:center; color:#002f45; font-size:0.75rem; font-weight:600; text-transform:uppercase;">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesertaList ?? [] as $peserta)
                        <tr style="border-bottom:1px solid #e0decd; {{ $loop->even ? 'background:#fdfbf7;' : '' }}">
                            <td style="padding:0.875rem 1rem;">
                                <div style="color:#002f45; font-weight:600; font-size:0.875rem;">{{ $peserta->name }}</div>
                                <div style="color:#002f45; opacity:0.5; font-size:0.75rem;">{{ $peserta->nim }}</div>
                            </td>
                            <td style="padding:0.875rem 1rem;">
                                <span
                                    style="background:#002f45; color:#d2c296; padding:0.25rem 0.6rem; border-radius:0.4rem; font-size:0.75rem; font-weight:700;">
                                    Kelompok {{ $peserta->kelompok }}
                                </span>
                            </td>
                            <td style="padding:0.875rem 1rem; color:#002f45; font-size:0.875rem;">
                                {{ $peserta->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:center;">
                                <form method="POST" action="{{ route('admin.peserta.reset', $peserta->id) }}"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Reset PW Peserta {{ $peserta->name }} ke NIM?')"
                                        style="padding:0.35rem 0.75rem; background:#002f45; color:#d2c296; border:none; border-radius:0.4rem; cursor:pointer; font-size:0.7rem; font-weight:600;">
                                        Reset PW
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:2rem; text-align:center; opacity:0.5;">Belum ada data peserta.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
