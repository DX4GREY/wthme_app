@extends('layouts.app')

@section('content')
{{-- Background Wrapper dengan gradien konsisten --}}
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:480px; margin:0 auto;">

        {{-- Navigasi Kembali --}}
        <a href="{{ route('peserta.index') }}" 
           style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7; transition:0.2s;"
           onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>

        {{-- Header Section --}}
        <div style="margin-bottom:2rem;">
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin-bottom:0.5rem;">
                Absensi Kegiatan
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.875rem; line-height:1.5;">
                Silakan scan QR Code yang tersedia atau masukkan kode sesi secara manual di bawah ini.
            </p>
        </div>

        {{-- Notifikasi Error --}}
        @if(session('error') || $error)
        <div style="padding:1rem; background: rgba(239, 68, 68, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.2); border-radius:1rem; color:#b91c1c; margin-bottom:1.5rem; font-size:0.875rem; display:flex; gap:0.75rem; align-items:center;">
            <span>⚠️</span> {{ session('error') ?? $error }}
        </div>
        @endif

        {{-- Sukses / Sudah Absen --}}
        @if(session('success') || $sudahAbsen)
        <div style="padding:2.5rem 1.5rem; background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); border-radius:2rem; text-align:center; border:1px solid rgba(255, 255, 255, 0.5); box-shadow: 0 8px 32px rgba(0,0,0,0.05);">
            <div style="font-size:4rem; margin-bottom:1rem;">✨</div>
            <h3 style="color:#166534; font-weight:800; font-size:1.25rem; margin-bottom:0.5rem;">
                {{ session('success') ?? 'Kehadiran Tercatat!' }}
            </h3>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem;">
                Terima kasih, data kamu sudah masuk ke dalam sistem.
            </p>
        </div>

        {{-- Form Konfirmasi Lokasi (Setelah Scan/Input Kode) --}}
        @elseif($qrSession && !$error)
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:2rem; padding:1.5rem; border:1px solid rgba(255, 255, 255, 0.5); box-shadow: 0 8px 32px rgba(0,0,0,0.05);">
            
            <div id="geo-status" style="padding:0.85rem; background:rgba(255,255,255,0.4); border-radius:1rem; color:#002f45; margin-bottom:1.5rem; font-size:0.85rem; display:flex; align-items:center; gap:0.75rem; border:1px solid rgba(255,255,255,0.3);">
                <span id="geo-icon">📡</span> <span id="geo-text">Menyiapkan GPS...</span>
            </div>

            <div style="margin-bottom:2rem; text-align:center;">
                <div style="font-size:0.7rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem;">Sesi Saat Ini</div>
                <h2 style="color:#002f45; font-weight:800; font-size:1.4rem; margin:0;">{{ $qrSession->nama_sesi }}</h2>
                @if($qrSession->berlaku_hingga)
                    <div style="display:inline-block; margin-top:0.75rem; padding:0.25rem 0.75rem; background:rgba(217, 119, 6, 0.1); color:#d97706; border-radius:2rem; font-size:0.75rem; font-weight:700;">
                        ⏰ Hingga {{ $qrSession->berlaku_hingga->format('H:i') }} WIB
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('peserta.absen.store') }}" id="absen-form">
                @csrf
                <input type="hidden" name="session_code" value="{{ $qrSession->session_code }}">
                <input type="hidden" name="token" value="{{ $token ?? '' }}" id="input-token">
                <input type="hidden" name="latitude" id="input-lat">
                <input type="hidden" name="longitude" id="input-lng">
                <input type="hidden" name="fingerprint" id="input-fp">

                <button type="submit" id="btn-absen"
                    style="width:100%; padding:1.1rem; background:#002f45; color:#d2c296; font-weight:800; border:none; border-radius:1.25rem; cursor:pointer; font-size:1rem; box-shadow: 0 10px 20px rgba(0,47,69,0.2); transition:0.3s; opacity:0.5;"
                    disabled>
                    Menunggu Lokasi...
                </button>
            </form>
        </div>

        {{-- Scanner Utama --}}
        @else
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:2rem; padding:1.5rem; border:1px solid rgba(255, 255, 255, 0.5); box-shadow: 0 8px 32px rgba(0,0,0,0.05);">
            
            {{-- Frame Scanner --}}
            <div style="position:relative; border-radius:1.5rem; overflow:hidden; background:#000; border:4px solid rgba(255,255,255,0.3);">
                <div id="qr-reader" style="width:100%;"></div>
                {{-- Overlay animasi scan (opsional, murni estetika) --}}
                <div style="position:absolute; top:0; left:0; width:100%; height:2px; background:rgba(210, 194, 150, 0.5); box-shadow:0 0 15px #d2c296; animation: scanMove 2s infinite linear;"></div>
            </div>

            <p style="text-align:center; color:#002f45; opacity:0.6; font-size:0.85rem; margin-top:1.25rem; font-weight:500;">
                Arahkan kamera ke QR Code sesi
            </p>

            {{-- Pemisah --}}
            <div style="display:flex; align-items:center; gap:1rem; margin:1.5rem 0;">
                <div style="flex:1; height:1px; background:rgba(0,47,69,0.1);"></div>
                <span style="color:#002f45; opacity:0.4; font-size:0.75rem; font-weight:700; text-transform:uppercase;">Atau Masukkan Kode</span>
                <div style="flex:1; height:1px; background:rgba(0,47,69,0.1);"></div>
            </div>

            {{-- Input Manual --}}
            <form method="GET" action="{{ route('peserta.absen') }}" style="display:flex; gap:0.5rem;">
                <input type="text" name="code" placeholder="Contoh: AB12CD"
                    style="flex:1; padding:0.9rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(255,255,255,0.5); border-radius:1rem; font-size:0.95rem; color:#002f45; outline:none; font-weight:600; text-transform:uppercase;"
                    onfocus="this.style.background='white'; this.style.borderColor='#002f45';" 
                    onblur="this.style.background='rgba(255,255,255,0.5)'; this.style.borderColor='rgba(255,255,255,0.5)';">
                <button type="submit"
                    style="padding:0.9rem 1.5rem; background:#002f45; color:#d2c296; border:none; border-radius:1rem; cursor:pointer; font-weight:800; transition:0.2s;"
                    onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    Cek
                </button>
            </form>
        </div>
        @endif

        <style>
            @keyframes scanMove {
                0% { top: 0; }
                100% { top: 100%; }
            }
            #qr-reader__dashboard { display: none !important; } /* Sembunyikan tombol file html5-qrcode */
            #qr-reader { border: none !important; }
        </style>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Scanner UI yang lebih bersih
    if (document.getElementById('qr-reader')) {
        const scanner = new Html5QrcodeScanner("qr-reader", { 
            fps: 15, 
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        }, false);
        
        scanner.render(function(decodedText) {
            // Jika link mengandung URL aplikasi kita, arahkan langsung
            window.location.href = decodedText;
        });
    }
});

