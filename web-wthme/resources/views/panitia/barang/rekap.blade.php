@extends('layouts.app')

@section('content')
{{-- Background Wrapper dengan Gradient Lembut --}}
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1200px; margin:0 auto;">

    {{-- Header Section (Glass Card) --}}
    <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem; box-shadow: 0 8px 32px rgba(0, 47, 69, 0.15);">
        <div>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                Rekap Global Pengumpulan
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem; font-weight:500;">
                📊 Monitoring logistik seluruh kelompok dalam satu layar
            </p>
        </div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('panitia.barang.export') }}"
               style="text-decoration:none; background: rgba(22, 163, 74, 0.85); color:white; padding:0.75rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:800; backdrop-filter: blur(5px); transition: 0.3s; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.2);">
                ⬇️ Download Excel
            </a>
            <a href="{{ route('panitia.barang.index') }}"
               style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.75rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700; backdrop-filter: blur(5px); transition: 0.3s;">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Matrix Overview (Frosted Table) --}}
    @if($barangs->isNotEmpty() && $kelompoks->isNotEmpty())
    <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; margin-bottom:3rem; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
        <div style="background: rgba(0, 47, 69, 0.85); padding:1.25rem 1.5rem;">
            <span style="color:#d2c296; font-weight:800; font-size:1rem; letter-spacing:0.5px;">🗺️ Matrix Overview — Semua Kelompok</span>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
                <tr style="background: rgba(255, 255, 255, 0.1); border-bottom: 2px solid rgba(0, 47, 69, 0.1);">
                    <th style="padding:1.25rem; text-align:left; color:#002f45; font-weight:800; position:sticky; left:0; background:rgba(224, 222, 205, 0.95); backdrop-filter: blur(10px); z-index:10; border-right: 1px solid rgba(0,0,0,0.05);">Kelompok</th>
                    @foreach($barangs as $b)
                    <th style="padding:1rem; text-align:center; color:#002f45; font-weight:700; min-width:100px;">
                        <div style="font-size:0.85rem;">{{ $b->nama_barang }}</div>
                        <div style="color:#002f45; opacity:0.5; font-weight:500; font-size:0.7rem;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($kelompoks as $k)
            <tr style="border-bottom: 1px solid rgba(0, 0, 0, 0.05);">
                <td style="padding:1.25rem; font-weight:800; color:#002f45; position:sticky; left:0; background:rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); z-index:5; border-right: 1px solid rgba(0,0,0,0.05);">
                    Klp {{ $k }}
                </td>
                @foreach($barangs as $b)
                @php
                    $row       = collect($rekap[$k])->firstWhere('barang.id', $b->id);
                    $terkumpul = $row ? $row['jumlah_terkumpul'] : 0;
                    $lengkap   = $row ? $row['is_lengkap'] : false;
                    
                    // Translucent status colors
                    $bgStatus = $lengkap ? 'rgba(34, 197, 94, 0.2)' : ($terkumpul > 0 ? 'rgba(245, 158, 11, 0.2)' : 'rgba(239, 68, 68, 0.1)');
                    $colorTxt = $lengkap ? '#166534' : ($terkumpul > 0 ? '#92400e' : '#991b1b');
                @endphp
                <td style="padding:1rem; text-align:center; background:{{ $bgStatus }}; transition: 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                    <span style="color:{{ $colorTxt }}; font-weight:800; font-size:0.9rem;">
                        {{ $terkumpul }}
                    </span>
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Detail Section Title --}}
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.5rem; font-weight:800; margin-bottom:1.5rem; padding-left:0.5rem;">Detail Per Kelompok</h3>

    {{-- Detail Cards --}}
    <div style="display: grid; gap: 2rem;">
    @foreach($kelompoks as $k)
    <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div style="background: rgba(0, 47, 69, 0.8); padding:1rem 1.5rem; display:flex; align-items:center; justify-content:space-between;">
            <span style="color:#d2c296; font-weight:800; font-size:1rem;">📦 Kelompok {{ $k }}</span>
            <a href="{{ route('panitia.barang.kelompok', $k) }}"
               style="color:#bdd1d3; font-size:0.75rem; text-decoration:none; font-weight:700; background: rgba(255,255,255,0.1); padding: 0.4rem 0.8rem; border-radius: 0.6rem;">
               DETAIL PENUH →
            </a>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
                <tr style="background: rgba(0, 47, 69, 0.03); border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Barang</th>
                    <th style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Target</th>
                    <th style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Progress</th>
                    <th style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Bukti</th>
                    <th style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rekap[$k] as $item)
            @php
                $b         = $item['barang'];
                $terkumpul = $item['jumlah_terkumpul'];
                $lengkap   = $item['is_lengkap'];
                $rowStyle  = $lengkap ? 'rgba(34, 197, 94, 0.05)' : 'transparent';
            @endphp
            <tr style="background:{{ $rowStyle }}; border-bottom: 1px solid rgba(0,0,0,0.03);">
                <td style="padding:1rem 1.5rem; color:#002f45; font-weight:700;">{{ $b->nama_barang }}</td>
                <td style="padding:1rem; text-align:center; color:#002f45; opacity:0.6; font-weight:600;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}</td>
                <td style="padding:1rem; text-align:center;">
                    <span style="font-weight:800; color:{{ $lengkap ? '#16a34a' : ($terkumpul > 0 ? '#d97706' : '#dc2626') }};">
                        {{ $terkumpul }}/{{ $b->jumlah_kebutuhan }}
                    </span>
                </td>
                <td style="padding:1rem; text-align:center;">
                    @if($item['foto'])
                    <a href="{{ $item['foto'] }}" target="_blank">
                        <img src="{{ $item['foto'] }}" style="width:36px; height:36px; object-fit:cover; border-radius:8px; border:2px solid rgba(255,255,255,0.8); box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    </a>
                    @else
                    <span style="color:#002f45; opacity:0.2;">—</span>
                    @endif
                </td>
                <td style="padding:1rem; text-align:center;">
                    @if($lengkap)
                    <span style="background: rgba(34, 197, 94, 0.15); color:#166534; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px; text-transform:uppercase;">Lengkap</span>
                    @elseif($terkumpul > 0)
                    <span style="background: rgba(245, 158, 11, 0.15); color:#92400e; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px; text-transform:uppercase;">Sebagian</span>
                    @else
                    <span style="background: rgba(239, 68, 68, 0.1); color:#991b1b; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px; text-transform:uppercase;">Belum</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endforeach
    </div>

</div>
</div>
@endsection