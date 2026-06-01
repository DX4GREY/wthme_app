@extends('layouts.app')

@section('content')
    {{-- Tambahkan CDN Tom Select di dalam section content agar langsung aktif --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <div
        style="min-height: 100vh; background: linear-gradient(135deg, #bdd1d3 0%, #e0decd 100%); padding: 3rem 1.5rem; font-family: 'Segoe UI', sans-serif;">
        <div style="max-width: 900px; margin: 0 auto;">

            <div style="text-align: center; margin-bottom: 2.5rem;">
                <h2
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.8rem; font-weight:800; margin:0;">
                    Quest <span style="color:#6b705c; font-style:italic;">Kekeluargaan</span>
                </h2>
                <p style="color: #002f45; opacity: 0.7;">Abadikan momen kebersamaan berdua bersama rekan angkatanmu. Unggah
                    fotonya dan tandai (*tag*) nama mereka. Poin keaktifan akan otomatis bertambah setelah rekanmu
                    memvalidasi kebersamaan kalian! Kejar keakraban, jalin persaudaraan. ✨</p>
                <a href="{{ url('/dashboard') }}" style="color: #002f45; font-weight: 600; text-decoration: none;">← Kembali
                    ke Dashboard</a>
            </div>

            @if (session('success'))
                <div
                    style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;">
                    {{ session('success') }}</div>
            @endif

            {{-- Row Form & Approval --}}
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">

                {{-- Form Upload --}}
                <div
                    style="background: rgba(255,255,255,0.4); border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(255,255,255,0.6);">
                    <h3 style="color: #002f45; margin-top: 0;">📤 Bagikan Momen Baru</h3>
                    <form action="{{ route('peserta.kekeluargaan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600; color:#002f45;">Siapa rekan
                                yang berfoto bersamamu?</label>

                            <select name="teman_id" id="select-teman" required placeholder="Ketik nama atau NIM rekanmu...">
                                <option value="">Ketik nama atau NIM rekanmu...</option>
                                @foreach ($daftarTeman as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} (NIM. {{ $t->nim }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600; color:#002f45;">Unggah Foto
                                Kalian:</label>
                            <input type="file" name="foto" required style="width:100%;">
                        </div>
                        <button type="submit"
                            style="width:100%; background:#002f45; color:white; padding:0.75rem; border-radius:12px; border:none; font-weight:700; cursor:pointer; transition: 0.3s;"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Kirim Undangan Konfirmasi
                        </button>
                    </form>
                </div>

                {{-- Kotak Masuk Persetujuan Yang Butuh Di-ACC oleh Saya --}}
                <div
                    style="background: rgba(255,255,255,0.4); border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(255,255,255,0.6);">
                    <h3 style="color: #002f45; margin-top: 0;">💝 Konfirmasi Kebersamaan</h3>
                    @forelse($permintaanMasuk as $p)
                        <div
                            style="background:rgba(255,255,255,0.6); padding:1rem; border-radius:15px; margin-bottom:1rem; display:flex; align-items:center; gap:1rem;">
                            <img src="{{ asset('storage/kekeluargaan/' . $p->foto) }}"
                                style="width:70px; height:70px; object-fit:cover; border-radius:10px; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                            <div style="flex:1;">
                                {{-- Perubahan Bahasa Utama Ada di Sini --}}
                                <div style="font-weight:700; color:#002f45; font-size:0.9rem;">Momen bersama:
                                    <b>{{ $p->pengirim->name }}</b></div>
                                <div style="font-size: 0.75rem; color:#6b705c; margin-top: 2px; font-weight:600;">Apakah ini
                                    benar foto kalian?</div>

                                <div style="display:flex; gap:0.5rem; margin-top:0.6rem;">
                                    <form action="{{ route('peserta.kekeluargaan.approve', $p->id) }}" method="POST">
                                        @csrf
                                        <button
                                            style="background:#2e7d32; color:white; border:none; padding:0.3rem 0.8rem; border-radius:8px; font-size:0.75rem; font-weight:700; cursor:pointer; transition: 0.2s;"
                                            onmouseover="this.style.transform='scale(1.05)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                            ✓ Benar, Ini Kami!
                                        </button>
                                    </form>
                                    <form action="{{ route('peserta.kekeluargaan.reject', $p->id) }}" method="POST">
                                        @csrf
                                        <button
                                            style="background:#c62828; color:white; border:none; padding:0.3rem 0.8rem; border-radius:8px; font-size:0.75rem; font-weight:700; cursor:pointer; transition: 0.2s;"
                                            onmouseover="this.style.transform='scale(1.05)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                            ❌ Bukan Saya
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p
                            style="color:#002f45; opacity:0.5; font-size:0.9rem; text-align:center; padding:2.5rem 0; line-height: 1.4;">
                            Belum ada kiriman memori hangat dari rekan yang menandai dirimu nih. Yuk, keliling dan abadikan
                            foto bersama yang lain! 📸
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- AKTIFKAN FITUR SEARCH BOX INTERAKTIF --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#select-teman", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                controlInput: '<input autofocus>',
                render: {
                    control: function(data, escape) {
                        return '<div class="ts-control" style="border-radius:12px; padding:0.6rem; border:1px solid rgba(0,47,69,0.2); background:white; color:#002f45;">' +
                            escape(data.text) + '</div>';
                    },
                    dropdown: function() {
                        return '<div class="ts-dropdown" style="border-radius:12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border:none; margin-top:5px;"></div>';
                    }
                }
            });
        });
    </script>
@endsection
