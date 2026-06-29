@extends('layouts.app')

@section('content')
    <div
        style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:1100px; margin:0 auto;">

            <div style="margin-bottom:2rem;">
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.2rem; font-weight:800; margin:0;">
                    📸 Kelola <span style="color:#6b705c; font-style:italic;">Capture Moment</span>
                </h1>
                <p style="color:#002f45; opacity:0.7; margin-top:0.4rem;">
                    Nilai foto tiap kelompok — ranking & poin terhitung otomatis dari total skor.
                </p>
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

            {{-- SETTING PERIODE --}}
            <div
                style="background: rgba(255,255,255,0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.6); border-radius:1.5rem; padding:1.5rem; margin-bottom:2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.05);">
                <h3 style="color:#002f45; font-family:'Playfair Display',serif; margin:0 0 1rem 0;">Atur Periode Quest</h3>
                <form action="{{ route('panitia.capture.settings') }}" method="POST"
                    style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
                    @csrf
                    <div>
                        <label style="display:block; font-size:0.8rem; color:#002f45; opacity:0.7; margin-bottom:4px;">Mulai</label>
                        <input type="datetime-local" name="mulai_at"
                            value="{{ $setting->mulai_at?->format('Y-m-d\TH:i') }}"
                            style="padding:0.5rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.2);">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.8rem; color:#002f45; opacity:0.7; margin-bottom:4px;">Deadline</label>
                        <input type="datetime-local" name="selesai_at"
                            value="{{ $setting->selesai_at?->format('Y-m-d\TH:i') }}"
                            style="padding:0.5rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(0,47,69,0.2);">
                    </div>
                    <button type="submit"
                        style="background:#002f45; color:#fff; padding:0.6rem 1.5rem; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer;">
                        Simpan
                    </button>
                    <span style="font-weight:700; color:{{ $setting->sedangBerjalan() ? '#3f6b3a' : '#ef4444' }};">
                        ● {{ $setting->statusLabel() }}
                    </span>
                </form>
            </div>

            {{-- DAFTAR FOTO PER KELOMPOK --}}
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:1.25rem;">
                @forelse ($foto as $item)
                    <div
                        style="background: rgba(255,255,255,0.35); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06);">
                        <div style="position:relative;">
                            <img src="{{ asset('storage/' . $item->foto_path) }}" alt="Foto Kelompok {{ $item->kelompok }}"
                                style="width:100%; max-height:280px; object-fit:cover; display:block;">
                            <span
                                style="position:absolute; top:10px; left:10px; background:#002f45; color:#fff; font-size:0.7rem; font-weight:800; padding:4px 12px; border-radius:50px;">
                                Kelompok {{ $item->kelompok }}
                            </span>
                            @if ($item->labelJuara())
                                <span
                                    style="position:absolute; top:10px; right:10px; background:#d2c296; color:#002f45; font-size:0.7rem; font-weight:800; padding:4px 12px; border-radius:50px;">
                                    {{ $item->labelJuara() }} · {{ $item->poin }} pts
                                </span>
                            @endif
                        </div>

                        <div style="padding:1.1rem;">
                            <p style="color:#002f45; opacity:0.7; font-size:0.8rem; margin:0 0 0.5rem 0;">
                                Upload oleh: {{ $item->uploader->name ?? '-' }} ·
                                Reaction: {{ $item->reactions->count() }} 🎉
                            </p>

                            @if ($item->caption)
                                <p style="color:#002f45; font-size:0.9rem; margin:0 0 0.75rem 0;">{{ $item->caption }}</p>
                            @endif

                            <form action="{{ route('panitia.capture.nilai', $item->id) }}" method="POST">
                                @csrf
                                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; margin-bottom:0.75rem;">
                                    <div>
                                        <label style="font-size:0.7rem; color:#002f45; opacity:0.7;">Kelengkapan</label>
                                        <input type="number" name="skor_kelengkapan" min="0" max="100" required
                                            value="{{ $item->skor_kelengkapan }}"
                                            style="width:100%; padding:0.45rem; border-radius:0.5rem; border:1px solid rgba(0,47,69,0.2);">
                                    </div>
                                    <div>
                                        <label style="font-size:0.7rem; color:#002f45; opacity:0.7;">Tema</label>
                                        <input type="number" name="skor_tema" min="0" max="100" required
                                            value="{{ $item->skor_tema }}"
                                            style="width:100%; padding:0.45rem; border-radius:0.5rem; border:1px solid rgba(0,47,69,0.2);">
                                    </div>
                                    <div>
                                        <label style="font-size:0.7rem; color:#002f45; opacity:0.7;">Estetika</label>
                                        <input type="number" name="skor_estetika" min="0" max="100" required
                                            value="{{ $item->skor_estetika }}"
                                            style="width:100%; padding:0.45rem; border-radius:0.5rem; border:1px solid rgba(0,47,69,0.2);">
                                    </div>
                                </div>
                                <button type="submit"
                                    style="width:100%; background:#002f45; color:#fff; padding:0.6rem; border:none; border-radius:0.75rem; font-weight:700; cursor:pointer;">
                                    {{ $item->sudahDinilai() ? 'Update Nilai (Total: ' . $item->total_skor . ')' : 'Simpan Nilai' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p style="color:#002f45; opacity:0.6;">Belum ada kelompok yang upload foto.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