// Geolocation dengan UI Feedback yang lebih manis
const btnAbsen  = document.getElementById('btn-absen');
const geoText   = document.getElementById('geo-text');
const geoIcon   = document.getElementById('geo-icon');
const geoStatus = document.getElementById('geo-status');
const inputLat  = document.getElementById('input-lat');
const inputLng  = document.getElementById('input-lng');

if (btnAbsen && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            inputLat.value = pos.coords.latitude;
            inputLng.value = pos.coords.longitude;

            geoText.textContent = 'Lokasi ditemukan';
            geoIcon.textContent = '✅';
            geoStatus.style.background = 'rgba(34, 197, 94, 0.2)';
            geoStatus.style.borderColor = 'rgba(34, 197, 94, 0.3)';
            enableButton();
        },
        function(err) {
            geoText.textContent = 'GPS tidak aktif / izin ditolak';
            geoIcon.textContent = '❌';
            geoStatus.style.background = 'rgba(239, 68, 68, 0.1)';
            enableButton(); 
        },
        { timeout: 10000, enableHighAccuracy: true }
    );
}

function enableButton() {
    if (!btnAbsen) return;
    btnAbsen.disabled = false;
    btnAbsen.style.opacity = '1';
    btnAbsen.textContent  = 'Konfirmasi Kehadiran';
}

// Fingerprint tetap sama (Logic background)
async function getFingerprint() {
    const data = [navigator.userAgent, navigator.language, screen.width + 'x' + screen.height, new Date().getTimezoneOffset()].join('|');
    const encoder = new TextEncoder();
    const buffer  = await crypto.subtle.digest('SHA-256', encoder.encode(data));
    return Array.from(new Uint8Array(buffer)).map(b => b.toString(16).padStart(2, '0')).join('');
}

const inputFp = document.getElementById('input-fp');
if (inputFp) { getFingerprint().then(fp => { inputFp.value = fp; }); }
</script>
@endsection