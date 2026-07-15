@extends('layouts.app')

@section('content')
<div style="min-height:100vh; padding:4rem 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); font-family:'Inter',sans-serif;">
    <div style="max-width:700px; margin:0 auto;">

        {{-- Back Link --}}
        <a href="{{ route('panitia.suara.index') }}" 
            style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; color:#002f45; font-weight:600; font-size:0.9rem; margin-bottom:1.5rem; opacity:0.6; transition:0.3s;"
            onmouseover="this.style.opacity='1'"
            onmouseout="this.style.opacity='0.6'">
            <span style="font-size:1.2rem;">←</span> Kembali ke Daftar Suara
        </a>

        {{-- Card Detail --}}
        <div style="background:rgba(255,255,255,0.5); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:2rem; padding:2.5rem; box-shadow:0 8px 32px rgba(0,47,69,0.05);">

            {{-- Status Badges --}}
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap;">
                @if ($suara->dibaca)
                <span style="background:rgba(0,47,69,0.05); color:#002f45; opacity:0.5; font-size:0.65rem; font-weight:800; padding:0.3rem 0.75rem; border-radius:0.5rem; letter-spacing:0.05em;">
                    ✅ DIBACA {{ $suara->dibaca_at ? $suara->dibaca_at->diffForHumans() : '' }}
                </span>
                @else
                <span style="background:#002f45; color:white; font-size:0.65rem; font-weight:800; padding:0.3rem 0.75rem; border-radius:0.5rem; letter-spacing:0.05em;">
                    🔴 BARU
                </span>
                @endif

                @if ($suara->anonim)
                <span style="background:rgba(107,112,92,0.1); color:#6b705c; font-size:0.65rem; font-weight:800; padding:0.3rem 0.75rem; border-radius:0.5rem;">
                    🕵️ DIKIRIM ANONIM
                </span>
                @endif
            </div>

            {{-- Informasi Pengirim --}}
            <div style="background:rgba(0,47,69,0.03); border-radius:1rem; padding:1.25rem; margin-bottom:2rem; border:1px solid rgba(0,47,69,0.06);">
                <div style="font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#002f45; opacity:0.4; margin-bottom:0.75rem;">
                    Informasi Pengirim
                </div>
                
                @if (auth()->user()->isAdmin())
                    {{-- Admin: lihat identitas lengkap --}}
                    <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
                        <div>
                            <span style="font-size:0.7rem; color:#002f45; opacity:0.5;">Nama</span>
                            <div style="color:#002f45; font-weight:600; font-size:0.95rem;">{{ $suara->user->name }}</div>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#002f45; opacity:0.5;">NIM</span>
                            <div style="color:#002f45; font-weight:600; font-size:0.95rem;">{{ $suara->user->nim }}</div>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#002f45; opacity:0.5;">Kelompok</span>
                            <div style="color:#002f45; font-weight:600; font-size:0.95rem;">{{ $suara->user->kelompok }}</div>
                        </div>
                    </div>
                    @if ($suara->anonim)
                    <div style="margin-top:0.75rem; padding-top:0.75rem; border-top:1px dashed rgba(0,47,69,0.1); color:#6b705c; font-size:0.8rem;">
                        🔒 <strong>Catatan:</strong> Peserta mengirim ini secara anonim. Identitas hanya terlihat oleh admin.
                    </div>
                    @endif
                @else
                    {{-- Panitia: tidak lihat identitas --}}
                    <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
                        <div>
                            <span style="font-size:0.7rem; color:#002f45; opacity:0.5;">Status</span>
                            <div style="color:#002f45; font-weight:600; font-size:0.95rem;">
                                {{ $suara->anonim ? '🕵️ Dikirim Secara Anonim' : '👤 Peserta' }}
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:0.75rem; padding-top:0.75rem; border-top:1px dashed rgba(0,47,69,0.1); color:#6b705c; font-size:0.8rem;">
                        🔒 Identitas pengirim hanya bisa dilihat oleh admin.
                    </div>
                @endif
            </div>

            {{-- Waktu --}}
            <div style="color:#002f45; opacity:0.4; font-size:0.8rem; margin-bottom:1.5rem;">
                Dikirim {{ $suara->created_at->format('d F Y H:i') }} ({{ $suara->created_at->diffForHumans() }})
            </div>

            {{-- Pesan --}}
            <div style="margin-bottom:2rem;">
                <h3 style="color:#002f45; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; opacity:0.5; margin:0 0 0.75rem 0;">
                    Isi Pesan
                </h3>
                <div style="background:rgba(255,255,255,0.5); border-radius:1rem; padding:1.5rem; border:1px solid rgba(0,47,69,0.06);">
                    <p style="color:#002f45; font-size:1rem; line-height:1.8; margin:0; white-space:pre-wrap;">
                        {{ $suara->pesan }}
                    </p>
                </div>
            </div>

            {{-- Foto --}}
            @if ($suara->foto)
            <div style="margin-bottom:2rem;">
                <h3 style="color:#002f45; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; opacity:0.5; margin:0 0 0.75rem 0;">
                    Foto Terlampir
                </h3>
                <div style="background:rgba(255,255,255,0.5); border-radius:1rem; padding:1rem; border:1px solid rgba(0,47,69,0.06); text-align:center;">
                    <img src="{{ asset('storage/' . $suara->foto) }}" alt="Foto suara peserta"
                        style="max-width:100%; max-height:400px; border-radius:0.75rem; cursor:pointer; transition:transform 0.3s;"
                        onclick="window.open(this.src, '_blank')"
                        onmouseover="this.style.transform='scale(1.02)'"
                        onmouseout="this.style.transform='scale(1)'">
                    <p style="color:#002f45; opacity:0.4; font-size:0.75rem; margin:0.5rem 0 0 0;">
                        Klik foto untuk melihat ukuran penuh
                    </p>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div style="display:flex; gap:1rem; border-top:1px solid rgba(0,47,69,0.08); padding-top:1.5rem;">
                <a href="{{ route('panitia.suara.index') }}"
                    style="flex:1; background:rgba(255,255,255,0.6); color:#002f45; text-decoration:none; border:1px solid rgba(0,47,69,0.15); padding:0.8rem; border-radius:0.75rem; font-size:0.85rem; font-weight:600; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:0.5rem;"
                    onmouseover="this.style.background='rgba(255,255,255,0.9)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.6)'">
                    <span>←</span> Kembali
                </a>

                <form action="{{ route('panitia.suara.destroy', $suara->id) }}" method="POST" style="flex:0.5;">
                    @csrf @method('DELETE')
                    <button type="submit" 
                        onclick="return confirm('Yakin ingin menghapus suara ini?')"
                        style="width:100%; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); padding:0.8rem; border-radius:0.75rem; font-size:0.85rem; font-weight:600; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:0.5rem;"
                        onmouseover="this.style.background='rgba(239,68,68,0.2)'"
                        onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection