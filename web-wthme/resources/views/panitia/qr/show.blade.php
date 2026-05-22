@extends('layouts.app')

@if($qrSession->rotating && $qrSession->aktif)
<script>
const sessionCode  = "{{ $qrSession->session_code }}";
const interval     = {{ $qrSession->rotate_interval }} * 1000;
const refreshUrl   = "{{ route('panitia.qr.refresh', $qrSession->session_code) }}";

function hitungSisa(expiresAt) {
    const sisa = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
    return sisa;
}

async function refreshQr() {
    try {
        const res  = await fetch(refreshUrl);
        const data = await res.json();
        document.getElementById('qr-container').innerHTML = data.qr_svg;
        startCountdown(data.expires_at);
    } catch(e) {
        console.error('Gagal refresh QR:', e);
    }
}

function startCountdown(expiresAt) {
    clearInterval(window._countdownTimer);
    window._countdownTimer = setInterval(() => {
        const sisa = hitungSisa(expiresAt);
        const el   = document.getElementById('countdown');
        if (el) el.textContent = sisa + 's';

        const bar = document.getElementById('progress-bar');
        if (bar) bar.style.width = ((sisa / {{ $qrSession->rotate_interval }}) * 100) + '%';

        if (sisa <= 0) {
            clearInterval(window._countdownTimer);
            refreshQr();
        }
    }, 1000);
}

const expiresAt = {{ $qrSession->token_expires_at ? $qrSession->token_expires_at->timestamp : 'Math.floor(Date.now()/1000) + ' . $qrSession->rotate_interval }};
startCountdown(expiresAt);
</script>

<style>
#qr-container svg { width: 260px; height: 260px; transition: all 0.5s ease; }
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}
.qr-active { animation: pulse 2s infinite ease-in-out; }
</style>
@endif

