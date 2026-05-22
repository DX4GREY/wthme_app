@extends('layouts.app')

@section('content')
{{-- Main Background with Glassmorphism Theme --}}
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #d2c296 100%); font-family: 'Inter', sans-serif;">
    <div style="max-width:1100px; margin:0 auto;">

        {{-- Header & Action Bar --}}
        <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <a href="{{ route('panitia.index') }}" 
                   style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; margin-bottom:1rem; transition:0.3s;"
                   onmouseover="this.style.opacity='1'; this.style.transform='translateX(-5px)'"
                   onmouseout="this.style.opacity='0.7'; this.style.transform='translateX(0)'">
                   <span style="margin-right:8px;">←</span> Kembali ke Dashboard
                </a>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Absensi <span style="color:#6b705c; font-style:italic;">Panitia</span>
                </h1>
            </div>
            
            <a href="{{ route('panitia.export.panitia') }}"
               style="padding:0.8rem 1.5rem; background: rgba(0, 47, 69, 0.9); color:#d2c296; border-radius:1rem; text-decoration:none; font-size:0.875rem; font-weight:700; backdrop-filter: blur(10px); transition:0.3s; display:flex; align-items:center; gap:8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);"
               onmouseover="this.style.background='#002f45'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='rgba(0, 47, 69, 0.9)'; this.style.transform='translateY(0)'">
                <span>⬇</span> Export Excel
            </a>
        </div>

        @if($absensi->isEmpty())
        <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(20px); border-radius: 2rem; padding: 5rem 2rem; text-align: center; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
            <div style="font-size:5rem; margin-bottom:1.5rem; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.1));">📊</div>
            <h3 style="color:#002f45; font-size:1.5rem; margin-bottom:0.5rem;">Data Kosong</h3>
            <p style="color:#002f45; opacity:0.6; max-width:400px; margin:0 auto;">Belum ada data absensi panitia yang tercatat pada sistem kami.</p>
        </div>
        @else
        
        @foreach($absensi as $divisi => $dataDivisi)
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius: 1.5rem; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.4); margin-bottom: 2.5rem; box-shadow: 0 15px 35px rgba(0,0,0,0.05);">
            
            {{-- Section Header --}}
            <div style="background: rgba(0, 47, 69, 0.85); padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; backdrop-filter: blur(5px);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:10px; height:10px; background:#d2c296; border-radius:50%;"></div>
                    <span style="color:#d2c296; font-weight:800; letter-spacing: 0.1em; font-size:0.9rem; text-transform:uppercase;">DIVISI {{ $divisi }}</span>
                </div>
                <span style="color:white; background:rgba(255,255,255,0.15); padding:0.4rem 1rem; border-radius:2rem; font-size:0.8rem; font-weight:500; border: 1px solid rgba(255,255,255,0.2);">
                    {{ $dataDivisi->count() }} Orang Hadir
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background: rgba(255, 255, 255, 0.1);">
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border-bottom: 1px solid rgba(0,0,0,0.05);">No</th>
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border-bottom: 1px solid rgba(0,0,0,0.05);">Nama Panitia</th>
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border-bottom: 1px solid rgba(0,0,0,0.05);">NIM</th>
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border-bottom: 1px solid rgba(0,0,0,0.05);">Sesi</th>
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; border-bottom: 1px solid rgba(0,0,0,0.05);">Waktu Presensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataDivisi as $i => $absen)
                        <tr style="border-bottom:1px solid rgba(0,0,0,0.03); transition: 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.4)'"
                            onmouseout="this.style.background='transparent'">
                            <td style="padding:1.25rem 1.5rem; color:#002f45; opacity:0.5; font-family:monospace;">{{ sprintf('%02d', $i + 1) }}</td>
                            <td style="padding:1.25rem 1.5rem;">
                                <div style="color:#002f45; font-weight:700; font-size:0.95rem;">{{ $absen->nama }}</div>
                            </td>
                            <td style="padding:1.25rem 1.5rem; color:#002f45; font-size:0.85rem; opacity:0.7; font-family:monospace;">{{ $absen->nim }}</td>
                            <td style="padding:1.25rem 1.5rem;">
                                <span style="background: rgba(0, 47, 69, 0.05); color: #002f45; padding: 0.3rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600;">
                                    {{ $absen->qrSession->nama_sesi ?? '-' }}
                                </span>
                            </td>
                            <td style="padding:1.25rem 1.5rem;">
                                <div style="color:#002f45; font-size:0.8rem; font-weight:500;">
                                    <span style="opacity:0.6;">📅</span> {{ $absen->waktu_absen->format('d M') }}
                                    <span style="margin-left:8px; opacity:0.6;">⏰</span> {{ $absen->waktu_absen->format('H:i') }}
                                </div>
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