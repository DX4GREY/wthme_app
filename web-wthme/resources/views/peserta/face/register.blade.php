@extends('layouts.app')

@section('content')
<div style="min-height:100vh; background:linear-gradient(135deg,#e0decd 0%,#bdd1d3 100%); padding:3rem 1.5rem; display:flex; align-items:center; justify-content:center;">
    <div style="max-width:520px; width:100%; background:rgba(255,255,255,0.4); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:2rem; padding:2.5rem;">

        {{-- Header --}}
        <div style="text-align:center; margin-bottom:2rem;">
            <h2 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.8rem; font-weight:800; margin:0;">
                Daftar <span style="color:#6b705c; font-style:italic;">Wajah</span>
            </h2>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.5rem;">
                Sistem akan mengambil <strong>3 foto</strong> dengan panduan posisi
            </p>
        </div>

        {{-- Alert sukses / error --}}
        @if(session('success'))
            <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#065f46; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; text-align:center;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#991b1b; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; text-align:center;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Status sudah daftar --}}
        @if(auth()->user()->face_registered)
            <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#065f46; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; text-align:center; font-size:0.9rem;">
                ✅ Wajah kamu sudah terdaftar sejak {{ auth()->user()->face_registered_at?->format('d M Y') }}.<br>
                <span style="opacity:0.7;">Daftar ulang di bawah untuk memperbarui data.</span>
            </div>
        @endif

        {{-- ══════════════════════════════════════════ --}}
        {{-- KOTAK CEK WAJAH                            --}}
        {{-- ══════════════════════════════════════════ --}}
        <div style="background:rgba(0,47,69,0.05); border:1.5px dashed rgba(0,47,69,0.2); border-radius:1.25rem; padding:1.25rem; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                <p style="color:#002f45; font-size:0.88rem; font-weight:700; margin:0;">🔍 Cek Wajah Terlebih Dahulu</p>
                <span style="color:#002f45; opacity:0.4; font-size:0.72rem; background:rgba(0,47,69,0.08); padding:0.2rem 0.6rem; border-radius:999px;">Disarankan</span>
            </div>
            <p style="color:#002f45; opacity:0.55; font-size:0.8rem; margin:0 0 1rem; line-height:1.55;">
                Pastikan wajahmu bisa terbaca sistem sebelum mendaftar. Arahkan ke kamera lalu klik tombol di bawah.
            </p>

            {{-- Hasil cek --}}
            <div id="validationResult"
                style="display:none; padding:0.85rem 1rem; border-radius:0.9rem; margin-bottom:0.85rem; font-size:0.86rem; line-height:1.6; text-align:center;">
            </div>

            <button type="button" id="checkFaceBtn"
                style="width:100%; background:rgba(0,47,69,0.1); color:#002f45; padding:0.75rem 1rem;
                       border:1px solid rgba(0,47,69,0.18); border-radius:0.9rem; font-size:0.88rem;
                       font-weight:600; cursor:pointer; transition:0.2s;
                       display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                <span id="checkIcon">🔍</span>
                <span id="checkLabel">Cek Wajah Sekarang</span>
            </button>
        </div>

        {{-- Panduan posisi --}}
        <div id="guideBox" style="background:rgba(0,47,69,0.07); border-radius:1rem; padding:1rem; margin-bottom:1.25rem; text-align:center;">
            <p id="guideText" style="color:#002f45; font-size:0.95rem; font-weight:600; margin:0;">
                📸 Posisikan wajah menghadap <strong>lurus ke depan</strong>
            </p>
            <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin-top:0.3rem;">
                Pastikan wajah terlihat jelas dan pencahayaan cukup
            </p>
        </div>

        {{-- Video --}}
        <div style="position:relative; display:flex; justify-content:center; margin-bottom:1.25rem;">
            <video id="video" autoplay playsinline
                style="width:100%; max-width:360px; border-radius:1.25rem; background:#000; aspect-ratio:4/3; object-fit:cover;">
            </video>

            {{-- Countdown --}}
            <div id="countdownOverlay"
                style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
                       background:rgba(0,0,0,0.55); color:white; font-size:3rem; font-weight:800;
                       width:80px; height:80px; border-radius:50%; align-items:center; justify-content:center;">
                <span id="countdownNum">3</span>
            </div>

            {{-- Flash --}}
            <div id="flashOverlay"
                style="display:none; position:absolute; inset:0; background:white; border-radius:1.25rem; opacity:0.8; pointer-events:none;">
            </div>

            {{-- Scanning ring (biru saat cek wajah) --}}
            <div id="scanRing"
                style="display:none; position:absolute; inset:0; border-radius:1.25rem;
                       border:3px solid #3b82f6; pointer-events:none; animation:pulseRing 1.2s ease-in-out infinite;">
            </div>
        </div>

        {{-- Canvas --}}
        <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>

        {{-- Progress --}}
        <div id="progressArea" style="display:none; margin-bottom:1.25rem;">
            <div style="background:rgba(0,47,69,0.1); border-radius:999px; height:8px; overflow:hidden;">
                <div id="progressBar" style="background:#002f45; height:8px; border-radius:999px; width:0%; transition:width 0.4s;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:0.5rem;">
                <span id="step1dot" style="font-size:0.78rem; color:#002f45; opacity:0.4;">⬤ Foto 1: Lurus</span>
                <span id="step2dot" style="font-size:0.78rem; color:#002f45; opacity:0.4;">⬤ Foto 2: Kiri</span>
                <span id="step3dot" style="font-size:0.78rem; color:#002f45; opacity:0.4;">⬤ Foto 3: Kanan</span>
            </div>
        </div>

        {{-- Preview thumbnail --}}
        <div id="previewArea" style="display:none; margin-bottom:1.25rem;">
            <p style="color:#002f45; font-size:0.85rem; opacity:0.6; text-align:center; margin-bottom:0.5rem;">Preview foto yang diambil:</p>
            <div style="display:flex; gap:0.5rem; justify-content:center;">
                <img id="prev1" style="width:90px; height:68px; border-radius:0.5rem; object-fit:cover; border:2px solid rgba(0,47,69,0.2);" />
                <img id="prev2" style="width:90px; height:68px; border-radius:0.5rem; object-fit:cover; border:2px solid rgba(0,47,69,0.2); opacity:0.3;" />
                <img id="prev3" style="width:90px; height:68px; border-radius:0.5rem; object-fit:cover; border:2px solid rgba(0,47,69,0.2); opacity:0.3;" />
            </div>
        </div>

        {{-- Form daftar --}}
        <form id="uploadForm" method="POST" action="{{ route('peserta.face.register.store') }}" enctype="multipart/form-data">
            @csrf
            <div id="photoInputs"></div>

            <button type="button" id="startBtn"
                style="width:100%; background:#002f45; color:white; padding:0.9rem; border:none; border-radius:1rem; font-size:1rem; font-weight:600; cursor:pointer;">
                📷 Mulai Daftar Wajah
            </button>

            <button type="submit" id="submitBtn"
                style="display:none; width:100%; background:#10b981; color:white; padding:0.9rem; border:none; border-radius:1rem; font-size:1rem; font-weight:600; cursor:pointer; margin-top:0.75rem;">
                ✅ Simpan Pendaftaran Wajah
            </button>

            <button type="button" id="retakeBtn"
                style="display:none; width:100%; background:rgba(0,47,69,0.08); color:#002f45; padding:0.9rem; border:none; border-radius:1rem; font-size:0.9rem; font-weight:600; cursor:pointer; margin-top:0.5rem;">
                🔄 Ulangi Pengambilan Foto
            </button>
        </form>

        <div style="text-align:center; margin-top:1.5rem;">
            <a href="{{ route('peserta.index') }}" style="color:#002f45; opacity:0.5; font-size:0.85rem; text-decoration:none;">
                ← Kembali ke Portal
            </a>
        </div>

    </div>
