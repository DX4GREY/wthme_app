@extends('layouts.app')

@section('content')
    <style>
        #sessionSelect, #sessionSelect option {
            color: #002f45 !important;
            background-color: #ffffff !important;
        }

        #sessionSelect {
            color-scheme: light !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div style="min-height:100vh; background:linear-gradient(135deg,#e0decd 0%,#bdd1d3 100%); padding:2rem 1.5rem; display:flex; flex-direction:column; align-items:center; gap:1.5rem;">

        {{-- Header --}}
        <div style="text-align:center;">
            <h2 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                Face <span style="color:#6b705c; font-style:italic;">Gate</span>
            </h2>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem;">
                Absensi otomatis via pengenalan wajah (Multi-Wajah, Mode Tanpa Jeda)
            </p>
        </div>

        <div style="display:flex; gap:1.5rem; width:100%; max-width:900px; flex-wrap:wrap; align-items:flex-start;">

            {{-- ─── Kolom Kiri: Kamera & Kontrol ─── --}}
            <div style="flex:1; min-width:280px; background:rgba(255,255,255,0.45); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:2rem; padding:1.75rem;">

                {{-- Pilih sesi --}}
                <div style="margin-bottom:1rem;">
                    <label style="color:#002f45; font-size:0.85rem; font-weight:600; display:block; margin-bottom:0.4rem;">
                        Sesi Absensi
                    </label>
                    <select id="sessionSelect" data-theme="light"
                        style="width:100%; padding:0.7rem 1rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.2); background:white; color:#002f45; font-size:0.9rem;">
                        <option value="" style="color:#002f45; background:white;">-- Pilih Sesi --</option>
                        @foreach ($sesiList as $session)
                            <option value="{{ $session->id }}" style="color:#002f45; background:white;">
                                {{ $session->nama_sesi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Video --}}
                <div style="position:relative; margin-bottom:1rem;">
                    <video id="video" autoplay playsinline
                        style="width:100%; border-radius:1.25rem; background:#000; aspect-ratio:4/3; object-fit:cover;">
                    </video>
                    <div id="scanOverlay"
                        style="display:none; position:absolute; inset:0; border-radius:1.25rem; border:3px solid rgba(16,185,129,0.7); box-shadow:0 0 20px rgba(16,185,129,0.3);">
                    </div>
                    <div id="errorOverlay"
                        style="display:none; position:absolute; inset:0; border-radius:1.25rem; border:3px solid rgba(239,68,68,0.7); box-shadow:0 0 20px rgba(239,68,68,0.3);">
                    </div>
                </div>

                {{-- Canvas hidden --}}
                <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>

                {{-- Status indicator --}}
                <div id="statusBox"
                    style="padding:0.75rem 1rem; border-radius:0.75rem; background:rgba(0,47,69,0.07); text-align:center; margin-bottom:1rem; font-size:0.88rem; color:#002f45; min-height:48px; display:flex; align-items:center; justify-content:center;">
                    <span id="statusText">Pilih sesi lalu tekan Mulai Scan.</span>
                </div>

                {{-- Tombol --}}
                <button id="startBtn" type="button"
                    style="width:100%; background:#002f45; color:white; padding:0.85rem; border:none; border-radius:1rem; font-size:1rem; font-weight:600; cursor:pointer;">
                    ▶ Mulai Scan Otomatis
                </button>
                <button id="stopBtn" type="button"
                    style="display:none; width:100%; background:rgba(239,68,68,0.15); color:#991b1b; padding:0.85rem; border:1px solid rgba(239,68,68,0.3); border-radius:1rem; font-size:1rem; font-weight:600; cursor:pointer; margin-top:0.5rem;">
                    ⏹ Stop Scan
                </button>
            </div>

            {{-- ─── Kolom Kanan: Log Absensi ─── --}}
            <div style="flex:1; min-width:280px; background:rgba(255,255,255,0.45); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:2rem; padding:1.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="color:#002f45; font-size:1rem; font-weight:700; margin:0;">📋 Log Absensi</h3>
                    <span id="counterBadge"
                        style="background:#002f45; color:white; font-size:0.78rem; font-weight:700; padding:0.25rem 0.75rem; border-radius:999px;">
                        0 hadir
                    </span>
                </div>

                <div id="logList"
                    style="display:flex; flex-direction:column; gap:0.6rem; max-height:420px; overflow-y:auto;">
                    <p style="color:#002f45; opacity:0.4; font-size:0.85rem; text-align:center; margin-top:2rem;">
                        Belum ada peserta yang absen.
                    </p>
                </div>
            </div>
        </div>

        {{-- Back --}}
        <a href="{{ route('panitia.index') }}"
            style="color:#002f45; opacity:0.5; font-size:0.85rem; text-decoration:none; margin-top:0.5rem;">
            ← Kembali ke Dashboard Panitia
        </a>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const statusText = document.getElementById('statusText');
        const scanOverlay = document.getElementById('scanOverlay');
        const errorOverlay = document.getElementById('errorOverlay');
        const logList = document.getElementById('logList');
        const counterBadge = document.getElementById('counterBadge');
        const sessionSelect = document.getElementById('sessionSelect');

        const SCAN_INTERVAL = 200;

        let scanTimer = null;
        let attendedCount = 0;
        let isProcessing = false;
        const loggedIds = new Set();

        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        }).then(stream => {
            video.srcObject = stream;
        }).catch(() => {
            alert('Kamera tidak bisa diakses.');
        });

        function setStatus(msg, type = 'neutral') {
            statusText.textContent = msg;
            const box = document.getElementById('statusBox');

            box.style.background = type === 'success' ? 'rgba(16,185,129,0.15)' :
                type === 'error' ? 'rgba(239,68,68,0.15)' :
                type === 'warning' ? 'rgba(234,179,8,0.15)' :
                'rgba(0,47,69,0.07)';

            if (type === 'success') {
                scanOverlay.style.display = 'block';
                errorOverlay.style.display = 'none';
            } else if (type === 'error') {
                errorOverlay.style.display = 'block';
                scanOverlay.style.display = 'none';
            } else {
                scanOverlay.style.display = 'none';
                errorOverlay.style.display = 'none';
            }
        }

        function addLog(data) {
            const placeholder = logList.querySelector('p');
            if (placeholder) placeholder.remove();

            const isNew = !data.already;
            const color = data.already ? '#6b705c' : '#065f46';
            const bgColor = data.already ? 'rgba(107,112,92,0.08)' : 'rgba(16,185,129,0.1)';
            const border = data.already ? 'rgba(107,112,92,0.2)' : 'rgba(16,185,129,0.3)';
            const icon = data.already ? '🔁' : '✅';

            const now = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const item = document.createElement('div');
            item.style.cssText = `
                background:${bgColor}; border:1px solid ${border}; border-radius:0.75rem;
                padding:0.65rem 0.9rem; animation:fadeIn 0.15s ease;
            `;
            item.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:${color}; font-weight:700; font-size:0.9rem;">${icon} ${data.user_name}</span>
                    <span style="color:#002f45; opacity:0.4; font-size:0.75rem;">${now}</span>
                </div>
                <div style="color:#002f45; opacity:0.6; font-size:0.78rem; margin-top:0.2rem;">
                    Kelompok ${data.kelompok ?? '-'} • ${data.nim ?? '-'}
                    ${data.confidence ? `• ${data.confidence}% cocok` : ''}
                </div>
            `;

            logList.prepend(item);

            if (isNew) {
                attendedCount++;
                counterBadge.textContent = `${attendedCount} hadir`;
            }
        }

        async function captureAndIdentify() {
            if (isProcessing) return;

            const sessionId = sessionSelect.value;
            if (!sessionId) {
                setStatus('⚠️ Pilih sesi absensi terlebih dahulu.', 'warning');
                return;
            }

            isProcessing = true;

            try {
                ctx.drawImage(video, 0, 0, 640, 480);

                const blob = await new Promise(resolve =>
                    canvas.toBlob(resolve, 'image/jpeg', 0.65)
                );

                const formData = new FormData();
                formData.append('photo', blob, 'capture.jpg');
                formData.append('session_id', sessionId);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');

                const res = await fetch('{{ route('panitia.absen.face.process') }}', {
                    method: 'POST',
                    body: formData,
                });
                const data = await res.json();

                if (data.success && Array.isArray(data.results) && data.results.length > 0) {
                    let anyNew = false;

                    data.results.forEach(r => {
                        if (r.already) {
                            if (!loggedIds.has(r.user_name)) {
                                loggedIds.add(r.user_name);
                                addLog(r);
                            }
                        } else {
                            loggedIds.add(r.user_name);
                            addLog(r);
                            anyNew = true;
                        }
                    });

                    const names = data.results.map(r => r.user_name).join(', ');
                    setStatus(
                        anyNew ? `✅ ${names}` : `🔁 Sudah absen: ${names}`,
                        anyNew ? 'success' : 'warning'
                    );
                } else if (data.fallback) {
                    setStatus('🔍 Mencari wajah...', 'neutral');
                } else {
                    setStatus('❌ Wajah tidak dikenali', 'error');
                }

            } catch (err) {
                setStatus('⚠️ Error koneksi', 'error');
            }

            isProcessing = false;
        }

        function startScanLoop() {
            scanTimer = setInterval(captureAndIdentify, SCAN_INTERVAL);
        }

        startBtn.addEventListener('click', () => {
            if (!sessionSelect.value) {
                alert('Pilih sesi absensi terlebih dahulu!');
                return;
            }
            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            setStatus('🟢 Scan Aktif', 'neutral');
            captureAndIdentify();
            startScanLoop();
        });

        stopBtn.addEventListener('click', () => {
            clearInterval(scanTimer);
            scanTimer = null;
            stopBtn.style.display = 'none';
            startBtn.style.display = 'block';
            setStatus('⏹ Scan dihentikan.', 'neutral');
            isProcessing = false;
        });
    </script>
@endsection