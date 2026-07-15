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
                                style="width:100%; height:160px; border-radius:1rem; object-fit:cover; box-shadow:0 4px 15px rgba(0,0,0,0.12);">
                        </div>
                        <div style="flex:1; min-width:180px;">
                            @if ($milikKelompok->caption)
                                <p style="color:#002f45; margin:0 0 0.5rem 0; font-size:0.9rem;">{{ $milikKelompok->caption }}</p>
                            @endif
                            <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin:0;">
                                Diupload oleh: <strong>{{ $milikKelompok->uploader->name ?? '-' }}</strong>
                            </p>
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
                                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.35rem;">Caption <span style="opacity:0.4;">(opsional)</span></label>
                                <textarea name="caption" placeholder="Tulis cerita singkat tentang foto ini..." maxlength="255" rows="2"
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
                            <div style="position:relative;">
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
@endsection