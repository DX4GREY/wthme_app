@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:700px; margin:0 auto;">

        {{-- Header --}}
        <div style="text-align:center; margin-bottom:2.5rem;">
            <span style="display:inline-block; padding:0.5rem 1.25rem; background:rgba(0,47,69,0.05); border-radius:2rem; color:#002f45; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:1rem;">
                Suara Peserta
            </span>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                Sampaikan <span style="color:#6b705c; font-style:italic;">Suara Kamu</span>
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:1rem; margin-top:0.75rem;">
                Punya saran, kritik, atau keluhan? Sampaikan kepada panitia di sini.
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
        @if ($errors->any())
        <div style="background:#f8d7da; border:1px solid #f5c6cb; border-radius:1rem; padding:1rem 1.5rem; margin-bottom:1.5rem;">
            <ul style="margin:0; color:#721c24; font-weight:500;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Form Card --}}
        <div style="background:rgba(255,255,255,0.4); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:2rem; padding:2.5rem; box-shadow:0 8px 32px rgba(0,47,69,0.05);">
            <form action="{{ route('peserta.suara.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Pesan --}}
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; color:#002f45; font-weight:700; font-size:0.9rem; margin-bottom:0.5rem;">
                        Pesan <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea name="pesan" rows="6" required
                        placeholder="Tulis saran, kritik, atau keluhan kamu di sini..."
                        style="width:100%; padding:1rem; border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-family:'Inter',sans-serif; font-size:0.95rem; outline:none; resize:vertical; box-sizing:border-box; background:rgba(255,255,255,0.6); transition:border-color 0.3s;"
                        onfocus="this.style.borderColor='#002f45'"
                        onblur="this.style.borderColor='rgba(0,47,69,0.15)'"
                    >{{ old('pesan') }}</textarea>
                </div>

                {{-- Foto --}}
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; color:#002f45; font-weight:700; font-size:0.9rem; margin-bottom:0.5rem;">
                        Foto (Opsional)
                    </label>
                    <div style="position:relative;">
                        <input type="file" name="foto" id="fotoInput" accept="image/*"
                            style="width:100%; padding:0.75rem; border:1px solid rgba(0,47,69,0.15); border-radius:1rem; background:rgba(255,255,255,0.6); outline:none; box-sizing:border-box; cursor:pointer;">
                        <p style="color:#002f45; opacity:0.4; font-size:0.75rem; margin:0.5rem 0 0 0;">
                            Format: JPEG, PNG, JPG, GIF, WebP. Maks: 5MB
                        </p>
                    </div>
                    {{-- Preview Foto --}}
                    <div id="fotoPreview" style="display:none; margin-top:0.75rem;">
                        <img id="previewImage" src="#" alt="Preview"
                            style="max-width:200px; max-height:200px; border-radius:1rem; border:1px solid rgba(0,47,69,0.1);">
                    </div>
                </div>

                {{-- Opsi Anonim --}}
                <div style="margin-bottom:2rem; background:rgba(0,47,69,0.03); border-radius:1rem; padding:1.25rem; border:1px solid rgba(0,47,69,0.08);">
                    <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer;">
                        <input type="checkbox" name="anonim" value="1" {{ old('anonim') ? 'checked' : '' }}
                            style="width:1.25rem; height:1.25rem; accent-color:#002f45; cursor:pointer;">
                        <div>
                            <span style="color:#002f45; font-weight:700; font-size:0.9rem;">Kirim Secara Anonim</span>
                            <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin:0.25rem 0 0 0;">
                                Nama dan identitas kamu tidak akan ditampilkan ke panitia
                            </p>
                        </div>
                    </label>
                    <p style="color:#6b705c; font-size:0.75rem; margin:0.75rem 0 0 0; font-style:italic;">
                        🔒 Identitas asli tetap aman dan hanya bisa dilihat oleh admin sistem.
                    </p>
                </div>

                {{-- Tombol Kirim --}}
                <div style="display:flex; gap:1rem;">
                    <button type="submit"
                        style="flex:1; background:#002f45; color:white; border:none; padding:1rem; border-radius:1rem; font-size:1rem; font-weight:700; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:0.5rem;"
                        onmouseover="this.style.background='#001f2e'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'">
                        <span>📨</span> Kirim Suara
                    </button>
                    <a href="{{ route('peserta.index') }}"
                        style="flex:0.4; background:rgba(255,255,255,0.6); color:#002f45; text-decoration:none; border:1px solid rgba(0,47,69,0.15); padding:1rem; border-radius:1rem; font-size:1rem; font-weight:600; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center;"
                        onmouseover="this.style.background='rgba(255,255,255,0.9)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.6)'">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Info Box --}}
        <div style="margin-top:1.5rem; background:rgba(0,47,69,0.05); border-radius:1.5rem; padding:1.25rem 2rem; border:1px solid rgba(0,47,69,0.1); display:flex; align-items:center; gap:1rem;">
            <span style="font-size:2rem;">💡</span>
            <div>
                <p style="color:#002f45; font-weight:700; margin:0 0 0.25rem 0;">Setiap suara sangat berarti!</p>
                <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin:0;">
                    Tim panitia akan membaca setiap masukan yang masuk untuk meningkatkan kualitas acara.
                </p>
            </div>
        </div>

    </div>
</div>

<script>
    // Preview foto sebelum upload
    document.getElementById('fotoInput').addEventListener('change', function(e) {
        const preview = document.getElementById('fotoPreview');
        const img = document.getElementById('previewImage');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            img.src = '#';
        }
    });
</script>
@endsection