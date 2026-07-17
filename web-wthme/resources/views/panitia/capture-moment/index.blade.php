@extends('layouts.app')

@section('content')
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:1100px; margin:0 auto;">

            {{-- HEADER --}}
            <div style="margin-bottom:2.5rem; text-align:center;">
                <div style="display:inline-block; padding:0.4rem 1.25rem; background:rgba(0,47,69,0.05); border-radius:2rem; color:#002f45; font-size:0.7rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:0.75rem;">
                    Panel Penilaian
                </div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.2rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    📸 Kelola <span style="color:#6b705c; font-style:italic;">Capture Moment</span>
                </h1>
                <p style="color:#002f45; opacity:0.6; margin-top:0.4rem; font-size:0.95rem;">
                    Nilai foto tiap kelompok — ranking & poin terhitung otomatis dari total skor.
                </p>
            </div>

            {{-- FLASH MESSAGES --}}
            @if (session('success'))
                <div style="background:#e6f4ea; color:#1e7e34; padding:0.9rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem; font-weight:600; display:flex; align-items:center; gap:0.6rem;">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div style="background:#fde8e8; color:#b91c1c; padding:0.9rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem; font-weight:600; display:flex; align-items:center; gap:0.6rem;">
                    <span>⛔</span> {{ session('error') }}
                </div>
            @endif

            {{-- SETTING PERIODE --}}
            <div style="background:rgba(255,255,255,0.45); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:1.5rem; padding:1.5rem 1.75rem; margin-bottom:2.5rem; box-shadow:0 8px 32px rgba(0,0,0,0.06);">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; margin-bottom:1rem;">
                    <h3 style="color:#002f45; font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin:0;">
                        ⏰ Atur Periode Quest
                    </h3>
                    <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.35rem 1rem; border-radius:2rem; font-size:0.8rem; font-weight:700;
                        background:{{ $setting->sedangBerjalan() ? 'rgba(107,160,99,0.12)' : 'rgba(239,68,68,0.1)' }};
                        color:{{ $setting->sedangBerjalan() ? '#2d6e28' : '#b91c1c' }};">
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $setting->sedangBerjalan() ? '#2d6e28' : '#b91c1c' }};"></span>
                        {{ $setting->statusLabel() }}
                    </span>
                </div>
                <form action="{{ route('panitia.capture.settings') }}" method="POST"
                    style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
                    @csrf
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:600; color:#002f45; opacity:0.6; margin-bottom:4px;">Mulai</label>
                        <input type="datetime-local" name="mulai_at"
                            value="{{ $setting->mulai_at?->format('Y-m-d\TH:i') }}"
                            style="padding:0.5rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.15); background:rgba(255,255,255,0.5); font-size:0.85rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:600; color:#002f45; opacity:0.6; margin-bottom:4px;">Deadline</label>
                        <input type="datetime-local" name="selesai_at"
                            value="{{ $setting->selesai_at?->format('Y-m-d\TH:i') }}"
                            style="padding:0.5rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.15); background:rgba(255,255,255,0.5); font-size:0.85rem;">
                    </div>
                    <button type="submit"
                        style="display:inline-flex; align-items:center; gap:0.4rem; background:#002f45; color:#fff; padding:0.55rem 1.5rem; border:none; border-radius:0.75rem; font-weight:700; font-size:0.85rem; cursor:pointer; transition:all 0.3s;"
                        onmouseover="this.style.background='#001f2e'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'">
                        💾 Simpan
                    </button>
                </form>
            </div>

            {{-- DAFTAR FOTO PER KELOMPOK --}}
            @if ($foto->isEmpty())
                <div style="text-align:center; padding:3rem 1rem; background:rgba(255,255,255,0.2); border-radius:1.5rem;">
                    <div style="font-size:3rem; margin-bottom:0.75rem;">📭</div>
                    <p style="color:#002f45; opacity:0.6; margin:0;">Belum ada kelompok yang upload foto.</p>
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.25rem;">
                    @foreach ($foto as $item)
                        <div style="background:rgba(255,255,255,0.35); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06);">
                            {{-- Thumbnail --}}
                            <div style="position:relative; cursor:pointer;"
                                onclick="bukaPreview('{{ asset('storage/' . $item->foto_path) }}', '{{ addslashes($item->caption ?? '') }}')">
                                <img src="{{ asset('storage/' . $item->foto_path) }}" alt="Foto Kelompok {{ $item->kelompok }}"
                                    loading="lazy"
                                    style="width:100%; height:220px; object-fit:cover; display:block;">
                                <div style="position:absolute; top:0; left:0; right:0; padding:0.6rem; display:flex; justify-content:space-between; pointer-events:none;">
                                    <span style="background:rgba(0,47,69,0.85); color:#fff; font-size:0.65rem; font-weight:800; padding:4px 12px; border-radius:50px; backdrop-filter:blur(4px);">
                                        Kelompok {{ $item->kelompok }}
                                    </span>
                                    @if ($item->labelJuara())
                                        <span style="background:rgba(210,194,150,0.9); color:#002f45; font-size:0.65rem; font-weight:800; padding:4px 12px; border-radius:50px; backdrop-filter:blur(4px);">
                                            {{ $item->labelJuara() }} · {{ $item->poin }} pts
                                        </span>
                                    @endif
                                    @if ($item->isRejected())
                                        <span style="background:#b91c1c; color:#fff; font-size:0.65rem; font-weight:800; padding:4px 12px; border-radius:50px; backdrop-filter:blur(4px);">
                                            ⛔ Ditolak
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div style="padding:1rem 1.1rem;">
                                {{-- Meta info --}}
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.25rem;">
                                    <span style="color:#002f45; opacity:0.5; font-size:0.75rem;">
                                        👤 {{ $item->uploader->name ?? '-' }}
                                    </span>
                                    <span style="color:#002f45; opacity:0.5; font-size:0.75rem;">
                                        💬 {{ $item->reactions->count() }} reaksi
                                    </span>
                                </div>

                                {{-- Caption --}}
                                @if ($item->caption)
                                    <p style="color:#002f45; font-size:0.85rem; margin:0 0 0.75rem 0; line-height:1.5; background:rgba(0,47,69,0.03); padding:0.5rem 0.75rem; border-radius:0.5rem;">
                                        {{ $item->caption }}
                                    </p>
                                @endif

                                {{-- Form penilaian --}}
                                <form action="{{ route('panitia.capture.nilai', $item->id) }}" method="POST">
                                    @csrf
                                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; margin-bottom:0.75rem;">
                                        <div>
                                            <label style="display:block; font-size:0.65rem; font-weight:600; color:#002f45; opacity:0.6; margin-bottom:3px;">Kelengkapan</label>
                                            <input type="number" name="skor_kelengkapan" min="0" max="100" required
                                                value="{{ $item->skor_kelengkapan }}"
                                                style="width:100%; padding:0.45rem; border-radius:0.5rem; border:1px solid rgba(0,47,69,0.12); background:rgba(255,255,255,0.5); font-size:0.85rem; text-align:center;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-size:0.65rem; font-weight:600; color:#002f45; opacity:0.6; margin-bottom:3px;">Tema</label>
                                            <input type="number" name="skor_tema" min="0" max="100" required
                                                value="{{ $item->skor_tema }}"
                                                style="width:100%; padding:0.45rem; border-radius:0.5rem; border:1px solid rgba(0,47,69,0.12); background:rgba(255,255,255,0.5); font-size:0.85rem; text-align:center;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-size:0.65rem; font-weight:600; color:#002f45; opacity:0.6; margin-bottom:3px;">Estetika</label>
                                            <input type="number" name="skor_estetika" min="0" max="100" required
                                                value="{{ $item->skor_estetika }}"
                                                style="width:100%; padding:0.45rem; border-radius:0.5rem; border:1px solid rgba(0,47,69,0.12); background:rgba(255,255,255,0.5); font-size:0.85rem; text-align:center;">
                                        </div>
                                    </div>
                                    <button type="submit"
                                        style="width:100%; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; background:#002f45; color:#fff; padding:0.6rem; border:none; border-radius:0.75rem; font-weight:700; font-size:0.85rem; cursor:pointer; transition:all 0.3s;"
                                        onmouseover="this.style.background='#001f2e'; this.style.transform='translateY(-2px)'"
                                        onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'">
                                        {{ $item->sudahDinilai() ? '🔄 Update Nilai (Total: ' . $item->total_skor . ')' : '📝 Simpan Nilai' }}
                                    </button>
                                </form>

                                {{-- Action buttons: Tolak & Hapus --}}
                                @if (!$item->sudahDinilai())
                                    <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                                        {{-- Tombol Tolak --}}
                                        <form action="{{ route('panitia.capture.tolak', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin menolak foto kelompok {{ $item->kelompok }}? Peserta dapat mengunggah ulang.')">
                                            @csrf
                                            <button type="submit"
                                                style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; background:#fef2f2; color:#b91c1c; padding:0.5rem; border:1px solid #fca5a5; border-radius:0.5rem; font-weight:600; font-size:0.8rem; cursor:pointer; transition:all 0.2s;"
                                                onmouseover="this.style.background='#fee2e2'"
                                                onmouseout="this.style.background='#fef2f2'">
                                                ⛔ Tolak Foto
                                            </button>
                                        </form>
                                        
                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('panitia.capture.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus foto kelompok {{ $item->kelompok }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; background:#fef2f2; color:#b91c1c; padding:0.5rem; border:1px solid #fca5a5; border-radius:0.5rem; font-weight:600; font-size:0.8rem; cursor:pointer; transition:all 0.2s;"
                                                onmouseover="this.style.background='#fee2e2'"
                                                onmouseout="this.style.background='#fef2f2'">
                                                🗑️ Hapus Foto
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL PREVIEW FOTO --}}
    <div id="previewModal"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:9999; align-items:center; justify-content:center; padding:2rem; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); cursor:pointer;"
        onclick="tutupPreview()">
        <div style="max-width:90vw; max-height:90vh; border-radius:1.25rem; overflow:hidden; box-shadow:0 30px 80px rgba(0,0,0,0.5); cursor:default; position:relative;"
            onclick="event.stopPropagation()">
            <button onclick="tutupPreview()"
                style="position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:50%; background:rgba(0,0,0,0.5); color:#fff; border:none; font-size:1.25rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.3s; z-index:10;"
                onmouseover="this.style.background='rgba(0,0,0,0.8)'" onmouseout="this.style.background='rgba(0,0,0,0.5)'">
                ✕
            </button>
            <img id="previewImage" src="" alt="Preview Foto"
                style="width:100%; max-width:800px; max-height:85vh; object-fit:contain; display:block; background:#111;">
            <div id="previewCaption" style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(0,0,0,0.7)); padding:2rem 1.25rem 1rem 1.25rem; color:#fff; font-size:0.9rem; font-weight:500; text-align:center; pointer-events:none;"></div>
        </div>
    </div>

    <script>
        function bukaPreview(src, caption) {
            document.getElementById('previewImage').src = src;
            document.getElementById('previewCaption').textContent = caption || '';
            document.getElementById('previewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function tutupPreview() {
            document.getElementById('previewModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupPreview();
        });
    </script>
@endsection