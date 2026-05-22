@extends('layouts.app')

@section('content')
    {{-- Background Wrapper dengan Gradient Lembut --}}
    <div
        style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:1000px; margin:0 auto;">

            {{-- Header Section (Glass Card) --}}
            <div
                style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem; box-shadow: 0 8px 32px rgba(0, 47, 69, 0.1);">
                <div>
                    <h1
                        style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                        Kelompok {{ $kelompok }} — Detail Logistik
                    </h1>
                    <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem; font-weight:500;">
                        🛡️ View only · Status pengumpulan barang terkini
                    </p>
                </div>
                <a href="{{ route('panitia.barang.index') }}"
                    style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.75rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700; backdrop-filter: blur(5px); transition: 0.3s; box-shadow: 0 4px 15px rgba(0, 47, 69, 0.2);">
                    ← Kembali
                </a>
            </div>

            {{-- Progress Summary (Dark Glass) --}}
            @php
                $totalBarang = $data->count();
                $barangLengkap = $data->where('is_lengkap', true)->count();
                $persen = $totalBarang > 0 ? round(($barangLengkap / $totalBarang) * 100) : 0;
            @endphp
            <div
                style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.75rem 2rem; margin-bottom: 2rem; display:flex; align-items:center; gap:2.5rem; flex-wrap:wrap; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div style="text-align: center;">
                    <div
                        style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem;">
                        Total Progress</div>
                    <div style="color:#d2c296; font-size:2.25rem; font-weight:800; line-height:1;">{{ $persen }}%
                    </div>
                </div>
                <div style="flex:1; min-width:250px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.6rem;">
                        <span style="color:#bdd1d3; font-size:0.85rem; font-weight:500;">{{ $barangLengkap }} dari
                            {{ $totalBarang }} barang lengkap</span>
                        <span style="color:#d2c296; font-size:0.85rem; font-weight:700;">{{ $persen }}%</span>
                    </div>
                    <div
                        style="background: rgba(255,255,255,0.1); border-radius:999px; height:12px; overflow:hidden; border: 1px solid rgba(255,255,255,0.05);">
                        <div
                            style="background: linear-gradient(90deg, #d2c296, #bdd1d3); height:100%; border-radius:999px; width:{{ $persen }}%; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Container (Glass Card) --}}
            <div
                style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                        <thead>
                            <tr style="background: rgba(0, 47, 69, 0.05); border-bottom: 1px solid rgba(0, 47, 69, 0.1);">
                                <th
                                    style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px;">
                                    Barang</th>
                                <th
                                    style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px;">
                                    Target</th>
                                <th
                                    style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px;">
                                    Terkumpul</th>
                                <th
                                    style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px;">
                                    Bukti</th>
                                <th
                                    style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px;">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                @php
                                    $b = $item['barang'];
                                    $terkumpul = $item['jumlah_terkumpul'];
                                    $lengkap = $item['is_lengkap'];
                                    $pct =
                                        $b->jumlah_kebutuhan > 0
                                            ? min(100, round(($terkumpul / $b->jumlah_kebutuhan) * 100))
                                            : 0;
                                @endphp
                                <tr
                                    style="border-bottom: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, {{ $lengkap ? '0.2' : '0.05' }});">
                                    <td style="padding:1.25rem 1.5rem;">
                                        <div style="color:#002f45; font-weight:700; font-size:1rem;">{{ $b->nama_barang }}
                                        </div>
                                        @if ($b->keterangan)
                                            <div style="color:#002f45; opacity:0.5; font-size:0.75rem; font-style:italic;">
                                                {{ $b->keterangan }}</div>
                                        @endif
                                        <div style="color:#002f45; opacity:0.4; font-size:0.7rem; margin-top:4px;">Update:
                                            {{ $item['updated_at'] ? $item['updated_at']->format('d/m H:i') : '-' }}</div>
                                        @if ($item['updated_by_name'])
                                            <div style="color:#002f45; opacity:0.4; font-size:0.65rem; margin-top:0.4rem;">
                                                👤 {{ $item['updated_by_name'] }}</div>
                                        @endif
                                    </td>
                                    <td style="padding:1.25rem 1rem; text-align:center;">
                                        <div style="color:#002f45; font-weight:700;">{{ $b->jumlah_kebutuhan }}</div>
                                        <div
                                            style="color:#002f45; opacity:0.5; font-size:0.7rem; text-transform:uppercase;">
                                            {{ $b->satuan }}</div>
                                    </td>
                                    <td style="padding:1.25rem 1rem; text-align:center;">
                                        <div
                                            style="font-size:1.15rem; font-weight:800; color:{{ $lengkap ? '#16a34a' : ($terkumpul > 0 ? '#d97706' : '#dc2626') }};">
                                            {{ $terkumpul }}
                                        </div>
                                        <div
                                            style="color:#002f45; opacity:0.5; font-size:0.7rem; text-transform:uppercase;">
                                            {{ $b->satuan }}</div>
                                    </td>
                                    <td style="padding:1.25rem 1rem; text-align:center;">
                                        @if ($item['foto'])
                                            <a href="{{ $item['foto'] }}" target="_blank"
                                                style="display:inline-block; transition: 0.3s;"
                                                onmouseover="this.style.transform='scale(1.1)'"
                                                onmouseout="this.style.transform='scale(1)'">
                                                <img src="{{ $item['foto'] }}" alt="Bukti"
                                                    style="width:50px; height:50px; object-fit:cover; border-radius:12px; border:2px solid rgba(255,255,255,0.8); box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                            </a>
                                        @else
                                            <div
                                                style="width:50px; height:50px; background: rgba(0,0,0,0.05); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#002f45; opacity:0.2;">
                                                📷
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding:1.25rem 1rem; text-align:center;">
                                        @if ($lengkap)
                                            <span
                                                style="background: rgba(34, 197, 94, 0.2); color:#166534; font-size:0.7rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:10px; border: 1px solid rgba(34, 197, 94, 0.2); text-transform:uppercase;">Lengkap</span>
                                        @elseif($terkumpul > 0)
                                            <span
                                                style="background: rgba(245, 158, 11, 0.2); color:#92400e; font-size:0.7rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:10px; border: 1px solid rgba(245, 158, 11, 0.2); text-transform:uppercase;">Sebagian</span>
                                        @else
                                            <span
                                                style="background: rgba(239, 68, 68, 0.15); color:#991b1b; font-size:0.7rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:10px; border: 1px solid rgba(239, 68, 68, 0.15); text-transform:uppercase;">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
