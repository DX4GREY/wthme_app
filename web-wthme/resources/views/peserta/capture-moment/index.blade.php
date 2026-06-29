@extends('layouts.app')

@section('content')
    <div
        style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:900px; margin:0 auto;">

            {{-- Header --}}
            <div style="margin-bottom:2rem; text-align: center;">
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.2rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    📸 Quest <span style="color:#6b705c; font-style:italic;">Capture Moment</span>
                </h1>
                <p style="color:#002f45; opacity:0.7; margin-top:0.5rem;">
                    Abadikan momen kebersamaan kelompok kamu — Tema: <b>Kekeluargaan</b>
                </p>

                <div
                    style="display:inline-block; margin-top:0.75rem; padding:0.4rem 1.25rem; border-radius:2rem; font-size:0.85rem; font-weight:700;
                    background: {{ $setting->sedangBerjalan() ? 'rgba(107,160,99,0.15)' : 'rgba(239,68,68,0.12)' }};
                    color: {{ $setting->sedangBerjalan() ? '#3f6b3a' : '#ef4444' }};">
                    {{ $setting->statusLabel() }}
                    @if ($setting->selesai_at)
                        — Deadline: {{ $setting->selesai_at->translatedFormat('d M Y, H:i') }}
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div style="background:#e6f4ea; color:#1e7e34; padding:0.9rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem; font-weight:600;">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div style="background:#fde8e8; color:#c0392b; padding:0.9rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem; font-weight:600;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORM UPLOAD / GANTI FOTO KELOMPOK SENDIRI --}}
            <div
                style="background: rgba(255,255,255,0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.6); border-radius:1.5rem; padding:1.75rem; margin-bottom:2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.05);">
                <h3 style="color:#002f45; font-family:'Playfair Display',serif; margin:0 0 1rem 0;">
                    Foto Kelompok {{ auth()->user()->kelompok }}
                </h3>

                @if ($milikKelompok)
                    <div style="display:flex; gap:1.25rem; flex-wrap:wrap; align-items:flex-start; margin-bottom:1.25rem;">
                        <img src="{{ asset('storage/' . $milikKelompok->foto_path) }}" alt="Foto Kelompok"
                            style="width:220px; border-radius:1rem; object-fit:cover; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                        <div style="flex:1; min-width:200px;">
                            @if ($milikKelompok->caption)
                                <p style="color:#002f45; margin:0 0 0.75rem 0;">{{ $milikKelompok->caption }}</p>
                            @endif

                            @if ($milikKelompok->sudahDinilai())
                                <div style="display:inline-block; padding:0.3rem 1rem; border-radius:2rem; background:#002f45; color:#fff; font-weight:700; font-size:0.85rem;">
                                    {{ $milikKelompok->labelJuara() ?? 'Sudah dinilai' }} — {{ $milikKelompok->total_skor }} poin nilai
                                </div>
                            @else
                                <div style="display:inline-block; padding:0.3rem 1rem; border-radius:2rem; background:rgba(0,47,69,0.1); color:#002f45; font-weight:700; font-size:0.85rem;">
                                    Menunggu dinilai panitia
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <p style="color:#002f45; opacity:0.6; margin-bottom:1rem;">Kelompok kamu belum upload foto. Yuk upload sebelum deadline!</p>
                @endif

                @if ($setting->sedangBerjalan())
                    <form action="{{ route('peserta.capture.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="foto" accept="image/*" required
                            style="display:block; margin-bottom:0.75rem; width:100%;">
                        <textarea name="caption" placeholder="Caption (opsional)" maxlength="255" rows="2"
                            style="width:100%; padding:0.6rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.2); margin-bottom:0.75rem; font-family:'Inter',sans-serif;">{{ old('caption', $milikKelompok->caption ?? '') }}</textarea>
                        <button type="submit"
                            style="background:#002f45; color:#fff; padding:0.7rem 1.5rem; border:none; border-radius:1rem; font-weight:700; cursor:pointer;">
                            {{ $milikKelompok ? '🔄 Ganti Foto' : '📤 Upload Foto' }}
                        </button>
                    </form>
                @else
                    <p style="color:#ef4444; font-weight:600;">Periode upload sudah ditutup / belum dibuka.</p>
                @endif
            </div>

            {{-- GALERI SEMUA KELOMPOK + REACTION --}}
            <h3 style="color:#002f45; font-family:'Playfair Display',serif; margin-bottom:1rem;">
                Galeri Capture Moment Semua Kelompok
            </h3>

            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap:1.25rem;">
                @forelse ($semuaFoto as $foto)
                    <div
                        style="background: rgba(255,255,255,0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06);">
                        <div style="position:relative;">
                            <img src="{{ asset('storage/' . $foto->foto_path) }}" alt="Foto Kelompok {{ $foto->kelompok }}"
                                style="width:100%; max-height:320px; object-fit:cover; display:block;">

                            <span
                                style="position:absolute; top:10px; left:10px; background:#002f45; color:#fff; font-size:0.7rem; font-weight:800; padding:4px 12px; border-radius:50px;">
                                Kelompok {{ $foto->kelompok }}
                            </span>

                            @if ($foto->labelJuara())
                                <span
                                    style="position:absolute; top:10px; right:10px; background:#d2c296; color:#002f45; font-size:0.7rem; font-weight:800; padding:4px 12px; border-radius:50px;">
                                    {{ $foto->labelJuara() }}
                                </span>
                            @endif
                        </div>

                        <div style="padding:1rem;">
                            @if ($foto->caption)
                                <p style="color:#002f45; font-size:0.9rem; margin:0 0 0.75rem 0;">{{ $foto->caption }}</p>
                            @endif

                            {{-- Reaction summary --}}
                            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:0.75rem;">
                                @foreach ($foto->reactions->groupBy('emoji') as $emoji => $group)
                                    <span style="background:rgba(0,47,69,0.08); padding:3px 10px; border-radius:50px; font-size:0.85rem;">
                                        {{ $emoji }} {{ $group->count() }}
                                    </span>
                                @endforeach
                            </div>

                            {{-- Form react --}}
                            @if ($setting->sedangBerjalan())
                                <form action="{{ route('peserta.capture.react', $foto->id) }}" method="POST"
                                    style="display:flex; gap:6px; flex-wrap:wrap;">
                                    @csrf
                                    @php $myEmoji = $reaksiSaya[$foto->id] ?? null; @endphp
                                    @foreach (['❤️', '🔥', '😂', '😍', '👏', '🎉'] as $opt)
                                        <button type="submit" name="emoji" value="{{ $opt }}"
                                            style="font-size:1.1rem; background:{{ $myEmoji === $opt ? '#002f45' : 'rgba(255,255,255,0.5)' }}; border:1px solid rgba(0,47,69,0.15); border-radius:50px; padding:4px 10px; cursor:pointer;">
                                            {{ $opt }}
                                        </button>
                                    @endforeach
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p style="color:#002f45; opacity:0.6;">Belum ada kelompok yang upload foto.</p>
                @endforelse
            </div>

            <div style="margin-top:2rem;">
                <a href="{{ route('peserta.index') }}"
                    style="display:inline-flex; align-items:center; gap:0.5rem; color:#002f45; text-decoration:none; font-weight:600;">
                    ← Kembali ke Portal Peserta
                </a>
            </div>
        </div>
    </div>
@endsection
