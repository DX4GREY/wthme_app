@extends('layouts.app')

@section('content')
<div style="min-height:100vh; padding:4rem 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); font-family:'Inter',sans-serif;">
    <div style="max-width:900px; margin:0 auto;">

        {{-- Header --}}
        <div style="text-align:center; margin-bottom:3rem;">
            <span style="display:inline-block; padding:0.5rem 1.25rem; background:rgba(0,47,69,0.05); border-radius:2rem; color:#002f45; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:1rem;">
                Suara Peserta
            </span>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                Kotak <span style="color:#6b705c; font-style:italic;">Suara</span>
            </h1>
            <p style="color:#002f45; opacity:0.5; font-size:1.1rem; margin-top:0.75rem;">
                Semua masukan, kritik, saran, dan keluhan dari peserta
            </p>
        </div>

        {{-- Success Alert --}}
        @if (session('success'))
        <div style="background:#d4edda; border:1px solid #c3e6cb; border-radius:1rem; padding:1rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:1.5rem;">✅</span>
            <span style="color:#155724; font-weight:600;">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Statistik --}}
        @php
            $total = $suaraList->total();
            $belumDibaca = $suaraList->filter(function($s) { return !$s->dibaca; })->count();
        @endphp
        <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
            <div style="background:rgba(255,255,255,0.4); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.5); border-radius:1.5rem; padding:1rem 1.5rem; flex:1; min-width:150px;">
                <span style="font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#002f45; opacity:0.6;">Total</span>
                <div style="font-size:2rem; font-weight:800; color:#002f45;">{{ $total }}</div>
            </div>
            <div style="background:rgba(255,255,255,0.4); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.5); border-radius:1.5rem; padding:1rem 1.5rem; flex:1; min-width:150px;">
                <span style="font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#002f45; opacity:0.6;">Belum Dibaca</span>
                <div style="font-size:2rem; font-weight:800; color:{{ $belumDibaca > 0 ? '#ef4444' : '#002f45' }};">{{ $belumDibaca }}</div>
            </div>
        </div>

        {{-- Daftar Suara --}}
        @forelse ($suaraList as $suara)
        <div style="background:rgba(255,255,255,0.4); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.5); border-radius:1.5rem; padding:1.5rem; margin-bottom:1rem; transition:all 0.3s; {{ !$suara->dibaca ? 'border-left:4px solid #002f45;' : '' }}"
            onmouseover="this.style.background='rgba(255,255,255,0.7)'"
            onmouseout="this.style.background='rgba(255,255,255,0.4)'">
            
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                <div style="flex:1;">
                    {{-- Header Info --}}
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem; flex-wrap:wrap;">
                        {{-- Status Dibaca --}}
                        @if (!$suara->dibaca)
                        <span style="background:#002f45; color:white; font-size:0.6rem; font-weight:800; padding:0.2rem 0.6rem; border-radius:0.5rem; letter-spacing:0.05em;">
                            BARU
                        </span>
                        @else
                        <span style="background:rgba(0,47,69,0.05); color:#002f45; opacity:0.4; font-size:0.6rem; font-weight:800; padding:0.2rem 0.6rem; border-radius:0.5rem; letter-spacing:0.05em;">
                            DIBACA
                        </span>
                        @endif

                        {{-- Status Anonim --}}
                        @if ($suara->anonim)
                        <span style="background:rgba(107,112,92,0.1); color:#6b705c; font-size:0.6rem; font-weight:800; padding:0.2rem 0.6rem; border-radius:0.5rem;">
                            🕵️ ANONIM
                        </span>
                        @endif
                    </div>

                    {{-- Pengirim (untuk panitia, identitas sebenarnya tetap terlihat) --}}
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                        <div style="width:40px; height:40px; border-radius:50%; background:#e0decd; display:flex; align-items:center; justify-content:center; font-weight:700; color:#002f45; font-size:0.85rem; flex-shrink:0;">
                            {{ strtoupper(substr($suara->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="color:#002f45; font-weight:600; font-size:0.9rem;">
                                {{ $suara->anonim ? '🕵️ Peserta (Anonim)' : $suara->user->name }}
                                <span style="color:#002f45; opacity:0.4; font-weight:400; font-size:0.8rem;">
                                    ({{ $suara->user->nim }})
                                </span>
                            </div>
                            <div style="color:#002f45; opacity:0.4; font-size:0.75rem;">
                                Kel. {{ $suara->user->kelompok }} · {{ $suara->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    {{-- Pesan (dipotong) --}}
                    <p style="color:#002f45; opacity:0.8; font-size:0.9rem; line-height:1.6; margin:0;">
                        {{ Str::limit($suara->pesan, 200) }}
                    </p>

                    {{-- Ada foto? --}}
                    @if ($suara->foto)
                    <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                        <span style="font-size:0.8rem;">📷</span>
                        <span style="color:#002f45; opacity:0.5; font-size:0.8rem;">Foto terlampir</span>
                    </div>
                    @endif
                </div>

                {{-- Tombol Lihat --}}
                <a href="{{ route('panitia.suara.show', $suara->id) }}" 
                    style="text-decoration:none; background:#002f45; color:white; font-size:0.75rem; font-weight:700; padding:0.6rem 1.2rem; border-radius:0.75rem; white-space:nowrap; transition:all 0.3s; display:flex; align-items:center; gap:0.4rem; flex-shrink:0;"
                    onmouseover="this.style.background='#001f2e'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'">
                    Lihat Suara
                    <span style="font-size:1rem;">→</span>
                </a>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:4rem 2rem; background:rgba(255,255,255,0.2); border-radius:2rem;">
            <div style="font-size:4rem; margin-bottom:1rem;">📭</div>
            <h3 style="color:#002f45; font-weight:700; margin:0 0 0.5rem 0;">Belum Ada Suara</h3>
            <p style="color:#002f45; opacity:0.5; margin:0;">Belum ada peserta yang mengirimkan suara.</p>
        </div>
        @endforelse

        {{-- Pagination --}}
        @if ($suaraList->hasPages())
        <div style="margin-top:2rem;">
            {{ $suaraList->links() }}
        </div>
        @endif

    </div>
</div>

{{-- Floating Back Button --}}
<a href="{{ route('panitia.index') }}"
    style="position:fixed; bottom:2rem; left:2rem; z-index:100; text-decoration:none; background:rgba(255,255,255,0.8); backdrop-filter:blur(10px); padding:0.75rem 1.25rem; border-radius:999px; border:1px solid rgba(0,47,69,0.1); color:#002f45; font-size:0.85rem; font-weight:700; box-shadow:0 10px 25px rgba(0,0,0,0.05); display:flex; align-items:center; gap:0.5rem; transition:0.3s;"
    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)';"
    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)';">
    <span>⬅️</span> Dashboard Panitia
</a>
@endsection