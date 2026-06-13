@extends('layouts.app')

@section('content')
    <div style="max-width:1100px; margin:0 auto; padding:2rem 1.5rem;">
        <a href="{{ route('dashboard') }}"
            style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
            ← Kembali ke Dashboard
        </a>

        {{-- Header & Statistik --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:700;">
                    Manajemen Data Abang KBMS
                </h1>
                <p style="color:#002f45; opacity:0.5; font-size:0.875rem;">
                    Total Data Terdaftar: <strong>{{ $totalAbang }}</strong> abang
                </p>
            </div>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <a href="{{ route('admin.abang.import') }}"
                    style="padding:0.6rem 1.25rem; background:#d2c296; color:#002f45; border-radius:0.6rem; 
                      text-decoration:none; font-size:0.875rem; font-weight:700; border:2px solid #d2c296;">
                    ⬆ Import Data Abang
                </a>
            </div>
        </div>

        {{-- Form Pencarian --}}
        <div style="margin-bottom: 2rem; background: #f4f7f8; padding: 1.5rem; border-radius: 1rem; border: 1px solid #bdd1d3;">
            <form action="{{ route('admin.abang.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau Angkatan..."
                    style="flex: 1; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 2px solid #bdd1d3; font-size: 0.875rem; outline: none;">

                <button type="submit"
                    style="padding: 0.75rem 1.5rem; background: #002f45; color: white; border: none; border-radius: 0.6rem; cursor: pointer; font-weight: 600;">
                    🔍 Cari
                </button>

                @if (request('search'))
                    <a href="{{ route('admin.abang.index') }}"
                        style="padding: 0.75rem 1.5rem; background: #bdd1d3; color: #002f45; border-radius: 0.6rem; text-decoration: none; font-weight: 600; font-size: 0.875rem;">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.75rem; margin-bottom:1.5rem; border:1px solid #86efac; font-size:0.875rem;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabel Data --}}
        <h2 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.25rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
            <span>👔</span> Daftar Master Abang
        </h2>
        <div style="background:white; border-radius:1rem; overflow:hidden; border:1px solid #bdd1d3;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#002f45;">
                        <th style="padding:0.875rem 1rem; text-align:left; color:#d2c296; font-size:0.75rem; font-weight:600; text-transform:uppercase; width: 60%;">Nama</th>
                        <th style="padding:0.875rem 1rem; text-align:left; color:#d2c296; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Angkatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($abangList as $abang)
                        <tr style="border-bottom:1px solid #e0decd; {{ $loop->even ? 'background:#fdfbf7;' : '' }}">
                            <td style="padding:0.875rem 1rem;">
                                <div style="color:#002f45; font-weight:600; font-size:0.875rem;">{{ $abang->name }}</div>
                            </td>
                            <td style="padding:0.875rem 1rem;">
                                <span style="background:#e0decd; color:#002f45; padding:0.25rem 0.6rem; border-radius:0.4rem; font-size:0.75rem; font-weight:700;">
                                    Angkatan {{ $abang->angkatan }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:2rem; text-align:center; opacity:0.5;">Belum ada data abang KBMS.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection