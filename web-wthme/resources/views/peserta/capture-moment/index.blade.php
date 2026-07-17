@extends('layouts.app')

@section('content')
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:900px; margin:0 auto;">

            {{-- HEADER --}}
            <div style="margin-bottom:2.5rem; text-align:center;">
                <div style="display:inline-block; padding:0.4rem 1.25rem; background:rgba(0,47,69,0.05); border-radius:2rem; color:#002f45; font-size:0.7rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:0.75rem;">
                    Quest Foto
                </div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.2rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    📸 <span style="color:#6b705c; font-style:italic;">Capture Moment</span>
                </h1>
                <p style="color:#002f45; opacity:0.6; margin-top:0.5rem; font-size:0.95rem;">
                    Abadikan momen kebersamaan kelompok kamu — Tema: <strong>Kekeluargaan</strong>
                </p>

                {{-- Status periode --}}
                <div style="display:flex; align-items:center; justify-content:center; gap:0.5rem; margin-top:0.75rem;">
                    <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.4rem 1.25rem; border-radius:2rem; font-size:0.8rem; font-weight:700;
                        background:{{ $setting->sedangBerjalan() ? 'rgba(107,160,99,0.12)' : 'rgba(239,68,68,0.1)' }};
                        color:{{ $setting->sedangBerjalan() ? '#2d6e28' : '#b91c1c' }};">
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $setting->sedangBerjalan() ? '#2d6e28' : '#b91c1c' }};"></span>
                        {{ $setting->statusLabel() }}
                    </span>
                    @if ($setting->selesai_at)
                        <span style="color:#002f45; opacity:0.5; font-size:0.8rem;">
                            Deadline: {{ $setting->selesai_at->translatedFormat('d M Y, H:i') }}
                        </span>
                    @endif
                </div>
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

            {{-- KARTU: FOTO KELOMPOK SENDIRI --}}
            <div style="background:rgba(255,255,255,0.45); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.6); border-radius:1.5rem; padding:1.75rem; margin-bottom:2.5rem; box-shadow:0 8px 32px rgba(0,0,0,0.06);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem;">
                    <h3 style="color:#002f45; font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700; margin:0;">
                        🏕️ Foto Kelompok {{ auth()->user()->kelompok }}
                    </h3>
                    @if ($milikKelompok && $milikKelompok->sudahDinilai())
                        <span style="display:inline-flex; align-items:center; gap:0.3rem; background:#002f45; color:#fff; padding:0.3rem 1rem; border-radius:2rem; font-size:0.8rem; font-weight:700;">
                            {{ $milikKelompok->labelJuara() ?? 'Dinilai' }} · {{ $milikKelompok->total_skor }} pts
                        </span>
                    @elseif ($milikKelompok)
                        <span style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(0,47,69,0.08); color:#002f45; padding:0.3rem 1rem; border-radius:2rem; font-size:0.8rem; font-weight:700;">
                            ⏳ Menunggu penilaian
                        </span>
                    @endif
                </div>

                @if ($milikKelompok)
                    {{-- Preview foto --}}
                    <div style="display:flex; gap:1.25rem; flex-wrap:wrap; align-items:flex-start; margin-bottom:1.25rem;">
                        <div style="width:220px; flex-shrink:0;">
                            <img src="{{ asset('storage/' . $milikKelompok->foto_path) }}" alt="Foto Kelompok {{ $milikKelompok->kelompok }}"
                                style="width:100%; height:160px; border-radius:1rem; object-fit:cover; box-shadow:0 4px 15px rgba(0,0,0,0.12); cursor:pointer; @if($milikKelompok->isRejected()) opacity:0.5; @endif"
                                onclick="bukaPreview('{{ asset('storage/' . $milikKelompok->foto_path) }}', '{{ addslashes($milikKelompok->caption ?? '') }}')">
                            @if ($milikKelompok->isRejected())
                                <div style="margin-top:0.4rem; text-align:center;">
                                    <span style="background:#b91c1c; color:#fff; font-size:0.7rem; font-weight:700; padding:2px 10px; border-radius:9999px;">
                                        ⛔ Foto Ditolak - Silakan upload ulang
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div style="flex:1; min-width:180px;">
                            @if ($milikKelompok->caption)
                                <p style="color:#002f45; margin:0 0 0.5rem 0; font-size:0.9rem; @if($milikKelompok->isRejected()) opacity:0.5; @endif">{{ $milikKelompok->caption }}</p>
                            @endif
                            <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin:0;">
                                Diupload oleh: <strong>{{ $milikKelompok->uploader->name ?? '-' }}</strong>
                            </p>
                            {{-- Tombol Hapus --}}
                            <form action="{{ route('peserta.capture.destroy', $milikKelompok->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus foto Capture Moment kelompok {{ auth()->user()->kelompok }}? Tindakan ini tidak bisa dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="margin-top:0.75rem; display:inline-flex; align-items:center; gap:0.4rem; background:rgba(239,68,68,0.1); color:#b91c1c; padding:0.4rem 1rem; border:1px solid rgba(239,68,68,0.2); border-radius:0.75rem; font-weight:700; font-size:0.8rem; cursor:pointer; transition:all 0.3s;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                                    🗑️ Hapus Foto
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div style="background:rgba(0,47,69,0.04); border:2px dashed rgba(0,47,69,0.15); border-radius:1rem; padding:1.5rem; text-align:center; margin-bottom:1.25rem;">
                        <div style="font-size:2rem; margin-bottom:0.5rem;">📸</div>
                        <p style="color:#002f45; opacity:0.6; margin:0;">Kelompok kamu belum upload foto. Yuk upload sebelum deadline!</p>
                    </div>
                @endif

                {{-- Form upload --}}
                @if ($setting->sedangBerjalan())
                    <form action="{{ route('peserta.capture.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="display:flex; flex-direction:column; gap:0.75rem;">
                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.35rem;">Pilih Foto</label>
                                <input type="file" name="foto" accept="image/*" required
                                    style="width:100%; padding:0.5rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.15); background:rgba(255,255,255,0.5); font-size:0.9rem;">
                                <p style="color:#002f45; opacity:0.4; font-size:0.7rem; margin:0.25rem 0 0 0;">Maks. 15 MB · Format gambar umum</p>
                            </div>
                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.35rem;">Caption</label>
                                <textarea name="caption" placeholder="Tulis cerita singkat tentang foto ini..." rows="2"
                                    style="width:100%; padding:0.65rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.15); background:rgba(255,255,255,0.5); font-family:'Inter',sans-serif; resize:vertical;">{{ old('caption', $milikKelompok->caption ?? '') }}</textarea>
                            </div>
                            <button type="submit"
                                style="align-self:flex-start; display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#fff; padding:0.7rem 1.75rem; border:none; border-radius:0.75rem; font-weight:700; font-size:0.9rem; cursor:pointer; transition:all 0.3s;"
                                onmouseover="this.style.background='#001f2e'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'">
                                {{ $milikKelompok ? '🔄 Ganti Foto' : '📤 Upload Foto' }}
                            </button>
                        </div>
                    </form>
                @else
                    <div style="background:rgba(239,68,68,0.06); border-radius:0.75rem; padding:0.75rem 1rem;">
                        <p style="color:#b91c1c; font-weight:600; margin:0; font-size:0.9rem; display:flex; align-items:center; gap:0.5rem;">
                            🔒 Periode upload sudah ditutup atau belum dibuka oleh panitia.
                        </p>
                    </div>
                @endif
            </div>

            {{-- GALERI SEMUA KELOMPOK --}}
            <div style="margin-bottom:1.5rem;">
                <h3 style="color:#002f45; font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; margin:0 0 0.25rem 0;">
                    🖼️ Galeri Semua Kelompok
                </h3>
                <p style="color:#002f45; opacity:0.5; font-size:0.85rem; margin:0;">Berikan reaksi ke foto kelompok lain!</p>
            </div>

            @if ($semuaFoto->isEmpty())
                <div style="text-align:center; padding:3rem 1rem; background:rgba(255,255,255,0.2); border-radius:1.5rem;">
                    <div style="font-size:3rem; margin-bottom:0.75rem;">📭</div>
                    <p style="color:#002f45; opacity:0.6; margin:0;">Belum ada kelompok yang upload foto.</p>
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1.25rem;">
                    @foreach ($semuaFoto as $foto)
                        <div class="foto-card" style="background:rgba(255,255,255,0.35); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06); transition:transform 0.3s ease; cursor:pointer;"
                            data-foto-id="{{ $foto->id }}"
                            data-foto-src="{{ asset('storage/' . $foto->foto_path) }}"
                            data-foto-caption="{{ addslashes($foto->caption ?? '') }}"
                            data-foto-kelompok="{{ $foto->kelompok }}"
                            data-foto-uploader="{{ $foto->uploader->name ?? '—' }}"
                            data-foto-reactions="{{ $foto->reactions->count() }}"
                            onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                            {{-- Thumbnail --}}
                            <img src="{{ asset('storage/' . $foto->foto_path) }}" alt="Foto Kelompok {{ $foto->kelompok }}"
                                loading="lazy"
                                style="width:100%; height:200px; object-fit:cover; display:block;">
                            <div style="position:absolute; top:0; left:0; right:0; padding:0.6rem; display:flex; justify-content:space-between; pointer-events:none;">
                                <span style="background:rgba(0,47,69,0.85); color:#fff; font-size:0.65rem; font-weight:800; padding:4px 12px; border-radius:50px; backdrop-filter:blur(4px);">
                                    Kelompok {{ $foto->kelompok }}
                                </span>
                                @if ($foto->labelJuara())
                                    <span style="background:rgba(210,194,150,0.9); color:#002f45; font-size:0.65rem; font-weight:800; padding:4px 12px; border-radius:50px; backdrop-filter:blur(4px);">
                                        {{ $foto->labelJuara() }}
                                    </span>
                                @endif
                                @if ($foto->isRejected())
                                    <span style="background:#b91c1c; color:#fff; font-size:0.65rem; font-weight:800; padding:4px 12px; border-radius:50px; backdrop-filter:blur(4px);">
                                        ⛔ Ditolak
                                    </span>
                                @endif
                            </div>

                            <div style="padding:0.9rem 1rem;">
                                {{-- Caption --}}
                                @if ($foto->caption)
                                    <p style="color:#002f45; font-size:0.85rem; margin:0 0 0.75rem 0; line-height:1.5;">{{ $foto->caption }}</p>
                                @endif

                                {{-- Reaction summary --}}
                                <div style="display:flex; gap:4px; flex-wrap:wrap; margin-bottom:0.75rem;">
                                    @forelse ($foto->reactions->groupBy('emoji') as $emoji => $group)
                                        <span style="background:rgba(0,47,69,0.06); padding:3px 10px; border-radius:50px; font-size:0.8rem; font-weight:600; color:#002f45;">
                                            {{ $emoji }} <span style="opacity:0.6;">{{ $group->count() }}</span>
                                        </span>
                                    @empty
                                        <span style="color:#002f45; opacity:0.3; font-size:0.75rem;">Belum ada reaksi</span>
                                    @endforelse
                                </div>

                                {{-- Comment count --}}
                                @if ($foto->comments->count() > 0)
                                    <div style="margin-bottom:0.75rem;">
                                        <span style="color:#002f45; opacity:0.5; font-size:0.8rem;">
                                            💬 {{ $foto->comments->count() }} komentar
                                        </span>
                                    </div>
                                @endif

                                {{-- Uploader info --}}
                                <p style="color:#002f45; opacity:0.35; font-size:0.7rem; margin:0;">
                                    oleh {{ $foto->uploader->name ?? '—' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- BACK LINK --}}
            <div style="margin-top:2.5rem; text-align:center;">
                <a href="{{ route('peserta.index') }}"
                    style="display:inline-flex; align-items:center; gap:0.5rem; color:#002f45; text-decoration:none; font-weight:600; font-size:0.9rem; padding:0.6rem 1.25rem; border-radius:0.75rem; background:rgba(255,255,255,0.3); transition:all 0.3s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.5)'" onmouseout="this.style.background='rgba(255,255,255,0.3)'">
                    ← Kembali ke Portal Peserta
                </a>
            </div>
        </div>
    </div>

    {{-- INSTAGRAM-STYLE SIDE PANEL (Desktop) --}}
    <div id="sidePanel" style="display:none; position:fixed; top:64px; right:0; bottom:0; width:400px; background:rgba(255,255,255,0.95); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border-left:1px solid rgba(0,47,69,0.1); z-index:1000; flex-direction:column; max-width:90vw;">
        <div style="padding:1rem; border-bottom:1px solid rgba(0,47,69,0.08); display:flex; align-items:center; justify-content:space-between;">
            <h4 id="panelTitle" style="color:#002f45; font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin:0;">Komentar</h4>
            <button onclick="tutupPanel()" style="background:none; border:none; color:#002f45; font-size:1.5rem; cursor:pointer; padding:0; opacity:0.6;">&times;</button>
        </div>
        <div id="panelContent" style="flex:1; overflow-y:auto; padding:1rem;">
            {{-- Content will be loaded dynamically --}}
        </div>
    </div>

    {{-- MOBILE BOTTOM SHEET --}}
    <div id="bottomSheet" style="display:none; position:fixed; bottom:0; left:0; right:0; background:rgba(255,255,255,0.98); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border-top-left-radius:1.5rem; border-top-right-radius:1.5rem; z-index:1000; max-height:70vh; flex-direction:column; box-shadow:0 -10px 40px rgba(0,0,0,0.15);">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid rgba(0,47,69,0.08); display:flex; align-items:center; justify-content:space-between;">
            <div style="width:40px; height:4px; background:rgba(0,47,69,0.2); border-radius:9999px; margin:auto;"></div>
            <button onclick="tutupBottomSheet()" style="background:none; border:none; color:#002f45; font-size:1.2rem; cursor:pointer; padding:0; opacity:0.6; position:absolute; right:1rem; top:50%; transform:translateY(-50%);">&times;</button>
        </div>
        <div style="padding:0 1.25rem 0.5rem;">
            <h4 id="bottomSheetTitle" style="color:#002f45; font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin:0;">Komentar Kelompok <span id="bottomSheetKelompok"></span></h4>
        </div>
        <div id="bottomSheetContent" style="flex:1; overflow-y:auto; padding:0 1.25rem 1rem;">
            {{-- Content will be loaded dynamically --}}
        </div>
        {{-- Form komentar di bottom --}}
        @if ($setting->sedangBerjalan())
            <div style="padding:1rem 1.25rem; border-top:1px solid rgba(0,47,69,0.08); background:rgba(255,255,255,0.7);">
                <form id="bottomSheetForm" action="" method="POST" style="display:flex; gap:0.5rem; align-items:center;">
                    @csrf
                    <input type="text" name="comment" placeholder="Tulis komentar..." required maxlength="500"
                        style="flex:1; padding:0.5rem 0.85rem; border-radius:9999px; border:1px solid rgba(0,47,69,0.2); background:rgba(255,255,255,0.9); font-size:0.85rem; outline:none;"
                        onfocus="this.style.borderColor='#002f45'; this.style.boxShadow='0 0 0 2px rgba(0,47,69,0.1)'"
                        onblur="this.style.borderColor='rgba(0,47,69,0.2)'; this.style.boxShadow='none'">
                    <button type="submit"
                        style="display:inline-flex; align-items:center; justify-content:center; background:#002f45; color:#fff; border:none; border-radius:9999px; width:36px; height:36px; font-weight:600; font-size:0.95rem; cursor:pointer; transition:all 0.2s;"
                        onmouseover="this.style.background='#001f2e'; this.style.transform='scale(1.05)'"
                        onmouseout="this.style.background='#002f45'; this.style.transform='scale(1)'">
                        ✈️
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Overlay --}}
    <div id="overlay" onclick="tutupPanel()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999; backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);"></div>

    <script>
        // Data foto untuk komentar (dari server)
        const fotoData = @json($semuaFoto->map(function($f) {
            return [
                'id' => $f->id,
                'comments' => $f->comments->map(function($c) {
                    return [
                        'id' => $c->id,
                        'user' => $c->user->name ?? '—',
                        'comment' => $c->comment,
                        'likes' => $c->likes->count(),
                        'time' => $c->created_at->diffForHumans(),
                    ];
                }),
                'hasLiked' => $likeKomentarSaya->keys()->toArray()
            ];
        })->keyBy('id'));

        // Buka side panel (desktop) atau bottom sheet (mobile)
        document.querySelectorAll('.foto-card').forEach(card => {
            card.addEventListener('click', function() {
                const fotoId = this.dataset.fotoId;
                const isMobile = window.innerWidth <= 768;
                
                if (isMobile) {
                    bukaBottomSheet(fotoId);
                } else {
                    bukaPanel(fotoId);
                }
            });
        });

        function bukaPanel(fotoId) {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('sidePanel').style.display = 'flex';
            renderComments(fotoId, 'panel');
        }

        function bukaBottomSheet(fotoId) {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('bottomSheet').style.display = 'flex';
            document.getElementById('bottomSheetKelompok').textContent = document.querySelector(`[data-foto-id="${fotoId}"]`).dataset.fotoKelompok;
            document.getElementById('bottomSheetForm').action = `/peserta/capture-moment/${fotoId}/comment`;
            renderComments(fotoId, 'bottom');
        }

        function tutupPanel() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('sidePanel').style.display = 'none';
        }

        function tutupBottomSheet() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('bottomSheet').style.display = 'none';
        }

        function renderComments(fotoId, target) {
            const data = fotoData[fotoId];
            if (!data) return;

            const container = target === 'panel' ? document.getElementById('panelContent') : document.getElementById('bottomSheetContent');
            
            if (data.comments.length === 0) {
                container.innerHTML = '<p style="color:#002f45; opacity:0.5; text-align:center; padding:2rem 0;">Belum ada komentar</p>';
                return;
            }

            container.innerHTML = data.comments.map(c => `
                <div style="display:flex; align-items:flex-start; gap:0.5rem; margin-bottom:0.75rem; padding:0.5rem; border-radius:0.5rem; transition:background 0.2s;"
                    onmouseover="this.style.background='rgba(0,47,69,0.04)'" 
                    onmouseout="this.style.background='transparent'">
                    <div style="width:28px; height:28px; flex-shrink:0; border-radius:50%; background:linear-gradient(135deg, #6b705c, #002f45); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.7rem;">
                        ${c.user.charAt(0).toUpperCase()}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.3rem; margin-bottom:0.2rem;">
                            <span style="color:#002f45; font-size:0.85rem; font-weight:600;">${c.user}</span>
                            <span style="color:#002f45; font-size:0.85rem; opacity:0.8; line-height:1.4; word-break:break-word;">${c.comment}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            <small style="color:#002f45; opacity:0.4; font-size:0.68rem;">${c.time}</small>
                            ${c.likes > 0 ? `<span style="color:#002f45; opacity:0.4; font-size:0.68rem;">❤️ ${c.likes}</span>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Tutup dengan ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupPanel();
                tutupBottomSheet();
            }
        });
    </script>

    <style>
        @media (max-width: 768px) {
            #sidePanel { display: none !important; }
            #bottomSheet { display: none !important; }
        }
        @media (min-width: 769px) {
            #bottomSheet { display: none !important; }
        }
    </style>
@endsection