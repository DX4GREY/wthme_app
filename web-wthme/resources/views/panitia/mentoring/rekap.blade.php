@extends('layouts.app')

@section('content')
    {{-- Background Wrapper dengan Gradient Lembut --}}
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #f4f3ee 0%, #e0decd 100%);">
        <div style="max-width:1400px; margin:0 auto;">

            {{-- Header Section (Glass Card) --}}
            <div style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 1.5rem; padding: 2rem; display:flex; justify-content:space-between; align-items:center; margin-bottom:3rem; box-shadow: 0 8px 32px 0 rgba(0, 47, 69, 0.1);">
                <div>
                    <a href="{{ route('panitia.mentoring.index') }}"
                        style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                        <span>←</span> Kembali
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0;">
                        📋 Rekapitulasi
                    </h1>
                    <p style="color:#002f45; opacity:0.7; margin-top:0.5rem; font-size:1.1rem; font-weight:500;">
                        Klasifikasi berdasarkan Kelompok & Jenis Kegiatan
                    </p>
                </div>
                <a href="{{ route('panitia.mentoring.export_seluruh') }}"
                    style="padding:1rem 2rem; background: rgba(112, 173, 71, 0.8); backdrop-filter: blur(5px); color:white; border-radius:1rem; text-decoration:none; font-weight:700; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(112, 173, 71, 0.3); transition: transform 0.2s;">
                    📥 Download Excel
                </a>
            </div>

            @foreach ($rekapDetail as $noKelompok => $perKegiatan)
                <div style="margin-bottom:5rem;">
                    {{-- JUDUL KELOMPOK --}}
                    <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom:2rem;">
                        <div style="width:60px; height:60px; background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(5px); color:#d2c296; display:flex; align-items:center; justify-content:center; border-radius:18px; font-weight:800; font-size:1.6rem; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                            {{ $noKelompok }}
                        </div>
                        <h2 style="font-family:'Playfair Display',serif; color:#002f45; margin:0; font-size:2rem; letter-spacing:1px;">
                            KELOMPOK {{ $noKelompok }}
                        </h2>
                        <div style="flex-grow:1; height:1px; background: linear-gradient(to right, rgba(0,47,69,0.3), transparent);"></div>
                    </div>

                    {{-- GRID KEGIATAN --}}
                    <div style="display:grid; grid-template-columns: 1fr; gap:2.5rem; padding-left:1.5rem; border-left:2px solid rgba(0,47,69,0.1);">
                        @foreach ($perKegiatan as $namaKegiatan => $details)
                            <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);">

                                {{-- Sub-Header (Glass Top) --}}
                                <div style="padding:1.2rem 2rem; background: rgba(255, 255, 255, 0.3); border-bottom:1px solid rgba(255,255,255,0.2); display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-weight:800; color:#002f45; font-size:1.1rem; letter-spacing:0.5px;">
                                        📌 {{ strtoupper($namaKegiatan) }}
                                    </span>
                                    <span style="font-size:0.85rem; font-weight:700; color:#002f45; background: rgba(255, 255, 255, 0.5); padding:0.4rem 1rem; border-radius:12px; border: 1px solid rgba(255,255,255,0.3);">
                                        📅 {{ date('d M Y', strtotime($details->first()->mentoring->tanggal)) }}
                                    </span>
                                </div>

                                {{-- Table Area --}}
                                <div style="overflow-x: auto; padding: 0.5rem;">
                                    <table style="width:100%; border-collapse:separate; border-spacing: 0 0.5rem;">
                                        <thead>
                                            <tr>
                                                <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">Nama Peserta</th>
                                                <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">NIM</th>
                                                <th style="padding:1rem 1.5rem; text-align:center; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">Status</th>
                                                <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($details as $rd)
                                                <tr style="background: rgba(255, 255, 255, 0.15); transition: background 0.3s;">
                                                    <td style="padding:1rem 1.5rem; font-weight:700; color:#002f45; border-radius: 12px 0 0 12px;">{{ $rd->peserta->name }}</td>
                                                    <td style="padding:1rem 1.5rem; color:#002f45; font-size:0.9rem; opacity:0.8;">{{ $rd->peserta->nim }}</td>
                                                    <td style="padding:1rem 1.5rem; text-align:center;">
                                                        <span style="display:inline-block; padding:0.4rem 0.8rem; border-radius:10px; font-size:0.7rem; font-weight:800; letter-spacing:0.5px;
                                                            {{ $rd->kehadiran === 'Hadir' ? 'background:rgba(34, 197, 94, 0.2); color:#166534; border: 1px solid rgba(34, 197, 94, 0.3);' : 
                                                               ($rd->kehadiran === 'Izin' ? 'background:rgba(249, 115, 22, 0.2); color:#9a3412; border: 1px solid rgba(249, 115, 22, 0.3);' : 
                                                               'background:rgba(239, 68, 68, 0.2); color:#991b1b; border: 1px solid rgba(239, 68, 68, 0.3);') }}">
                                                            {{ strtoupper($rd->kehadiran) }}
                                                        </span>
                                                    </td>
                                                    <td style="padding:1rem 1.5rem; color:#002f45; font-size:0.85rem; font-style:italic; opacity:0.6; border-radius: 0 12px 12px 0;">
                                                        {{ $rd->keterangan ?? '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Footer (Glass Bottom) --}}
                                <div style="padding:1rem 2rem; background: rgba(255, 255, 255, 0.2); border-top:1px solid rgba(255,255,255,0.1); display:flex; gap:2rem; font-size:0.85rem;">
                                    <span style="color:#166534; font-weight:600;">
                                        <span style="opacity:0.7;">✅ Hadir:</span> <strong>{{ $details->where('kehadiran', 'Hadir')->count() }}</strong>
                                    </span>
                                    <span style="color:#991b1b; font-weight:600;">
                                        <span style="opacity:0.7;">❌ Tidak Hadir:</span> <strong>{{ $details->whereIn('kehadiran', ['Izin', 'Alpha'])->count() }}</strong>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>
@endsection