@extends('layouts.app')

@section('content')
    {{-- Main Background --}}
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #d2c296 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1200px; margin:0 auto;">

            {{-- Header & Action Bar --}}
            <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; margin-bottom:1rem; transition:0.3s;"
                        onmouseover="this.style.opacity='1'; this.style.transform='translateX(-5px)'"
                        onmouseout="this.style.opacity='0.7'; this.style.transform='translateX(0)'">
                        <span style="margin-right:8px;">←</span> Kembali
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Data Kesehatan <span style="color:#6b705c; font-style:italic;">Peserta</span>
                    </h1>
                </div>

                <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
                    <form action="{{ route('panitia.kesehatan.index') }}" method="GET" style="display:flex; gap:0.5rem; background: rgba(255, 255, 255, 0.2); padding: 0.5rem; border-radius: 1rem; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3);">
                        <select name="kelompok" style="padding:0.6rem 1rem; border-radius:0.75rem; border:none; font-size:0.875rem; background:white; color:#002f45; outline:none; cursor:pointer;">
                            <option value="">Semua Kelompok</option>
                            @foreach ($kelompokList as $k)
                                <option value="{{ $k }}" {{ request('kelompok') == $k ? 'selected' : '' }}>Kelompok {{ $k }}</option>
                            @endforeach
                        </select>
                        <button type="submit" style="background:#002f45; color:white; border:none; padding:0.6rem 1.2rem; border-radius:0.75rem; cursor:pointer; font-weight:600; font-size:0.875rem;">
                            Filter
                        </button>
                    </form>

                    <a href="{{ route('panitia.export.kesehatan') }}"
                        style="padding:0.8rem 1.5rem; background: rgba(0, 47, 69, 0.9); color:#d2c296; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; backdrop-filter: blur(10px); transition:0.3s; display:flex; align-items:center; gap:8px;">
                        <span>⬇</span> Export Excel
                    </a>
                </div>
            </div>

            @if ($semuaRiwayat->isEmpty())
                <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(20px); border-radius: 2rem; padding: 5rem 2rem; text-align: center; border: 1px solid rgba(255, 255, 255, 0.3);">
                    <div style="font-size:5rem; margin-bottom:1.5rem;">🩺</div>
                    <h3 style="color:#002f45;">Data Belum Tersedia</h3>
                </div>
            @else
                @foreach ($semuaRiwayat as $kelompok => $dataKesehatan)
                    <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius: 1.5rem; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.4); margin-bottom: 2.5rem; box-shadow: 0 15px 35px rgba(0,0,0,0.05);">
                        
                        <div style="background: rgba(0, 47, 69, 0.85); padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color:#d2c296; font-weight:800; letter-spacing: 0.1em; font-size:0.9rem;">KELOMPOK {{ $kelompok }}</span>
                            <span style="color:white; background:rgba(255,255,255,0.15); padding:0.4rem 1rem; border-radius:2rem; font-size:0.8rem;">
                                {{ $dataKesehatan->count() }} Peserta
                            </span>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width:100%; border-collapse:collapse; min-width: 1000px;">
                                <thead>
                                    <tr style="background: rgba(255, 255, 255, 0.1);">
                                        <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; border-bottom: 1px solid rgba(0,0,0,0.05);">Nama & NIM</th>
                                        <th style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Status</th>
                                        <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Riwayat Penyakit</th>
                                        <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Alergi</th>
                                        <th style="padding:1.25rem 1rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Obat Rutin</th>
                                        <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; font-weight:800; text-transform:uppercase; border-bottom: 1px solid rgba(0,0,0,0.05);">Catatan Tambahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataKesehatan as $r)
                                        @php
                                            $isWarning = $r->kondisi_kesehatan == 'Perlu Perhatian';
                                            $statusColor = $isWarning ? '#c53030' : ($r->kondisi_kesehatan == 'Cukup' ? '#975a16' : '#2f855a');
                                            $statusBg = $isWarning ? 'rgba(254, 215, 215, 0.6)' : ($r->kondisi_kesehatan == 'Cukup' ? 'rgba(254, 252, 191, 0.6)' : 'rgba(198, 246, 213, 0.6)');
                                        @endphp
                                        <tr style="border-bottom:1px solid rgba(0,0,0,0.03); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='transparent'">
                                            
                                            <td style="padding:1.25rem 1.5rem;">
                                                <div style="color:#002f45; font-weight:700; font-size:0.9rem;">{{ $r->nama }}</div>
                                                <div style="color:#002f45; font-size:0.75rem; opacity:0.6; font-family:monospace;">{{ $r->nim }}</div>
                                            </td>

                                            <td style="padding:1.25rem 1rem; text-align:center;">
                                                <span style="background:{{ $statusBg }}; color:{{ $statusColor }}; padding:0.4rem 0.6rem; border-radius:0.5rem; font-weight:800; font-size:0.65rem; display:inline-block; border: 1px solid {{ $statusColor }}30; backdrop-filter: blur(5px); white-space: nowrap;">
                                                    {{ strtoupper($r->kondisi_kesehatan) }}
                                                </span>
                                            </td>

                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.85rem;">
                                                {{ $r->riwayat_penyakit ?? '-' }}
                                            </td>

                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.85rem;">
                                                {{ $r->alergi ?? '-' }}
                                            </td>

                                            <td style="padding:1.25rem 1rem; color:#002f45; font-size:0.85rem;">
                                                <span style="font-weight: 600;">{{ $r->obat_rutin ?? '-' }}</span>
                                            </td>

                                            <td style="padding:1.25rem 1.5rem;">
                                                @if ($r->keterangan_tambahan)
                                                    <div style="font-size:0.8rem; color:#c53030; padding:0.5rem; background:rgba(197,48,48,0.05); border-left:3px solid #c53030; border-radius: 4px; font-style: italic;">
                                                        {{ $r->keterangan_tambahan }}
                                                    </div>
                                                @else
                                                    <span style="color:#ccc;">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
@endsection