@extends('layouts.app')

@section('content')
    {{-- Background Ambient (Menyelaraskan tema dashboard utama) --}}
    <div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Segoe UI', Roboto, sans-serif;">
        <div style="max-width: 1000px; margin: 0 auto;">

            {{-- Header Section --}}
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <span style="display: inline-block; padding: 0.5rem 1.25rem; background: rgba(0,47,69,0.05); border-radius: 2rem; color: #002f45; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1rem;">
                    @if(auth()->check() && auth()->user()->role === 'peserta')
                        ✨ Top 10 Papan Peringkat Teratas ✨
                    @else
                        Live Scoreboard Rangkaian Acara (Mode Panitia - Full View)
                    @endif
                </span>
                <h1 style="font-family: 'Playfair Display', serif; color: #002f45; font-size: 3rem; font-weight: 700; margin: 0; letter-spacing: -0.02em;">
                    Papan Peringkat Peserta
                </h1>
                <p style="color: #002f45; opacity: 0.6; font-size: 1.05rem; margin-top: 0.75rem; font-weight: 400; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                    @if(auth()->check() && auth()->user()->role === 'peserta')
                        Berikut merupakan daftar 10 peserta dengan akumulasi skor tertinggi dari gabungan parameter Absensi, Kecepatan Tugas, dan Keaktifan Forum. Tetap semangat!
                    @else
                        Akumulasi skor real-time dari gabungan parameter Kehadiran Absensi, Kecepatan Penyelesaian Tugas, dan Bonus Tambahan Keaktifan Forum.
                    @endif
                </p>
            </div>

            {{-- Navigasi Kembali / Menu Papan Leaderboard --}}
            <div style="text-align: center; margin-bottom: 3rem;">
                <a href="{{ url()->previous() == route('leaderboard.index') ? url('/dashboard') : url()->previous() }}" 
                   class="btn-back"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; background: rgba(0, 47, 69, 0.95); color: #e0decd; font-weight: 600; font-size: 0.85rem; text-decoration: none; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,47,69,0.15); transition: all 0.3s ease;">
                    <span>←</span> Kembali ke Dashboard Portal
                </a>
            </div>

            {{-- Leaderboard Card Container (Glassmorphism Light Aesthetic) --}}
            <div style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 2rem; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0, 47, 69, 0.05); overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 750px;">
                    <thead>
                        <tr style="color: #002f45; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; border-bottom: 2px solid rgba(0, 47, 69, 0.15);">
                            <th style="padding: 1.25rem 1rem; text-align: center; width: 90px;">Peringkat</th>
                            <th style="padding: 1.25rem 1rem; font-family: 'Playfair Display', serif; font-size: 0.95rem;">Nama Lengkap Peserta</th>
                            <th style="padding: 1.25rem 1rem; text-align: center;">Poin Absen</th>
                            <th style="padding: 1.25rem 1rem; text-align: center;">Poin Tugas</th>
                            <th style="padding: 1.25rem 1rem; text-align: center;">Keaktifan</th>
                            <th style="padding: 1.25rem 1rem; text-align: center; color: #002f45;">Total Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $podiumMedals = [
                                1 => '🥇',
                                2 => '🥈',
                                3 => '🥉',
                            ];
                        @endphp

                        @forelse($rankData as $index => $row)
                            @php 
                                $rankNum = $index + 1; 
                                
                                // Kondisi shading background baris podium
                                $rowBackground = 'transparent';
                                if($rankNum == 1) { $rowBackground = 'rgba(210, 194, 150, 0.25)'; }
                                elseif($rankNum == 2) { $rowBackground = 'rgba(255, 255, 255, 0.4)'; }
                                elseif($rankNum == 3) { $rowBackground = 'rgba(255, 255, 255, 0.2)'; }
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(0, 47, 69, 0.05); background: {{ $rowBackground }};">
                                
                                {{-- Nomor Rank / Medali Emas, Perak, Perunggu --}}
                                <td style="padding: 1.25rem 1rem; text-align: center; font-weight: 800; font-size: 1.25rem;">
                                    @php
                                        $podiumMedalText = null;
                                        if (array_key_exists($rankNum, $podiumMedals)) {
                                            $podiumMedalText = $podiumMedals[$rankNum];
                                        }
                                    @endphp
                                    
                                    @if(!is_null($podiumMedalText))
                                        {{ $podiumMedalText }}
                                    @else
                                        <span style="color: #002f45; opacity: 0.4; font-size: 0.95rem; font-weight: 700;">{{ $rankNum }}</span>
                                    @endif
                                </td>

                                {{-- Identitas Peserta --}}
                                <td style="padding: 1.25rem 1rem;">
                                    <div style="font-weight: 700; color: #002f45; font-size: 1.05rem;">{{ $row['name'] }}</div>
                                    <div style="font-size: 0.8rem; color: #002f45; opacity: 0.5; margin-top: 0.25rem; font-weight: 500;">
                                        NIM. {{ $row['nim'] }} &bull; Kelompok {{ $row['kelompok'] }}
                                    </div>
                                </td>

                                {{-- Rincian Nilai Komponen --}}
                                <td style="padding: 1.25rem 1rem; text-align: center; color: #002f45; opacity: 0.7; font-size: 0.95rem; font-weight: 600;">+{{ $row['poin_absen'] }}</td>
                                <td style="padding: 1.25rem 1rem; text-align: center; color: #002f45; opacity: 0.7; font-size: 0.95rem; font-weight: 600;">+{{ $row['poin_tugas'] }}</td>
                                <td style="padding: 1.25rem 1rem; text-align: center; font-weight: 700; font-size: 0.95rem; color: {{ $row['poin_keaktifan'] >= 0 ? '#2e7d32' : '#c62828' }};">
                                    {{ $row['poin_keaktifan'] >= 0 ? '+' : '' }}{{ $row['poin_keaktifan'] }}
                                </td>

                                {{-- Total Akumulasi Mutlak --}}
                                <td style="padding: 1.25rem 1rem; text-align: center; font-weight: 800; color: #002f45; font-size: 1.25rem;">
                                    {{ $row['total'] }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 4rem; text-align: center; color: #002f45; opacity: 0.5; font-size: 0.95rem;">
                                    Belum tersedia data master peserta untuk dikomparasikan ke papan peringkat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Interaktivitas Efek Hover --}}
    <style>
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,47,69,0.25) !important;
            background: #001f2e !important;
        }
        tbody tr:hover {
            background: rgba(255, 255, 255, 0.5) !important;
        }
    </style>
@endsection