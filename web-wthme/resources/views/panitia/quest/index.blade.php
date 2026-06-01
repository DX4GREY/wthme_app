@extends('layouts.app')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Inter', sans-serif;">
    <div style="max-width: 1000px; margin: 0 auto;">
        
        {{-- Header Section --}}
        <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end; wrap: wrap; gap: 1.5rem;">
            <div>
                <a href="{{ route('panitia.index') }}"
                    style="text-decoration: none; color: #002f45; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; opacity: 0.7; transition: 0.3s; margin-bottom: 0.5rem;"
                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    ⬅ Kembali ke Dashboard
                </a>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Verifikasi <span style="color:#6b705c; font-style:italic;">Lab Elektro</span>
                </h1>
                <p style="color: #002f45; opacity: 0.6; margin: 5px 0 0 0;">
                    Antrean verifikasi diurutkan berdasarkan <b>waktu kirim asli peserta (Submitted At)</b>.
                </p>
            </div>

            {{-- FITUR: Tombol ACC Semua --}}
            @if($pendingQuests->count() > 0)
                <form action="{{ route('panitia.quest.approveAll') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui (ACC) SEMUA quest yang ada di halaman ini?')" style="margin: 0;">
                    @csrf
                    <button type="submit"
                        style="background: #2e7d32; color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 1rem; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(46,125,50,0.2); display: flex; align-items: center; gap: 8px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(46,125,50,0.3)'"
                        onmouseout="this.style.transform='translateY(0)'">
                        ✅ ACC Semua Antrean
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Glassmorphism Table Card --}}
        <div style="background: rgba(255, 255, 255, 0.4); 
                    backdrop-filter: blur(15px); 
                    -webkit-backdrop-filter: blur(15px); 
                    padding: 2rem; 
                    border-radius: 2rem; 
                    border: 1px solid rgba(255, 255, 255, 0.6); 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
                    overflow-x: auto;">
            
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 650px;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(0,47,69,0.1); color: #002f45;">
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8;">WAKTU SUBMIT</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8;">PESERTA</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8;">LABORATORIUM</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8;">BUKTI FOTO</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8; text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingQuests as $pq)
                        <tr style="border-bottom: 1px solid rgba(0,47,69,0.05); transition: 0.3s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                            onmouseout="this.style.background='transparent'">
                            
                            {{-- Waktu --}}
                            <td style="padding: 1.2rem 1rem; font-size: 0.85rem; color: #002f45; opacity: 0.7; font-weight: 600;">
                                {{ $pq->submitted_at->format('d M Y') }}
                                <div style="font-size: 0.75rem; opacity: 0.6; margin-top: 2px;">{{ $pq->submitted_at->format('H:i:s') }} WIB</div>
                            </td>
                            
                            {{-- Nama Peserta --}}
                            <td style="padding: 1.2rem 1rem;">
                                <div style="font-weight: 800; color: #002f45; font-size: 1rem;">{{ $pq->user->name }}</div>
                                <div style="font-size: 0.75rem; color: #6b705c; font-weight: 700; margin-top: 2px;">
                                    👥 Kelompok {{ $pq->user->kelompok }}
                                </div>
                            </td>
                            
                            {{-- Nama Lab --}}
                            <td style="padding: 1.2rem 1rem;">
                                <span style="font-size: 0.8rem; font-weight: 800; background: rgba(107,112,92,0.15); color: #6b705c; padding: 0.4rem 0.8rem; border-radius: 2rem; letter-spacing: 0.5px;">
                                    🏢 {{ strtoupper($pq->nama_lab) }}
                                </span>
                            </td>
                            
                            {{-- Foto Selfie --}}
                            <td style="padding: 1.2rem 1rem;">
                                <a href="{{ asset('storage/quests/'.$pq->foto_selfie) }}" target="_blank" style="display: inline-block; transition: 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                    <img src="{{ asset('storage/quests/'.$pq->foto_selfie) }}" style="width: 80px; height: 50px; object-fit: cover; border-radius: 12px; border: 2px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                                </a>
                            </td>
                            
                            {{-- Tombol Aksi Satuan --}}
                            <td style="padding: 1.2rem 1rem; text-align: center;">
                                <div style="display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                    <form action="{{ route('panitia.quest.approve', $pq->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button style="background: #2e7d32; color: white; border: none; padding: 0.5rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.3s;"
                                                onmouseover="this.style.background='#1b5e20'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#2e7d32'; this.style.transform='translateY(0)';">
                                            ACC
                                        </button>
                                    </form>
                                    <form action="{{ route('panitia.quest.reject', $pq->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.3s;"
                                                onmouseover="this.style.background='#dc2626'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#ef4444'; this.style.transform='translateY(0)';">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 5rem 2rem; text-align: center;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">✨</div>
                                <p style="color: #002f45; opacity: 0.5; font-weight: 700; margin: 0; font-size: 1.1rem;">
                                    Tidak ada antrean pengajuan quest lab yang tertunda saat ini. Bangku bersih!
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination Links --}}
            @if($pendingQuests->hasPages())
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,47,69,0.05);">
                    {{ $pendingQuests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection