@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1000px; margin:0 auto;">

        {{-- Navigasi & Header --}}
        <div style="margin-bottom:2rem;">
            <a href="{{ route('peserta.index') }}" 
               style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7; transition:0.2s;"
               onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
                Kembali ke Portal
            </a>
            
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.25rem; font-weight:800; margin:0;">
                Logistik Kelompok
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-top:0.5rem; font-weight:500;">
                Kelompok {{ $kelompok }} — Pantau dan perbarui kelengkapan barang bawaan.
            </p>
        </div>

        @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; display:flex; align-items:center; gap:0.75rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($data->isEmpty())
        <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(15px); border-radius:2rem; padding:4rem 2rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2);">
            <div style="font-size:4rem; margin-bottom:1.5rem;">📦</div>
            <p style="color:#002f45; opacity:0.6; font-weight:600;">Belum ada daftar barang yang ditambahkan oleh panitia.</p>
        </div>
        @else

        {{-- Summary Card --}}
        @php
            $totalBarang  = $data->count();
            $barangLengkap = $data->where('is_lengkap', true)->count();
            $persen = $totalBarang > 0 ? round($barangLengkap / $totalBarang * 100) : 0;
        @endphp
        <div style="background: rgba(0, 47, 69, 0.85); backdrop-filter: blur(10px); border-radius:1.5rem; padding:1.75rem 2rem; margin-bottom:2rem; display:flex; align-items:center; gap:2.5rem; flex-wrap:wrap; box-shadow: 0 15px 35px rgba(0, 47, 69, 0.2); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div>
                <div style="color:rgba(210, 194, 150, 0.7); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:700; margin-bottom:0.5rem;">Progress Kelengkapan</div>
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

        {{-- Table --}}
        <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(15px); border-radius:1.5rem; overflow:hidden; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);">
            <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <thead>
                    <tr style="background: rgba(0, 47, 69, 0.05);">
                        <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45;">Nama Barang</th>
                        <th style="padding:1.25rem; text-align:center; color:#002f45;">Kebutuhan</th>
                        <th style="padding:1.25rem; text-align:center; color:#002f45;">Progress</th>
                        <th style="padding:1.25rem; text-align:center; color:#002f45;">Input Data</th>
                        <th style="padding:1.25rem; text-align:center; color:#002f45;">Bukti Foto</th>
                        <th style="padding:1.25rem; text-align:center; color:#002f45;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($data as $item)
                @php
                    $b = $item['barang'];
                    $terkumpul = $item['jumlah_terkumpul'];
                    $lengkap = $item['is_lengkap'];
                    $fotoUrl = $item['foto_url'];
                    $bgRow = $lengkap ? 'rgba(34, 197, 94, 0.05)' : ($terkumpul > 0 ? 'rgba(217, 119, 6, 0.03)' : 'transparent');
                @endphp
                <tr style="background:{{ $bgRow }}; border-bottom:1px solid rgba(0, 47, 69, 0.05);">
                    
                    {{-- Detail Barang --}}
                    <td style="padding:1.25rem 1.5rem;">
                        <div style="color:#002f45; font-weight:700;">{{ $b->nama_barang }}</div>
                        <div style="color:#002f45; opacity:0.5; font-size:0.75rem;">{{ $b->keterangan ?? '-' }}</div>
                        @if($item['updated_by_name'])
                            <div style="color:#002f45; opacity:0.4; font-size:0.65rem; margin-top:0.4rem;">👤 {{ $item['updated_by_name'] }}</div>
                        @endif
                    </td>

                    {{-- Kebutuhan --}}
                    <td style="padding:1.25rem; text-align:center;">
                        <div style="color:#002f45; font-weight:800;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}</div>
                    </td>

                    {{-- Progress --}}
                    <td style="padding:1.25rem; text-align:center;">
                        <span style="font-weight:800; color:{{ $lengkap ? '#15803d' : '#b45309' }};">
                            {{ $terkumpul }}/{{ $b->jumlah_kebutuhan }}
                        </span>
                    </td>

                    {{-- Input & Foto (Satu Form agar sinkron) --}}
                    <td style="padding:1.25rem; text-align:center;">
                        <form method="POST" action="{{ route('peserta.barang.update', $b->id) }}" enctype="multipart/form-data" id="form-{{ $b->id }}">
                            @csrf @method('PATCH')
                            <input type="number" name="jumlah_terkumpul" value="{{ $terkumpul }}" min="0" 
                                   style="width:65px; padding:0.5rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.2); border-radius:0.75rem; text-align:center; font-weight:700;">
                            {{-- Input file hidden, ditaruh di sini supaya masuk ke request form --}}
                            <input type="file" name="foto_bukti" id="file-{{ $b->id }}" accept="image/*" style="display:none;" onchange="document.getElementById('form-{{ $b->id }}').submit()">
                        </form>
                    </td>

                    <td style="padding:1.25rem; text-align:center;">
                        @if($fotoUrl)
                            <div style="position:relative; display:inline-block;">
                                <a href="{{ $fotoUrl }}" target="_blank">
                                    <img src="{{ $fotoUrl }}" style="width:50px; height:50px; object-fit:cover; border-radius:0.75rem; border:2px solid white;">
                                </a>
                                {{-- Tombol Hapus Foto menggunakan FORM DELETE --}}
                                <form action="{{ route('peserta.barang.hapus-foto', $b->id) }}" method="POST" style="position:absolute; top:-8px; right:-8px;">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus foto?')" style="background:#ef4444; color:white; border-radius:50%; width:20px; height:20px; border:none; cursor:pointer; font-size:10px;">✕</button>
                                </form>
                            </div>
                            <label for="file-{{ $b->id }}" style="display:block; margin-top:0.5rem; cursor:pointer; font-size:0.7rem; color:#002f45; font-weight:700; opacity:0.6;">Ganti Foto</label>
                        @else
                            <label for="file-{{ $b->id }}" style="cursor:pointer; display:inline-flex; flex-direction:column; align-items:center; gap:0.25rem; font-size:0.7rem; color:#002f45; background:rgba(255,255,255,0.4); padding:0.5rem 0.75rem; border-radius:0.75rem; border:1px dashed rgba(0,47,69,0.3);">
                                <span>📷 Upload</span>
                            </label>
                        @endif
                    </td>

                    {{-- Tombol Aksi --}}
                    <td style="padding:1.25rem; text-align:center;">
                        <div style="display:flex; flex-direction:column; gap:0.5rem;">
                            <button type="submit" form="form-{{ $b->id }}" style="background:#002f45; color:#d2c296; border:none; padding:0.5rem; border-radius:0.75rem; font-size:0.75rem; font-weight:700; cursor:pointer;">
                                Simpan
                            </button>
                            @if($item['pengumpulan'])
                            <form method="POST" action="{{ route('peserta.barang.reset', $b->id) }}" onsubmit="return confirm('Reset data barang ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220, 38, 38, 0.3); padding:0.3rem; border-radius:0.75rem; font-size:0.7rem; width:100%;">Reset</button>
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
        @endif
    </div>
</div>
@endsection