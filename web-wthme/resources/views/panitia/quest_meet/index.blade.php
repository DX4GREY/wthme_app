@extends('layouts.app')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #bdd1d3 100%); padding: 4rem 1.5rem; font-family: system-ui, -apple-system, sans-serif;">
    <div style="max-width: 1150px; margin: 0 auto;">
        
        {{-- Header Section --}}
        <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <a href="{{ route('panitia.index') }}"
                    style="text-decoration: none; color: #002f45; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; opacity: 0.7; transition: 0.3s; margin-bottom: 0.5rem;"
                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    ⬅ Kembali ke Dashboard
                </a>
                <h1 style="font-family:'Playfair Display', serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Verifikasi <span style="color:#6b705c; font-style:italic;">Meet KBM Elektro</span>
                </h1>
                <p style="color: #002f45; opacity: 0.6; margin: 5px 0 0 0; font-weight: 500;">
                    Memvalidasi bukti foto pertemuan seru peserta dengan abang-abang/alumni KBM Elektro.
                </p>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div style="background: #2e7d32; color: white; padding: 1rem 1.5rem; border-radius: 1rem; margin-bottom: 1.5rem; font-weight: 600; box-shadow: 0 4px 12px rgba(46,125,50,0.15);">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #ef4444; color: white; padding: 1rem 1.5rem; border-radius: 1rem; margin-bottom: 1.5rem; font-weight: 600; box-shadow: 0 4px 12px rgba(239,68,68,0.15);">
                ⚠ {{ session('error') }}
            </div>
        @endif

        {{-- Glassmorphism Table Card --}}
        <div style="background: rgba(255, 255, 255, 0.35); 
                    backdrop-filter: blur(15px); 
                    -webkit-backdrop-filter: blur(15px); 
                    padding: 2rem; 
                    border-radius: 2rem; 
                    border: 1px solid rgba(255, 255, 255, 0.5); 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.03);
                    overflow-x: auto;">
            
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(0,47,69,0.1); color: #002f45;">
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8; width: 25%;">PESERTA</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8; width: 40%;">TARGET MEET</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8; width: 15%;">BUKTI FOTO</th>
                        <th style="padding: 1rem; font-size: 0.8rem; font-weight: 800; opacity: 0.8; text-align: center; width: 20%;">STATUS / AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $sub)
                        @php
                            // Ambil data string abang, bersihkan spasi, dan pecah jadi array
                            $rawList = is_array($sub->selected_abang) ? $sub->selected_abang : explode(',', $sub->selected_abang);
                            $listNama = array_filter(array_map('trim', $rawList));
                            $totalAbang = count($listNama);
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(0,47,69,0.05); transition: 0.3s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                            onmouseout="this.style.background='transparent'">
                            
                            {{-- Info Peserta --}}
                            <td style="padding: 1.2rem 1rem; vertical-align: top;">
                                <div style="font-weight: 800; color: #002f45; font-size: 1rem; margin-bottom: 4px;">
                                    {{ $sub->peserta->name ?? 'User Terhapus' }}
                                </div>
                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                    <span style="font-size: 0.7rem; font-weight: 700; background: rgba(0,47,69,0.06); color: #002f45; padding: 0.15rem 0.4rem; border-radius: 5px;">
                                        NIM: {{ $sub->peserta->nim ?? '-' }}
                                    </span>
                                    <span style="font-size: 0.7rem; font-weight: 700; background: rgba(107,112,92,0.15); color: #4f5243; padding: 0.15rem 0.4rem; border-radius: 5px;">
                                        Kelompok: {{ $sub->peserta->kelompok ?? '-' }}
                                    </span>
                                </div>
                                <div style="font-size: 0.7rem; color: #002f45; opacity: 0.5; font-weight: 600; margin-top: 6px;">
                                    Dikirim {{ $sub->created_at->diffForHumans() }}
                                </div>
                            </td>
                            
                            {{-- Target Meet (Info Total + Scrollbox Rapi dengan Angka) --}}
                            <td style="padding: 1.2rem 1rem; vertical-align: top; max-width: 340px;">
                                <div style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                    <span style="font-size: 0.68rem; font-weight: 800; background: #002f45; color: #d2c296; padding: 0.2rem 0.4rem; border-radius: 4px; text-transform: uppercase;">
                                        Angkatan {{ $sub->kategori_angkatan }}
                                    </span>
                                    <span style="font-size: 0.68rem; font-weight: 700; background: rgba(0,47,69,0.08); color: #002f45; padding: 0.2rem 0.4rem; border-radius: 4px; text-transform: capitalize;">
                                        {{ $sub->tipe_meet }}
                                    </span>
                                    <span style="font-size: 0.68rem; font-weight: 700; background: rgba(46,125,50,0.12); color: #2e7d32; padding: 0.2rem 0.4rem; border-radius: 4px;">
                                        👥 {{ $totalAbang }} Orang (+{{ $totalAbang * 50 }} Poin)
                                    </span>
                                </div>

                                {{-- Box Pendek Ber-Scroll dengan Penomoran Asli Laravel --}}
                                <div style="max-height: 120px; overflow-y: auto; background: rgba(255, 255, 255, 0.5); padding: 0.4rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(0,47,69,0.15); box-sizing: border-box;">
                                    <div style="font-size: 0.72rem; color: #002f45; font-weight: 600; line-height: 1.45;">
                                        @foreach($listNama as $nama)
                                            <div style="display: flex; align-items: flex-start; gap: 6px; margin-bottom: 3px;">
                                                <span style="color: #6b705c; font-weight: 700; min-width: 18px; text-align: right; display: inline-block;">
                                                    {{ $loop->iteration }}.
                                                </span>
                                                <span style="flex: 1;">{{ $nama }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Foto Bukti --}}
                            <td style="padding: 1.2rem 1rem; vertical-align: top;">
                                <a href="{{ asset($sub->foto_bukti) }}" target="_blank" style="display: inline-block; transition: 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                    <img src="{{ asset($sub->foto_bukti) }}" style="width: 90px; height: 60px; object-fit: cover; border-radius: 12px; border: 2px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                                </a>
                            </td>
                            
                            {{-- Aksi Validasi --}}
                            <td style="padding: 1.2rem 1rem; text-align: center; vertical-align: top;">
                                @if($sub->status === 'pending')
                                    <div style="display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                        <form action="{{ route('panitia.meet.approve', $sub->id) }}" method="POST" style="margin:0;">
                                            @csrf
                                            <button type="submit" style="background: #2e7d32; color: white; border: none; padding: 0.55rem 1.2rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.2s;"
                                                    onmouseover="this.style.background='#1b5e20'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#2e7d32'; this.style.transform='translateY(0)';">
                                                ACC
                                            </button>
                                        </form>
                                        <form action="{{ route('panitia.meet.reject', $sub->id) }}" method="POST" style="margin:0;">
                                            @csrf
                                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 0.55rem 1.2rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.2s;"
                                                    onmouseover="this.style.background='#dc2626'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#ef4444'; this.style.transform='translateY(0)';">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @elseif($sub->status === 'approved')
                                    <span style="font-size: 0.8rem; font-weight: 700; background: rgba(46,125,50,0.15); color: #2e7d32; padding: 0.5rem 1rem; border-radius: 10px; display: inline-block;">
                                        ✅ Disetujui
                                    </span>
                                @else
                                    <span style="font-size: 0.8rem; font-weight: 700; background: rgba(239,68,68,0.15); color: #ef4444; padding: 0.5rem 1rem; border-radius: 10px; display: inline-block;">
                                        ❌ Ditolak
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 5rem 2rem; text-align: center;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">✨</div>
                                <p style="color: #002f45; opacity: 0.5; font-weight: 700; margin: 0; font-size: 1.1rem;">
                                    Belum ada data pengajuan quest meet dari peserta saat ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination Links --}}
            @if($submissions->hasPages())
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,47,69,0.05);">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection