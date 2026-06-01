@extends('layouts.app')

@section('content')
    {{-- Background Wrapper --}}
    <div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:700px; margin:0 auto;">

            {{-- Navigasi Kembali --}}
            <a href="{{ route('peserta.index') }}"
                style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7; transition:0.2s;"
                onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Portal
            </a>

            {{-- Header Section --}}
            <div style="margin-bottom:2rem;">
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.25rem; font-weight:800; margin-bottom:0.5rem;">
                    📚 Pengumpulan Tugas
                </h1>
                <p style="color:#002f45; opacity:0.6; font-size:1rem; font-weight:500;">
                    Halo, <strong>{{ $user->name }}</strong> — Kelompok {{ $user->kelompok }}
                </p>
            </div>

            {{-- Notifikasi --}}
            @if (session('success'))
                <div style="padding:1rem 1.5rem; background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); border-radius:1.25rem; color:#166534; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            {{-- Progress Ringkas --}}
            @php
                $sudahCount = $sudahKumpul->count();
                $totalCount = $tugasList->count();
            @endphp
            @if ($totalCount > 0)
                <div style="background: rgba(0, 47, 69, 0.9); backdrop-filter: blur(10px); border-radius:1.5rem; padding:1.75rem; margin-bottom:2.5rem; display:flex; align-items:center; gap:2rem; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 35px rgba(0, 47, 69, 0.2);">
                    <div style="text-align:center;">
                        <div style="color:#d2c296; font-size:2.5rem; font-weight:800; font-family:'Playfair Display',serif; line-height:1;">
                            {{ $sudahCount }}<span style="font-size:1.2rem; opacity:0.5;">/{{ $totalCount }}</span>
                        </div>
                        <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em; margin-top:0.5rem; font-weight:700;">
                            Tugas Selesai
                        </div>
                    </div>
                    <div style="flex:1;">
                        <div style="background:rgba(255,255,255,0.1); border-radius:999px; height:10px; overflow:hidden;">
                            <div style="background: linear-gradient(90deg, #d2c296, #f3e5ab); height:100%; border-radius:999px; width:{{ $totalCount > 0 ? round(($sudahCount / $totalCount) * 100) : 0 }}%;">
                            </div>
                        </div>
                        <div style="color:#bdd1d3; font-size:0.8rem; margin-top:0.75rem; font-weight:500;">
                            {{ $totalCount - $sudahCount }} tugas lagi yang perlu perhatianmu.
                        </div>
                    </div>
                </div>
            @endif

            @if ($tugasList->isEmpty())
                <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(15px); border-radius:2rem; padding:4rem 2rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2);">
                    <div style="font-size:4rem; margin-bottom:1rem;">📭</div>
                    <p style="color:#002f45; opacity:0.6; font-weight:600;">Belum ada tugas yang dibuka oleh panitia.</p>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    @foreach ($tugasList as $tugas)
                        @php
                            $kumpulan = $sudahKumpul[$tugas->id] ?? null;
                            $sudah = !is_null($kumpulan);
                            $isLewat = $tugas->isTerlambat();
                            $ekstensiOke = implode(', ', array_map('strtoupper', $tugas->ekstensiDiizinkan()));
                        @endphp

                        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:2rem; border:1px solid rgba(255, 255, 255, 0.5); overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">

                            <div style="padding:1.75rem; {{ $sudah ? 'background: rgba(34, 197, 94, 0.05);' : ($isLewat ? 'background: rgba(239, 68, 68, 0.05);' : '') }}">
                                <div style="margin-bottom: 1rem;">
                                    @if ($sudah)
                                        <span style="display:inline-block; padding:0.25rem 0.75rem; background:#dcfce7; color:#166534; border-radius:2rem; font-size:0.7rem; font-weight:800; text-transform:uppercase; margin-bottom:0.75rem;">
                                            {{ $kumpulan->status === 'tepat_waktu' ? '✓ Selesai' : '⚠ Terlambat' }}
                                        </span>
                                    @elseif($isLewat)
                                        <span style="display:inline-block; padding:0.25rem 0.75rem; background:#fee2e2; color:#991b1b; border-radius:2rem; font-size:0.7rem; font-weight:800; text-transform:uppercase; margin-bottom:0.75rem;">
                                            ⏰ Deadline Lewat
                                        </span>
                                    @else
                                        <span style="display:inline-block; padding:0.25rem 0.75rem; background:#fef9c3; color:#854d0e; border-radius:2rem; font-size:0.7rem; font-weight:800; text-transform:uppercase; margin-bottom:0.75rem;">
                                            ⏳ Menunggu
                                        </span>
                                    @endif
                                    <h3 style="color:#002f45; font-weight:800; font-size:1.25rem; margin:0;">
                                        {{ $tugas->nama_tugas }}
                                    </h3>
                                </div>

                                @if ($tugas->deskripsi)
                                    <p style="color:#002f45; opacity:0.8; font-size:0.95rem; line-height:1.6; margin-bottom:1.25rem; white-space: pre-line;">
                                        {{ $tugas->deskripsi }}
                                    </p>
                                @endif

                                {{-- BAGIAN PREVIEW FILE PETUNJUK --}}
                                @if ($tugas->file_petunjuk)
                                    <div style="margin-bottom: 1.5rem; border: 1px solid rgba(0,47,69,0.1); border-radius: 1.25rem; background: rgba(255,255,255,0.3); overflow: hidden;">
                                        <div style="padding: 0.6rem 1rem; background: rgba(0,47,69,0.05); border-bottom: 1px solid rgba(0,47,69,0.05); display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-size: 0.75rem; font-weight: 700; color: #002f45;">📄 LAMPIRAN PETUNJUK</span>
                                            <a href="{{ asset('storage/' . $tugas->file_petunjuk) }}" target="_blank" rel="noopener noreferrer" style="font-size: 0.7rem; color: #002f45; font-weight: 700; text-decoration: underline;">Buka di Tab Baru</a>
                                        </div>

                                        <div style="padding: 10px;">
                                            @php
                                                $urlPetunjuk = asset('storage/' . $tugas->file_petunjuk);
                                                $extPetunjuk = strtolower(pathinfo($tugas->file_petunjuk, PATHINFO_EXTENSION));
                                            @endphp

                                            @if (in_array($extPetunjuk, ['jpg', 'jpeg', 'png', 'webp']))
                                                <img src="{{ $urlPetunjuk }}" style="width: 100%; height: auto; border-radius: 0.75rem; display: block;">
                                            @elseif($extPetunjuk == 'pdf')
                                                <div style="width: 100%; height: 400px; border-radius: 0.75rem; overflow: hidden; background: #f0f0f0; position: relative;">
                                                    <object data="{{ $urlPetunjuk }}#toolbar=0&navpanes=0" type="application/pdf" width="100%" height="100%">
                                                        <div style="padding: 40px; text-align: center;">
                                                            <p style="font-size: 0.85rem; color: #002f45; font-weight: 600;">Preview PDF tidak tersedia di browser ini.</p>
                                                            <a href="{{ $urlPetunjuk }}" target="_blank" style="display: inline-block; margin-top: 10px; padding: 0.5rem 1rem; background: #002f45; color: #d2c296; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 800;">Klik untuk Lihat PDF</a>
                                                        </div>
                                                    </object>
                                                </div>
                                            @else
                                                <div style="padding: 1rem; text-align: center;">
                                                    <a href="{{ $urlPetunjuk }}" target="_blank" style="display: inline-block; padding: 0.5rem 1rem; background: #002f45; color: white; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem;">
                                                        Download {{ strtoupper($extPetunjuk) }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div style="display:flex; gap:1.5rem; flex-wrap:wrap; padding-top:1rem; border-top:1px solid rgba(0,47,69,0.05);">
                                    @if ($tugas->deadline)
                                        <div style="display:flex; align-items:center; gap:0.4rem;">
                                            <span style="font-size:0.9rem;">⏰</span>
                                            <span style="font-size:0.8rem; color:{{ $isLewat ? '#991b1b' : '#002f45' }}; font-weight:700;">
                                                {{ $tugas->deadline->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                    @endif
                                    <div style="display:flex; align-items:center; gap:0.4rem;">
                                        <span style="font-size:0.9rem;">📎</span>
                                        <span style="font-size:0.8rem; color:#002f45; opacity:0.6; font-weight:600;">
                                            {{ $ekstensiOke }} · Max {{ round($tugas->maks_ukuran / 1024, 0) }}MB per file
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Status Pengumpulan Peserta --}}
                            @if ($sudah)
                                <div style="margin:0 1.5rem 1.5rem 1.5rem; padding:1.25rem; background: rgba(255, 255, 255, 0.4); border: 1px solid rgba(34, 197, 94, 0.2); border-radius:1.25rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                                    <div style="display:flex; align-items:center; gap:1rem; flex: 1; min-width: 0;">
                                        <div style="width:45px; height:45px; background:white; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; font-size:1.5rem; box-shadow:0 4px 10px rgba(0,0,0,0.05); flex-shrink: 0;">
                                            📂
                                        </div>
                                        <div style="min-width: 0; flex: 1;">
                                            <div style="color:#002f45; font-size:0.85rem; font-weight:700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $kumpulan->file_nama_asli }}">
                                                {{ $kumpulan->file_nama_asli }}
                                            </div>
                                            <div style="color:#002f45; opacity:0.5; font-size:0.75rem; font-weight:600; margin-top:0.2rem;">
                                                {{ $kumpulan->ukuranFormatted() }} · {{ $kumpulan->dikumpulkan_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    <button onclick="toggleUpload({{ $tugas->id }})" style="background:#002f45; color:white; border:none; padding:0.5rem 1rem; border-radius:0.75rem; font-size:0.75rem; font-weight:700; cursor:pointer; flex-shrink: 0;">
                                        Ganti Berkas
                                    </button>
                                </div>
                            @endif

                            {{-- Form Upload (Mendukung Banyak Berkas) --}}
                            @php $showForm = !$sudah || $errors->has('file_tugas'); @endphp
                            <div id="upload-{{ $tugas->id }}" style="display:{{ $showForm ? 'block' : 'none' }}; padding:0 1.5rem 1.5rem 1.5rem;">
                                <form method="POST" action="{{ route('peserta.tugas.upload') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="tugas_kategori_id" value="{{ $tugas->id }}">
                                    
                                    <label for="file-{{ $tugas->id }}" id="label-{{ $tugas->id }}"
                                        style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; background: rgba(255, 255, 255, 0.4); border:2px dashed rgba(0, 47, 69, 0.2); border-radius:1.5rem; cursor:pointer; text-align:center; transition:0.2s; margin-bottom:1rem;">
                                        <div style="font-size:2.5rem; margin-bottom:0.75rem;">📤</div>
                                        <span id="file-label-{{ $tugas->id }}" style="color:#002f45; font-weight:700; font-size:0.9rem;">
                                            {{ $sudah ? 'Pilih berkas-berkas baru' : 'Klik atau seret berkas-berkas ke sini (Bisa pilih banyak)' }}
                                        </span>
                                        
                                        {{-- MODIFIKASI: Ditambahkan atribut 'multiple' dan kurung siku [] pada name --}}
                                        <input type="file" id="file-{{ $tugas->id }}" name="file_tugas[]" multiple
                                            accept="{{ implode(',', array_map(fn($e) => '.' . $e, $tugas->ekstensiDiizinkan())) }}"
                                            style="display:none;" required
                                            onchange="handleFileSelected(this, {{ $tugas->id }})">
                                    </label>
                                    
                                    <div style="margin-bottom:1.25rem;">
                                        <input type="text" name="catatan" value="{{ old('catatan', $kumpulan->catatan ?? '') }}"
                                            placeholder="Tambahkan catatan (opsional)..."
                                            style="width:100%; padding:0.9rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:1rem; font-size:0.9rem; color:#002f45; outline:none; font-weight:600; box-sizing:border-box;">
                                    </div>
                                    <div style="display:flex; gap:0.75rem;">
                                        <button type="submit" style="flex:1; padding:1rem; background:#002f45; color:#d2c296; font-weight:800; border:none; border-radius:1rem; cursor:pointer;">
                                            {{ $sudah ? 'Simpan Perubahan' : 'Kumpulkan Sekarang' }}
                                        </button>
                                        @if ($sudah)
                                            <button type="button" onclick="toggleUpload({{ $tugas->id }})" style="padding:1rem 1.5rem; background:transparent; color:#002f45; border:1px solid rgba(0,47,69,0.2); border-radius:1rem; cursor:pointer; font-weight:700;">
                                                Batal
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <p style="color:#002f45; opacity:0.4; font-size:0.8rem; margin-top:2rem; text-align:center; font-weight:600;">
                💡 Pastikan koneksi internet stabil saat mengunggah file besar.
            </p>
        </div>
    </div>

    <script>
        function toggleUpload(id) {
            const el = document.getElementById('upload-' + id);
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }

        // MODIFIKASI: Menghitung dinamis jumlah berkas yang diseleksi oleh user
        function handleFileSelected(input, id) {
            const label = document.getElementById('file-label-' + id);
            const wrap = document.getElementById('label-' + id);
            
            if (input.files.length > 0) {
                if (input.files.length === 1) {
                    label.textContent = "📄 " + input.files[0].name;
                } else {
                    label.textContent = "📂 " + input.files.length + " file berhasil dipilih";
                }
                wrap.style.borderColor = '#002f45';
                wrap.style.background = 'rgba(255, 255, 255, 0.8)';
            }
        }
    </script>
@endsection