@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:700px; margin:0 auto;">

        {{-- Header --}}
        <div style="text-align:center; margin-bottom:2.5rem;">
            <span style="display:inline-block; padding:0.5rem 1.25rem; background:rgba(0,47,69,0.05); border-radius:2rem; color:#002f45; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:1rem;">
                Status Suara
            </span>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                Status <span style="color:#6b705c; font-style:italic;">Suara Kamu</span>
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:1rem; margin-top:0.75rem;">
                Pantau apakah suara kamu sudah dibaca oleh panitia
            </p>
        </div>

        {{-- Success Alert --}}
        @if (session('success'))
        <div style="background:#d4edda; border:1px solid #c3e6cb; border-radius:1rem; padding:1rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:1.5rem;">✅</span>
            <span style="color:#155724; font-weight:600;">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Error Alert --}}
        @if (session('error'))
        <div style="background:#f8d7da; border:1px solid #f5c6cb; border-radius:1rem; padding:1rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:1.5rem;">❌</span>
            <span style="color:#721c24; font-weight:600;">{{ session('error') }}</span>
        </div>
        @endif

        {{-- Status Card --}}
        <div style="background:rgba(255,255,255,0.4); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:2rem; padding:2.5rem; box-shadow:0 8px 32px rgba(0,47,69,0.05);">

            {{-- Status Badge --}}
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap;">
                @if ($suara->dibaca)
                <span style="background:#d4edda; color:#155724; font-size:0.75rem; font-weight:800; padding:0.4rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.4rem;">
                    ✅ Sudah Dibaca
                </span>
                @else
                <span style="background:#fff3cd; color:#856404; font-size:0.75rem; font-weight:800; padding:0.4rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.4rem;">
                    ⏳ Belum Dibaca
                </span>
                @endif

                @if ($suara->anonim)
                <span style="background:rgba(107,112,92,0.1); color:#6b705c; font-size:0.65rem; font-weight:800; padding:0.3rem 0.75rem; border-radius:0.5rem;">
                    🕵️ Dikirim Anonim
                </span>
                @endif
            </div>

            {{-- Waktu Kirim --}}
            <div style="color:#002f45; opacity:0.4; font-size:0.8rem; margin-bottom:1.5rem;">
                Dikirim {{ $suara->created_at->format('d F Y H:i') }} ({{ $suara->created_at->diffForHumans() }})
            </div>

            {{-- Pesan --}}
            <div style="margin-bottom:1.5rem;">
                <h3 style="color:#002f45; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; opacity:0.5; margin:0 0 0.75rem 0;">
                    Pesan Kamu
                </h3>
                <div style="background:rgba(255,255,255,0.5); border-radius:1rem; padding:1.5rem; border:1px solid rgba(0,47,69,0.06);">
                    <p style="color:#002f45; font-size:1rem; line-height:1.8; margin:0; white-space:pre-wrap;">
                        {{ $suara->pesan }}
                    </p>
                </div>
            </div>

            {{-- Foto --}}
            @if ($suara->foto)
            <div style="margin-bottom:1.5rem;">
                <h3 style="color:#002f45; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; opacity:0.5; margin:0 0 0.75rem 0;">
                    Foto Terlampir
                </h3>
                <div style="background:rgba(255,255,255,0.5); border-radius:1rem; padding:1rem; border:1px solid rgba(0,47,69,0.06); text-align:center;">
                    <img src="{{ asset('storage/' . $suara->foto) }}" alt="Foto suara"
                        style="max-width:100%; max-height:300px; border-radius:0.75rem;">
                </div>
            </div>
            @endif

            {{-- Daftar Pembaca --}}
            @if ($suara->dibaca && $suara->reads->count() > 0)
            <div style="margin-bottom:1.5rem; border-top:1px solid rgba(0,47,69,0.08); padding-top:1.5rem;">
                <h3 style="color:#002f45; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; opacity:0.5; margin:0 0 0.75rem 0;">
                    Dibaca Oleh ({{ $suara->reads->count() }})
                </h3>
                <div style="display:flex; flex-direction:column; gap:0.5rem;">
                    @foreach ($suara->reads as $read)
                    <div style="display:flex; align-items:center; gap:0.75rem; background:rgba(255,255,255,0.4); padding:0.6rem 1rem; border-radius:0.75rem;">
                        <div style="width:32px; height:32px; border-radius:50%; background:#002f45; display:flex; align-items:center; justify-content:center; font-weight:700; color:white; font-size:0.75rem; flex-shrink:0;">
                            {{ strtoupper(substr($read->user->name, 0, 1)) }}
                        </div>
                        <div style="flex:1;">
                            <div style="color:#002f45; font-weight:600; font-size:0.85rem;">{{ $read->user->name }}</div>
                            <div style="color:#002f45; opacity:0.4; font-size:0.7rem;">
                                {{ $read->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tombol Hapus --}}
            <div style="border-top:1px solid rgba(0,47,69,0.08); padding-top:1.5rem;">
                <form action="{{ route('peserta.suara.destroy') }}" method="POST"
                    onsubmit="return confirm('Yakin ingin menghapus suara kamu? Kamu bisa mengirim suara baru setelahnya.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        style="width:100%; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); padding:0.8rem; border-radius:0.75rem; font-size:0.85rem; font-weight:600; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:0.5rem;"
                        onmouseover="this.style.background='rgba(239,68,68,0.2)'"
                        onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                        🗑️ Hapus Suara & Kirim Baru
                    </button>
                </form>
                <p style="color:#002f45; opacity:0.4; font-size:0.75rem; text-align:center; margin:0.75rem 0 0 0;">
                    Setelah dihapus, kamu bisa mengirimkan suara baru
                </p>
            </div>
        </div>

        {{-- Back --}}
        <div style="margin-top:1.5rem; text-align:center;">
            <a href="{{ route('peserta.index') }}"
                style="display:inline-flex; align-items:center; gap:0.5rem; color:#002f45; text-decoration:none; font-weight:600; font-size:0.9rem; opacity:0.6; transition:0.3s;"
                onmouseover="this.style.opacity='1'"
                onmouseout="this.style.opacity='0.6'">
                <span>←</span> Kembali ke Dashboard
            </a>
        </div>

    </div>
</div>
@endsection