@section('content')
{{-- Background Wrapper --}}
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:560px; margin:0 auto; text-align:center;">

    <a href="{{ route('panitia.index') }}" 
       style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.875rem; font-weight:600; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:2rem; transition:0.3s;"
       onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
        <span style="font-size:1.1rem;">←</span> Kembali ke Portal Panitia
    </a>

    @if(session('success'))
    <div style="padding:1rem; background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 1rem; color:#166534; margin-bottom:1.5rem; font-size:0.875rem; font-weight:600; text-align:left;">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- Glass QR Card --}}
    <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 2rem; padding: 2.5rem 2rem; box-shadow: 0 20px 40px rgba(0, 47, 69, 0.1); margin-bottom: 2rem;">

        {{-- Status Badge --}}
        <div style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.4rem 1.25rem; border-radius:999px; margin-bottom:1.5rem; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em;
             {{ $qrSession->aktif ? 'background: rgba(34, 197, 94, 0.2); color:#166534; border: 1px solid rgba(34, 197, 94, 0.2);' : 'background: rgba(239, 68, 68, 0.2); color:#991b1b; border: 1px solid rgba(239, 68, 68, 0.2);' }}">
            <span style="width:8px; height:8px; border-radius:50%; background:currentColor; display:inline-block;"></span>
            {{ $qrSession->aktif ? 'Sesi Aktif' : 'Sesi Ditutup' }}
        </div>

        <h2 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:800; margin-bottom:0.5rem;">
            {{ $qrSession->nama_sesi }}
        </h2>
        <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-bottom:1.5rem; font-weight:500;">
            Target: <span style="background:rgba(0,47,69,0.1); padding:0.2rem 0.5rem; border-radius:6px; color:#002f45;">{{ ucfirst($qrSession->untuk) }}</span>
            &nbsp;·&nbsp;
            ID: <strong style="font-family:monospace;">{{ $qrSession->session_code }}</strong>
        </p>

        @if($qrSession->berlaku_hingga)
        <p style="background: rgba(217, 119, 6, 0.1); display:inline-block; padding:0.5rem 1rem; border-radius:0.75rem; color:#92400e; font-size:0.8rem; font-weight:700; margin-bottom:2rem; border:1px solid rgba(217, 119, 6, 0.1);">
            ⏰ Exp: {{ $qrSession->berlaku_hingga->format('H:i') }} ({{ $qrSession->berlaku_hingga->format('d M') }})
        </p>
        @endif

        {{-- QR Container with Contrast --}}
        <div style="margin-bottom: 1.5rem;">
            <div id="qr-container" class="{{ $qrSession->aktif ? 'qr-active' : '' }}" 
                 style="display:inline-block; padding:1.5rem; background:white; border-radius:1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.8);">
                {!! $qrCode !!}
            </div>
        </div>

        @if($qrSession->rotating && $qrSession->aktif)
        <div style="max-width:300px; margin:0 auto 2rem;">
            <div style="background: rgba(0, 47, 69, 0.1); border-radius:999px; height:8px; overflow:hidden; margin-bottom:0.75rem; border:1px solid rgba(0,0,0,0.03);">
                <div id="progress-bar" style="height:100%; background: linear-gradient(90deg, #002f45, #1d5b79); width:100%; transition:width 1s linear; border-radius:999px;"></div>
            </div>
            <p style="color:#002f45; opacity:0.7; font-size:0.85rem; font-weight:600;">
                Refresh dalam <span id="countdown" style="color:#002f45; font-weight:800; font-size:1rem;">{{ $qrSession->rotate_interval }}s</span>
            </p>
        </div>
        @endif

        {{-- Copyable Link Area --}}
        <div style="background: rgba(255, 255, 255, 0.4); border: 1px solid rgba(255, 255, 255, 0.5); border-radius:1rem; padding:1rem; margin-bottom:2rem; text-align:left; position:relative;">
            <div style="font-size:0.65rem; color:#002f45; opacity:0.5; margin-bottom:0.4rem; text-transform:uppercase; font-weight:800; letter-spacing:0.1em;">URL Absensi Manual</div>
            <div style="font-size:0.8rem; color:#002f45; word-break:break-all; font-family:monospace; line-height:1.4; padding-right:2rem;">{{ $absenUrl }}</div>
        </div>

        {{-- Toggle Button --}}
        <form method="POST" action="{{ route('panitia.qr.toggle', $qrSession->id) }}">
            @csrf
            @method('PATCH')
            <button type="submit"
                style="width:100%; padding:1rem; border:none; border-radius:1.25rem; font-weight:800; font-size:0.9rem; cursor:pointer; transition:all 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.05);
                       {{ $qrSession->aktif ? 'background:#991b1b; color:#fff;' : 'background:#16a34a; color:#fff;' }}"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,0,0,0.1)';" 
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';">
                {{ $qrSession->aktif ? '⏹ HENTIKAN SESI ABSENSI' : '▶ AKTIFKAN SESI KEMBALI' }}
            </button>
        </form>
    </div>

    {{-- Instructions Glass Card --}}
    <div style="background: rgba(0, 47, 69, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.5rem; text-align: left; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <h3 style="color:#d2c296; font-weight:800; font-size:0.9rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
            <span style="font-size:1.2rem;">📝</span> Petunjuk Penggunaan
        </h3>
        <div style="color:#bdd1d3; font-size:0.85rem; line-height:1.7; font-weight:500;">
            <div style="display:flex; gap:0.75rem; margin-bottom:0.5rem;">
                <span style="color:#d2c296;">01.</span>
                <span>Tampilkan QR ini melalui proyektor agar terlihat oleh seluruh {{ $qrSession->untuk }}.</span>
            </div>
            <div style="display:flex; gap:0.75rem; margin-bottom:0.5rem;">
                <span style="color:#d2c296;">02.</span>
                <span>Peserta wajib memindai menggunakan Portal {{ ucfirst($qrSession->untuk) }} atau kamera HP.</span>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <span style="color:#d2c296;">03.</span>
                <span>QR akan berganti secara otomatis setiap {{ $qrSession->rotate_interval }} detik untuk mencegah kecurangan.</span>
            </div>
        </div>
    </div>

</div>
</div>
@endsection