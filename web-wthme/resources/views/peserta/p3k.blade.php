@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1000px; margin:0 auto;">

        {{-- Header --}}
        <div style="margin-bottom:2rem;">
            <a href="{{ route('peserta.index') }}"
               style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
                Kembali ke Portal
            </a>

            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.25rem; font-weight:800; margin:0;">
                🩹 P3K Kelompok
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-top:0.5rem; font-weight:500;">
                Kelompok {{ $kelompok }} — Pengumpulan barang P3K & pendataan obat pribadi.
            </p>
            @if($pj)
            <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin-top:0.25rem; font-weight:600;">
                🛡️ PJ P3K Kelompok Anda: {{ $pj->name }}
            </p>
            @endif
        </div>

        @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.3); color:#991b1b; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ⚠️ {{ $errors->first() }}
        </div>
        @endif

        {{-- Summary --}}
        @php
            $allData = $dataKelompok->concat($dataIndividu);
            $totalBarang  = $allData->count();
            $barangLengkap = $allData->where('is_lengkap', true)->count();
            $persen = $totalBarang > 0 ? round($barangLengkap / $totalBarang * 100) : 0;
        @endphp
        <div style="background: rgba(0, 47, 69, 0.85); backdrop-filter: blur(10px); border-radius:1.5rem; padding:1.75rem 2rem; margin-bottom:2rem; display:flex; align-items:center; gap:2.5rem; flex-wrap:wrap; box-shadow: 0 15px 35px rgba(0, 47, 69, 0.2); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div>
                <div style="color:rgba(210, 194, 150, 0.7); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:700; margin-bottom:0.5rem;">Progress Kelengkapan P3K</div>
                <div style="color:#d2c296; font-size:2.5rem; font-weight:800; line-height:1;">{{ $barangLengkap }}<span style="font-size:1.25rem; opacity:0.5; font-weight:400;">/{{ $totalBarang }}</span></div>
            </div>
            <div style="flex:1; min-width:250px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem;">
                    <span style="color:white; font-size:0.85rem; font-weight:600;">Barang Terkumpul</span>
                    <span style="color:#d2c296; font-size:0.85rem; font-weight:800;">{{ $persen }}%</span>
                </div>
                <div style="background:rgba(255,255,255,0.1); border-radius:999px; height:12px; overflow:hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="background: linear-gradient(90deg, #d2c296, #f3e5ab); height:100%; border-radius:999px; width:{{ $persen }}%;"></div>
                </div>
            </div>
        </div>

        {{-- Barang Kelompok --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">📦 Barang Kelompok</h3>
        <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin-top:-0.5rem; margin-bottom:1rem; padding-left:0.5rem;">Barang ini dikumpulkan jadi satu untuk dipakai bersama kelompok.</p>
        @include('peserta.partials.p3k-table-kelompok', ['data' => $dataKelompok])

        {{-- Barang Individu --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin:2rem 0 1rem; padding-left:0.5rem;">🎒 Barang Individu (Bawaan Pribadi)</h3>
        <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin-top:-0.5rem; margin-bottom:1rem; padding-left:0.5rem;">Barang ini wajib dibawa sendiri oleh Anda (bukan diwakilkan kelompok).</p>
        @include('peserta.partials.p3k-table-individu', ['data' => $dataIndividu])

        {{-- Obat Pribadi --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin:2rem 0 1rem; padding-left:0.5rem;">💊 Obat Pribadi</h3>

        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);">
            <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin-bottom:1.25rem;">
                Jika Anda membawa obat pribadi (untuk penyakit/alergi tertentu), laporkan di sini agar tercatat dan dapat diserahkan ke PJ P3K kelompok.
            </p>

            <form method="POST" action="{{ route('peserta.p3k.obat.store') }}" enctype="multipart/form-data" style="display:grid; grid-template-columns:1.5fr 1.5fr 2fr auto; gap:1rem; align-items:end; margin-bottom:1.5rem;">
                @csrf
                <div>
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Penyakit/Kondisi</label>
                    <input type="text" name="penyakit" required placeholder="cth: Asma, Maag..."
                           style="width:100%; padding:0.7rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.15); border-radius:0.7rem; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Nama Obat</label>
                    <input type="text" name="nama_obat" placeholder="cth: Ventolin Inhaler"
                           style="width:100%; padding:0.7rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.15); border-radius:0.7rem; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Catatan</label>
                    <input type="text" name="catatan" placeholder="cth: diminum jika sesak"
                           style="width:100%; padding:0.7rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.15); border-radius:0.7rem; font-size:0.9rem;">
                </div>
                <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.7rem 1.5rem; border-radius:0.7rem; font-size:0.85rem; font-weight:800; cursor:pointer;">
                    Lapor
                </button>
            </form>

            @if($obatPribadiSaya->isEmpty())
                <p style="color:#002f45; opacity:0.4; font-size:0.85rem; text-align:center; padding:1rem;">Belum ada data obat pribadi yang dilaporkan.</p>
            @else
            <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead>
                    <tr style="background: rgba(0, 47, 69, 0.05);">
                        <th style="padding:0.75rem 1rem; text-align:left; color:#002f45;">Penyakit</th>
                        <th style="padding:0.75rem 1rem; text-align:left; color:#002f45;">Obat</th>
                        <th style="padding:0.75rem 1rem; text-align:left; color:#002f45;">Catatan</th>
                        <th style="padding:0.75rem 1rem; text-align:center; color:#002f45;">Status</th>
                        <th style="padding:0.75rem 1rem; text-align:center; color:#002f45;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($obatPribadiSaya as $o)
                <tr style="border-bottom:1px solid rgba(0,47,69,0.05);">
                    <td style="padding:0.75rem 1rem; color:#002f45; font-weight:700;">{{ $o->penyakit }}</td>
                    <td style="padding:0.75rem 1rem; color:#002f45;">{{ $o->nama_obat ?? '-' }}</td>
                    <td style="padding:0.75rem 1rem; color:#002f45; opacity:0.7;">{{ $o->catatan ?? '-' }}</td>
                    <td style="padding:0.75rem 1rem; text-align:center;">
                        @if($o->sudah_diserahkan)
                            <span style="background: rgba(34, 197, 94, 0.15); color:#166534; font-size:0.7rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px;">✓ Diterima P3K</span>
                        @else
                            <span style="background: rgba(245, 158, 11, 0.15); color:#92400e; font-size:0.7rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px;">Belum Diserahkan</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem 1rem; text-align:center;">
                        @if(!$o->sudah_diserahkan)
                        <form method="POST" action="{{ route('peserta.p3k.obat.destroy', $o->id) }}" onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220,38,38,0.3); padding:0.3rem 0.6rem; border-radius:0.6rem; font-size:0.7rem; cursor:pointer;">Hapus</button>
                        </form>
                        @else
                        <span style="opacity:0.3; font-size:1rem;">🔒</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
