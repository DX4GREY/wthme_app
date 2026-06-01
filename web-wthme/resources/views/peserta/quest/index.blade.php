@extends('layouts.app')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(135deg, #bdd1d3 0%, #e0decd 100%); padding: 3rem 1.5rem; font-family: 'Segoe UI', sans-serif;">
    <div style="max-width: 900px; margin: 0 auto;">
        
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h1 style="font-family: 'Playfair Display', serif; color: #002f45; font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">🧭 Berburu Lab Elektro</h1>
            <p style="color: #002f45; opacity: 0.7; font-size: 1rem;">Temukan 4 Laboratorium Elektro di lingkungan kampus, ambil foto langsung dari web atau unggah berkas!</p>
            <a href="{{ url('/dashboard') }}" style="color: #002f45; font-weight: 600; text-decoration: none; font-size: 0.9rem;">← Kembali ke Beranda</a>
        </div>

        @if(session('success'))
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;">{{ session('error') }}</div>
        @endif

        {{-- LAYAR LOADING PROSES KOMPRESI & UPLOAD --}}
        <div id="upload-loading" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: white;">
            <div class="spinner"></div>
            <p style="font-weight: 600; margin-top: 1rem;" id="loading-text">Memproses Gambar...</p>
        </div>

        {{-- JENDELA MODAL KAMERA LIVE (LAPTOP & HP) --}}
        <div id="camera-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 999; justify-content: center; align-items: center; flex-direction: column; padding: 20px;">
            <div style="background: white; padding: 1.5rem; border-radius: 24px; max-width: 500px; width: 100%; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <h3 id="modal-lab-title" style="color: #002f45; margin-top: 0; margin-bottom: 1rem;">Kamera Aktif</h3>
                
                <div style="position: relative; width: 100%; aspect-ratio: 4/3; background: #000; border-radius: 16px; overflow: hidden; margin-bottom: 1rem;">
                    {{-- Class dinamis dialihkan lewat JavaScript (Mirror hanya untuk kamera depan) --}}
                    <video id="webcam-video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                    <canvas id="webcam-canvas" style="display: none;"></canvas>
                    
                    {{-- TOMBOL SWITCH KAMERA (Hanya muncul jika device punya banyak kamera) --}}
                    <button type="button" id="switch-camera-btn" onclick="switchCamera()" style="display: none; position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); color: white; border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; justify-content: center; align-items: center; z-index: 10;">
                        🔄
                    </button>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="takeSnapshot()" style="flex: 2; background: #059669; color: white; border: none; padding: 0.8rem; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem;">📸 Jepret Foto</button>
                    <button type="button" onclick="closeCamera()" style="flex: 1; background: #ea580c; color: white; border: none; padding: 0.8rem; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem;">Batal</button>
                </div>
            </div>
        </div>

        {{-- DAFTAR CARD LAB --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            @foreach($listLab as $lab)
                @php 
                    $q = $quests->get($lab); 
                    $cleanId = str_replace([' ', '/', '-', '_'], '', $lab);
                @endphp
                <div style="background: rgba(255,255,255,0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); border-radius: 20px; padding: 1.5rem; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <div>
                        <h3 style="color: #002f45; margin-bottom: 1rem;">{{ $lab }}</h3>
                        
                        @if($q)
                            @if($q->status == 'pending')
                                <span style="background: #fef3c7; color: #d97706; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700;">⌛ Menunggu ACC</span>
                            @elseif($q->status == 'approved')
                                <span style="background: #d1fae5; color: #059669; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700;">✓ Terverifikasi</span>
                            @else
                                <span style="background: #fee2e2; color: #dc2626; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700;">❌ Ditolak (Ulangi)</span>
                            @endif
                            
                            <div style="margin-top: 1rem; position: relative;">
                                <img src="{{ asset('storage/quests/'.$q->foto_selfie) }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 12px;">
                            </div>
                        @endif
                    </div>

                    <div style="margin-top: 1rem;">
                        @if($q && $q->status != 'approved')
                            <form action="{{ route('peserta.quest.delete', $lab) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto ini untuk menggantinya dengan yang baru?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #dc2626; color: white; padding: 0.5rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600; cursor: pointer; border: none; width: 100%; transition: 0.2s;">
                                    🗑️ Hapus & Ganti Foto
                                </button>
                            </form>
                        @endif

                        @if(!$q)
                            <form action="{{ route('peserta.quest.upload', $lab) }}" method="POST" enctype="multipart/form-data" id="form_{{ $cleanId }}" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @csrf
                                <input type="file" name="foto" id="main_file_{{ $cleanId }}" style="display: none;">
                                
                                <button type="button" onclick="openCamera('{{ $lab }}', '{{ $cleanId }}')" style="background: #002f45; color: white; padding: 0.6rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; width: 100%;">
                                    📷 Ambil Foto Live
                                </button>

                                <input type="file" id="fallback_{{ $cleanId }}" accept="image/*" style="display: none;" onchange="processFileOnly('{{ $cleanId }}', this)">
                                <label for="fallback_{{ $cleanId }}" style="display: block; background: rgba(0,47,69,0.05); color: #002f45; padding: 0.5rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600; cursor: pointer; border: 1px dashed #002f45;">
                                    📁 Alternatif: Unggah File
                                </label>
                            </form>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>

<script>
let activeStream = null;
let currentLabName = '';
let currentCleanId = '';
let useFacingMode = "user"; // Default awal: kamera depan

const video = document.getElementById('webcam-video');
const canvas = document.getElementById('webcam-canvas');
const modal = document.getElementById('camera-modal');
const loading = document.getElementById('upload-loading');
const switchBtn = document.getElementById('switch-camera-btn');

// Fungsi utama membuka kamera dengan mode dinamis
async function startStream() {
    // Stop stream lama jika sedang berjalan sebelum ganti mode
    if (activeStream) {
        activeStream.getTracks().forEach(track => track.stop());
    }

    try {
        activeStream = await navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: useFacingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        });
        
        video.srcObject = activeStream;

        // Efek cermin (mirror) HANYA berlaku untuk kamera depan ('user') agar user tidak bingung
        if (useFacingMode === "user") {
            video.style.transform = "scaleX(-1)";
        } else {
            video.style.transform = "scaleX(1)";
        }

        // Cek ketersediaan jumlah video input (kamera depan + belakang)
        checkCameraDevices();

    } catch (err) {
        console.error(err);
        alert("Gagal memuat kamera. Coba opsi 'Alternatif: Unggah File'.");
    }
}

// Fungsi mengecek ketersediaan multi-kamera di perangkat
async function checkCameraDevices() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(device => device.kind === 'videoinput');
        
        // Jika kamera lebih dari 1 (misal di HP), tampilkan tombol ganti kamera
        if (videoDevices.length > 1) {
            switchBtn.style.display = 'flex';
        } else {
            switchBtn.style.display = 'none';
        }
    } catch (e) {
        switchBtn.style.display = 'none';
    }
}

// Handler pemicu klik tombol modal kamera
function openCamera(labName, cleanId) {
    currentLabName = labName;
    currentCleanId = cleanId;
    document.getElementById('modal-lab-title').innerText = "Kamera: " + labName;
    modal.style.display = 'flex';
    
    useFacingMode = "user"; // Setiap kali klik tombol baru, reset ke kamera depan dahulu
    startStream();
}

// Fungsi mengganti mode kamera (Depan <=> Belakang)
function switchCamera() {
    useFacingMode = (useFacingMode === "user") ? "environment" : "user";
    startStream();
}

function takeSnapshot() {
    if (!activeStream) return;
    
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Sesuaikan penyimpanan gambar canvas dengan status efek mirror aktif
    if (useFacingMode === "user") {
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
    }
    
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    closeCamera();
    
    loading.style.display = 'flex';
    document.getElementById('loading-text').innerText = "Menyelaraskan & Mengompres Foto...";

    canvas.toBlob((blob) => {
        compressAndSubmit(blob, `selfie_${currentCleanId}.jpg`);
    }, 'image/jpeg', 0.9);
}

function compressAndSubmit(fileOrBlob, filename) {
    new Compressor(fileOrBlob, {
        quality: 0.6,
        maxWidth: 1000,
        maxHeight: 1000,
        success(result) {
            document.getElementById('loading-text').innerText = "Sedang Mengunggah Gambar...";
            const form = document.getElementById('form_' + currentCleanId);
            const mainFileInput = document.getElementById('main_file_' + currentCleanId);
            
            const dataTransfer = new DataTransfer();
            const compressedFile = new File([result], filename, {
                type: result.type,
                lastModified: Date.now()
            });
            
            dataTransfer.items.add(compressedFile);
            mainFileInput.files = dataTransfer.files;
            
            form.submit();
        },
        error(err) {
            console.error(err);
            alert('Gagal memproses pengecilan ukuran gambar.');
            loading.style.display = 'none';
        }
    });
}

function processFileOnly(cleanId, input) {
    if(!input.files[0]) return;
    currentCleanId = cleanId;
    loading.style.display = 'flex';
    document.getElementById('loading-text').innerText = "Mengompres Berkas Gambar...";
    compressAndSubmit(input.files[0], input.files[0].name);
}

function closeCamera() {
    if (activeStream) {
        activeStream.getTracks().forEach(track => track.stop());
    }
    modal.style.display = 'none';
    video.srcObject = null;
    activeStream = null;
}
</script>

<style>
.spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #fff;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection