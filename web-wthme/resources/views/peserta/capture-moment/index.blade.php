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
                        <div style="background:rgba(255,255,255,0.35); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06); transition:transform 0.3s ease;"
                            onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                            {{-- Thumbnail --}}
                            <div style="position:relative; cursor:pointer;"
                                onclick="bukaPreview('{{ asset('storage/' . $foto->foto_path) }}', '{{ addslashes($foto->caption ?? '') }}')">
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
                                </div>
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

                                {{-- Form react --}}
                                @if ($setting->sedangBerjalan())
                                    <form action="{{ route('peserta.capture.react', $foto->id) }}" method="POST" style="display:flex; gap:4px; flex-wrap:wrap;">
                                        @csrf
                                        @php $myEmoji = $reaksiSaya[$foto->id] ?? null; @endphp
                                        @foreach (['❤️', '🔥', '😂', '😍', '👏', '🎉'] as $opt)
                                            <button type="submit" name="emoji" value="{{ $opt }}"
                                                style="font-size:1rem; background:{{ $myEmoji === $opt ? '#002f45' : 'rgba(255,255,255,0.6)' }}; border:1px solid {{ $myEmoji === $opt ? '#002f45' : 'rgba(0,47,69,0.12)' }}; border-radius:50px; padding:4px 10px; cursor:pointer; transition:all 0.2s;"
                                                onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
                                                @if ($myEmoji === $opt) title="Reaksi kamu" @endif>
                                                {{ $opt }}
                                            </button>
                                        @endforeach
                                    </form>
                                @endif

                                {{-- Uploader info --}}
                                <p style="color:#002f45; opacity:0.35; font-size:0.7rem; margin-top:0.6rem; margin-bottom:0;">
                                    oleh {{ $foto->uploader->name ?? '—' }}
                                </p>
                            </div>

                            {{-- Comments Section (Instagram style) --}}
                            @if ($setting->sedangBerjalan())
                                {{-- Form komentar --}}
                                <div style="margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid rgba(0,47,69,0.08);">
                                    <form action="{{ route('peserta.capture.comment.store', $foto->id) }}" method="POST" style="display:flex; gap:0.4rem; align-items:center;">
                                        @csrf
                                        <input type="text" name="comment" placeholder="Tulis komentar..." required maxlength="500"
                                            style="flex:1; padding:0.45rem 0.75rem; border-radius:9999px; border:1px solid rgba(0,47,69,0.2); background:rgba(255,255,255,0.85); font-size:0.8rem; outline:none;"
                                            onfocus="this.style.borderColor='#002f45'; this.style.boxShadow='0 0 0 2px rgba(0,47,69,0.1)'"
                                            onblur="this.style.borderColor='rgba(0,47,69,0.2)'; this.style.boxShadow='none'">
                                        <button type="submit"
                                            style="display:inline-flex; align-items:center; justify-content:center; background:#002f45; color:#fff; border:none; border-radius:9999px; width:32px; height:32px; font-weight:600; font-size:0.9rem; cursor:pointer; transition:all 0.2s;"
                                            onmouseover="this.style.background='#001f2e'; this.style.transform='scale(1.05)'"
                                            onmouseout="this.style.background='#002f45'; this.style.transform='scale(1)'">
                                            ✈️
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- Tombol Show/Hide Comments --}}
                            @if ($foto->comments->count() > 0)
                                <div style="margin-top:0.5rem;">
                                    <button type="button" onclick="toggleComments({{ $foto->id }})"
                                        style="background:none; border:none; color:#002f45; font-size:0.8rem; font-weight:600; cursor:pointer; padding:0.2rem 0; display:flex; align-items:center; gap:0.2rem; transition:all 0.2s;"
                                        onmouseover="this.style.opacity='0.7'"
                                        onmouseout="this.style.opacity='1'">
                                        <span id="toggle-text-{{ $foto->id }}">Show {{ $foto->comments->count() }} comments</span>
                                        <span id="toggle-icon-{{ $foto->id }}">▼</span>
                                    </button>
                                </div>
                            @endif

                            {{-- Daftar komentar (default hidden) --}}
                            @if ($foto->comments->count() > 0)
                                <div id="comments-container-{{ $foto->id }}" style="display:none; margin-top:0.4rem; max-height:180px; overflow-y:auto; padding-right:0.25rem;">
                                    @foreach ($foto->comments as $comment)
                                        <div style="display:flex; align-items:flex-start; gap:0.5rem; margin-bottom:0.4rem; padding:0.4rem 0.5rem; border-radius:0.5rem; transition:background 0.2s;"
                                            onmouseover="this.style.background='rgba(0,47,69,0.04)'"
                                            onmouseout="this.style.background='transparent'">
                                            {{-- Avatar inisial --}}
                                            <div style="width:28px; height:28px; flex-shrink:0; border-radius:50%; background:linear-gradient(135deg, #6b705c, #002f45); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.75rem; margin-top:0.1rem;">
                                                {{ strtoupper(substr($comment->user->name ?? '—', 0, 1)) }}
                                            </div>
                                            <div style="flex:1; min-width:0;">
                                                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.3rem; margin-bottom:0.2rem;">
                                                    <span style="color:#002f45; font-size:0.82rem; font-weight:600; line-height:1;">{{ $comment->user->name ?? '—' }}</span>
                                                    <span style="color:#002f45; font-size:0.82rem; opacity:0.8; line-height:1.4; word-break:break-word;">{{ $comment->comment }}</span>
                                                </div>
                                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                                    <small style="color:#002f45; opacity:0.4; font-size:0.68rem;">{{ $comment->created_at->diffForHumans() }}</small>
                                                    @if ($comment->likes->count() > 0)
                                                        <span style="color:#002f45; opacity:0.4; font-size:0.68rem;">❤️ {{ $comment->likes->count() }}</span>
                                                    @endif
                                                    @if ($setting->sedangBerjalan())
                                                        <form action="{{ route('peserta.capture.comment.like', $comment->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @php $hasLiked = isset($likeKomentarSaya[$comment->id]); @endphp
                                                            <button type="submit"
                                                                style="background:none; border:none; color:{{ $hasLiked ? '#ef4444' : '#002f45' }}; opacity:{{ $hasLiked ? '1' : '0.4' }}; font-size:0.9rem; cursor:pointer; padding:0; display:flex; align-items:center; gap:0.15rem; transition:all 0.2s;"
                                                                onmouseover="this.style.opacity='0.7'"
                                                                onmouseout="this.style.opacity='{{ $hasLiked ? '1' : '0.4' }}'">
                                                                ❤️
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- Tombol hapus komentar (hanya untuk pemilik) --}}
                                            @if ($comment->user_id === auth()->id())
                                                <form action="{{ route('peserta.capture.comment.destroy', $comment->id) }}" method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Hapus komentar ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        style="background:none; border:none; color:#b91c1c; opacity:0.4; font-size:0.9rem; cursor:pointer; padding:0.1rem; margin-left:0.25rem; transition:all 0.2s;"
                                                        onmouseover="this.style.opacity='0.7'"
                                                        onmouseout="this.style.opacity='0.4'">
                                                        ✕
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

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

        function toggleComments(fotoId) {
            const container = document.getElementById('comments-container-' + fotoId);
            const toggleText = document.getElementById('toggle-text-' + fotoId);
            const toggleIcon = document.getElementById('toggle-icon-' + fotoId);
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                toggleText.textContent = 'Hide comments';
                toggleIcon.textContent = '▲';
            } else {
                container.style.display = 'none';
                toggleText.textContent = 'Show ' + container.children.length + ' comments';
                toggleIcon.textContent = '▼';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupPreview();
        });
    </script>
@endsection