</div>

<style>
@keyframes pulseRing {
    0%   { box-shadow: 0 0 8px rgba(59,130,246,0.4);  border-color: rgba(59,130,246,0.6); }
    50%  { box-shadow: 0 0 22px rgba(59,130,246,0.9); border-color: rgba(59,130,246,1);   }
    100% { box-shadow: 0 0 8px rgba(59,130,246,0.4);  border-color: rgba(59,130,246,0.6); }
}
</style>

<script>
// ─── Elemen ───
const video            = document.getElementById('video');
const canvas           = document.getElementById('canvas');
const ctx              = canvas.getContext('2d');
const startBtn         = document.getElementById('startBtn');
const submitBtn        = document.getElementById('submitBtn');
const retakeBtn        = document.getElementById('retakeBtn');
const progressBar      = document.getElementById('progressBar');
const progressArea     = document.getElementById('progressArea');
const guideText        = document.getElementById('guideText');
const countdownOverlay = document.getElementById('countdownOverlay');
const countdownNum     = document.getElementById('countdownNum');
const flashOverlay     = document.getElementById('flashOverlay');
const scanRing         = document.getElementById('scanRing');
const previewArea      = document.getElementById('previewArea');
const photoInputs      = document.getElementById('photoInputs');
const checkFaceBtn     = document.getElementById('checkFaceBtn');
const checkIcon        = document.getElementById('checkIcon');
const checkLabel       = document.getElementById('checkLabel');
const validationResult = document.getElementById('validationResult');

// URL FastAPI — diambil langsung dari config Laravel supaya tidak hardcode di JS
const FACE_API_BASE = '{{ config("services.face_api.url", "http://127.0.0.1:8001") }}';

const STEPS = [
    { label: '📸 Posisikan wajah menghadap <strong>lurus ke depan</strong>',      dot: 'step1dot' },
    { label: '↙️ Sekarang miringkan kepala <strong>sedikit ke kiri</strong> (~15°)',  dot: 'step2dot' },
    { label: '↗️ Sekarang miringkan kepala <strong>sedikit ke kanan</strong> (~15°)', dot: 'step3dot' },
];

// ─── Nyalakan kamera ───
navigator.mediaDevices.getUserMedia({
    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
}).then(stream => {
    video.srcObject = stream;
}).catch(() => {
    alert('Kamera tidak bisa diakses. Pastikan izin kamera sudah diberikan.');
});

// ─── Helper: ambil satu frame dari video ───
function captureFrame() {
    return new Promise(resolve => {
        ctx.drawImage(video, 0, 0, 640, 480);
        canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.92);
    });
}

// ─── Helper: countdown 3-2-1 ───
function countdown(seconds) {
    return new Promise(resolve => {
        countdownOverlay.style.display = 'flex';
        let n = seconds;
        countdownNum.textContent = n;
        const iv = setInterval(() => {
            n--;
            if (n <= 0) { clearInterval(iv); countdownOverlay.style.display = 'none'; resolve(); }
            else countdownNum.textContent = n;
        }, 1000);
    });
}

// ─── Helper: flash putih sesaat ───
function flash() {
    return new Promise(resolve => {
        flashOverlay.style.display = 'block';
        setTimeout(() => { flashOverlay.style.display = 'none'; resolve(); }, 150);
    });
}

// ════════════════════════════════════════════════════════
//  CEK WAJAH — kirim ke FastAPI /check-face
//  Endpoint ini hanya detect, tidak compare ke database
// ════════════════════════════════════════════════════════
checkFaceBtn.addEventListener('click', async () => {
    checkFaceBtn.disabled   = true;
    checkIcon.textContent   = '⏳';
    checkLabel.textContent  = 'Memindai wajah...';
    scanRing.style.display  = 'block';
    validationResult.style.display = 'none';

    try {
        const blob     = await captureFrame();
        const formData = new FormData();
        formData.append('photo', blob, 'check.jpg');

        const res  = await fetch(`${FACE_API_BASE}/check-face`, {
            method : 'POST',
            body   : formData,
        });

        if (!res.ok) throw new Error(`Server error: HTTP ${res.status}`);
        const data = await res.json();

        scanRing.style.display         = 'none';
        validationResult.style.display = 'block';

        if (data.detected) {
            // ── Tentukan label kualitas ──
            const q = data.quality_score ?? null;
            let qlabel = '', qcolor = '#065f46';
            if (q !== null) {
                if      (q >= 0.75) { qlabel = '🟢 Kualitas: <strong>Baik</strong>';   qcolor = '#065f46'; }
                else if (q >= 0.50) { qlabel = '🟡 Kualitas: <strong>Cukup</strong> — coba tambah pencahayaan'; qcolor = '#92400e'; }
                else                { qlabel = '🔴 Kualitas: <strong>Kurang</strong> — perbaiki cahaya / jarak'; qcolor = '#991b1b'; }
            }

            validationResult.style.cssText = `
                display:block; padding:0.85rem 1rem; border-radius:0.9rem; margin-bottom:0.85rem;
                font-size:0.86rem; line-height:1.7; text-align:center;
                background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.35);
                color: ${qcolor};
            `;
            validationResult.innerHTML = `
                <strong style="font-size:1rem;">✅ Wajah Terdeteksi!</strong><br>
                ${data.face_count > 1
                    ? `<span style="color:#92400e;">⚠️ ${data.face_count} wajah terdeteksi — pastikan hanya wajahmu yang terlihat.</span><br>`
                    : ''}
                ${qlabel ? `<span>${qlabel}</span><br>` : ''}
                <span style="opacity:0.75; font-size:0.8rem;">Kamu siap mendaftarkan wajah 🎉</span>
            `;

        } else {
            // ── Wajah tidak terdeteksi ──
            validationResult.style.cssText = `
                display:block; padding:0.85rem 1rem; border-radius:0.9rem; margin-bottom:0.85rem;
                font-size:0.86rem; line-height:1.7; text-align:center;
                background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#991b1b;
            `;
            validationResult.innerHTML = `
                <strong style="font-size:1rem;">❌ Wajah Tidak Terdeteksi</strong><br>
                <span style="opacity:0.85;">${data.reason ?? 'Sistem tidak bisa membaca wajahmu.'}</span><br>
                <span style="opacity:0.65; font-size:0.8rem;">
                    Tips: perbaiki pencahayaan, hadapkan wajah ke kamera, pastikan tidak tertutup masker/kacamata.
                </span>
            `;
        }

    } catch (err) {
        scanRing.style.display = 'none';
        validationResult.style.cssText = `
            display:block; padding:0.85rem 1rem; border-radius:0.9rem; margin-bottom:0.85rem;
            font-size:0.86rem; line-height:1.7; text-align:center;
            background:rgba(234,179,8,0.1); border:1px solid rgba(234,179,8,0.3); color:#92400e;
        `;
        validationResult.innerHTML = `
            <strong>⚠️ Tidak bisa terhubung ke server</strong><br>
            <span style="opacity:0.75; font-size:0.8rem;">Pastikan FastAPI berjalan di port 8001. (${err.message})</span>
        `;
    }

    checkFaceBtn.disabled  = false;
    checkIcon.textContent  = '🔍';
    checkLabel.textContent = 'Cek Ulang';
});

// ════════════════════════════════════════════════════════
//  PENDAFTARAN — 3 foto dengan panduan posisi
// ════════════════════════════════════════════════════════
startBtn.addEventListener('click', async () => {
    startBtn.disabled          = true;
    progressArea.style.display = 'block';
    previewArea.style.display  = 'block';

    // Reset state
    ['prev1','prev2','prev3'].forEach(id => {
        const el = document.getElementById(id);
        el.src = ''; el.style.opacity = '0.3';
    });
    STEPS.forEach(s => {
        const dot = document.getElementById(s.dot);
        dot.style.opacity = '0.4'; dot.style.fontWeight = 'normal';
    });

    const capturedBlobs = [];

    for (let i = 0; i < STEPS.length; i++) {
        guideText.innerHTML = STEPS[i].label;
        const dot           = document.getElementById(STEPS[i].dot);
        dot.style.opacity   = '1'; dot.style.fontWeight = '700';
        progressBar.style.width = ((i / STEPS.length) * 100) + '%';
        startBtn.textContent    = `Bersiap foto ${i + 1}/3...`;

        await countdown(3);

        const blob = await captureFrame();
        await flash();
        capturedBlobs.push(blob);

        const prev = document.getElementById(`prev${i + 1}`);
        prev.src           = URL.createObjectURL(blob);
        prev.style.opacity = '1';

        if (i < STEPS.length - 1) await new Promise(r => setTimeout(r, 600));
    }

    progressBar.style.width = '100%';
    guideText.innerHTML     = '✅ <strong>3 foto berhasil diambil!</strong> Klik Simpan untuk mendaftarkan wajah.';
    startBtn.textContent    = '📷 Mulai Daftar Wajah';

    // Masukkan blob ke form
    const dt = new DataTransfer();
    capturedBlobs.forEach((blob, i) =>
        dt.items.add(new File([blob], `face_${i}.jpg`, { type: 'image/jpeg' }))
    );
    const input         = document.createElement('input');
    input.type          = 'file';
    input.name          = 'photos[]';
    input.multiple      = true;
    input.style.display = 'none';
    input.files         = dt.files;
    photoInputs.innerHTML = '';
    photoInputs.appendChild(input);

    startBtn.style.display  = 'none';
    submitBtn.style.display = 'block';
    retakeBtn.style.display = 'block';
});

// ─── Ulangi ───
retakeBtn.addEventListener('click', () => {
    photoInputs.innerHTML      = '';
    progressBar.style.width    = '0%';
    progressArea.style.display = 'none';
    previewArea.style.display  = 'none';
    guideText.innerHTML = '📸 Posisikan wajah menghadap <strong>lurus ke depan</strong>';
    STEPS.forEach(s => {
        const dot = document.getElementById(s.dot);
        dot.style.opacity = '0.4'; dot.style.fontWeight = 'normal';
    });
    startBtn.disabled       = false;
    startBtn.style.display  = 'block';
    submitBtn.style.display = 'none';
    retakeBtn.style.display = 'none';
});
</script>
@endsection