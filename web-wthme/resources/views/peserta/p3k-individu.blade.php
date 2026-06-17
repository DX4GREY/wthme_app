@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1000px; margin:0 auto;">

        {{-- Header --}}
        <div style="margin-bottom:2rem;">
            <a href="{{ route('peserta.p3k') }}"
               style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
                Kembali ke P3K Kelompok
            </a>

            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                🎒 Pengumpulan Barang Individu
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-top:0.5rem; font-weight:500;">
                Kelompok {{ $kelompok }} — barang individu dikumpulkan lewat satu perwakilan per kelompok.
            </p>
        </div>

        @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.3); color:#991b1b; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ⚠️ {{ $errors->first() }}
        </div>
        @endif

        @if($pengumpulan && !$isPerwakilan)
            {{-- ───────────── STATE: SAYA DITITIPKAN KE ORANG LAIN (read-only) ───────────── --}}
            <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:1.5rem; padding:2rem; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom:1.5rem;">
                <p style="color:#002f45; font-weight:700; margin-bottom:0.25rem;">📦 Anda menitipkan barang individu ke <strong>{{ $pengumpulan->perwakilan->name }}</strong></p>
                <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin-bottom:1.5rem;">
                    Total anggota yang tercakup di pengumpulan ini: {{ $pengumpulan->jumlah_anggota }} orang
                    ({{ $pengumpulan->anggota->pluck('peserta.name')->filter()->implode(', ') }}).
                </p>

                <div style="overflow-x:auto; margin-bottom:1.5rem;">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                    <thead>
                        <tr style="background: rgba(0, 47, 69, 0.05);">
                            <th style="padding:0.85rem 1rem; text-align:left; color:#002f45;">Nama Barang</th>
                            <th style="padding:0.85rem; text-align:center; color:#002f45;">Target Pengumpulan</th>
                            <th style="padding:0.85rem; text-align:center; color:#002f45;">Sudah Dibawa</th>
                            <th style="padding:0.85rem; text-align:center; color:#002f45;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($barangsIndividu as $b)
                        @php
                            $dibawa = $pengumpulan->jumlahDibawaUntuk($b->id);
                            $target = $pengumpulan->targetUntuk($b);
                            $lengkap = $dibawa >= $target;
                        @endphp
                        <tr style="border-bottom:1px solid rgba(0,47,69,0.05);">
                            <td style="padding:0.85rem 1rem; color:#002f45; font-weight:700;">{{ $b->nama_barang }}</td>
                            <td style="padding:0.85rem; text-align:center; color:#002f45; opacity:0.7;">{{ $target }} {{ $b->satuan }}</td>
                            <td style="padding:0.85rem; text-align:center; color:#002f45; font-weight:800;">{{ $dibawa }}</td>
                            <td style="padding:0.85rem; text-align:center;">
                                @if($lengkap)
                                    <span style="color:#15803d; font-weight:800;">✓ Lengkap</span>
                                @else
                                    <span style="color:#b45309; font-weight:800;">Kurang</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>

                @if($pengumpulan->is_validated)
                    <div style="background: rgba(34, 197, 94, 0.15); color:#166534; padding:0.85rem 1.25rem; border-radius:0.85rem; font-size:0.85rem; font-weight:700;">
                        🛡️ Pengumpulan ini sudah di-ACC oleh panitia P3K.
                    </div>
                @else
                    <p style="color:#002f45; opacity:0.6; font-size:0.8rem; margin-bottom:1rem;">
                        Salah pilih perwakilan? Anda bisa keluar dan menitipkan ke orang lain, atau buat pengumpulan sendiri.
                    </p>
                    <form method="POST" action="{{ route('peserta.p3k.individu.keluar') }}" onsubmit="return confirm('Keluar dari pengumpulan ini? Anda akan bisa nitip ke orang lain atau mengumpulkan sendiri.')">
                        @csrf
                        <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220, 38, 38, 0.3); padding:0.7rem 1.5rem; border-radius:0.75rem; font-size:0.85rem; font-weight:700; cursor:pointer;">
                            Keluar dari Pengumpulan Ini
                        </button>
                    </form>
                @endif
            </div>

        @else
            {{-- ───────────── STATE: BELUM TERDAFTAR, atau SAYA PERWAKILAN (form edit) ───────────── --}}
            @php
                $locked = $pengumpulan && $pengumpulan->is_validated;
                $jumlahAnggotaAwal = $pengumpulan ? $pengumpulan->jumlah_anggota : 1;
                $idAnggotaSaya = $pengumpulan ? $pengumpulan->anggota->pluck('user_id') : collect();
            @endphp

            @if($locked)
            <div style="background: rgba(34, 197, 94, 0.15); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.85rem; font-weight:700;">
                🛡️ Pengumpulan Anda sudah di-ACC oleh panitia P3K dan terkunci. Hubungi panitia P3K jika ada kesalahan data.
            </div>
            @endif

            <form method="POST" action="{{ route('peserta.p3k.individu.store') }}" enctype="multipart/form-data" id="form-kolektif">
                @csrf
                <fieldset {{ $locked ? 'disabled' : '' }} style="border:none; padding:0; margin:0;">

                {{-- Card: Checklist rekan yang nitip --}}
                <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:1.5rem; padding:1.75rem; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom:1.5rem;">
                    <h3 style="color:#002f45; font-size:1.05rem; font-weight:800; margin:0 0 0.4rem;">👥 Rekan yang Nitip ke Anda</h3>
                    <p style="color:#002f45; opacity:0.6; font-size:0.8rem; margin-bottom:1rem;">
                        Centang nama rekan satu kelompok yang menitipkan barang individunya ke Anda. Anda otomatis terhitung sebagai 1 orang.
                    </p>

                    @if($kandidatChecklist->isEmpty())
                        <p style="color:#002f45; opacity:0.5; font-size:0.85rem; padding:0.75rem 0;">Tidak ada rekan lain yang bisa dipilih saat ini (kelompok Anda hanya 1 orang, atau semua rekan sudah tercakup di pengumpulan lain).</p>
                    @else
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:0.6rem; margin-bottom:1rem;">
                        @foreach($kandidatChecklist as $rekan)
                            @php $dicheck = $idAnggotaSaya->contains($rekan->id); @endphp
                            <label style="display:flex; align-items:center; gap:0.5rem; background:rgba(255,255,255,0.5); padding:0.6rem 0.8rem; border-radius:0.75rem; cursor:pointer; font-size:0.85rem; color:#002f45; font-weight:600; border:1px solid rgba(0,47,69,0.1);">
                                <input type="checkbox" name="anggota_ids[]" value="{{ $rekan->id }}" class="rekan-checkbox" {{ $dicheck ? 'checked' : '' }}>
                                {{ $rekan->name }}
                            </label>
                        @endforeach
                        </div>
                    @endif

                    @if($tercakupDiLain->isNotEmpty())
                    <div style="background: rgba(0,47,69,0.05); padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.75rem; color:#002f45; opacity:0.7; margin-bottom:1rem;">
                        ℹ️ Sudah tercakup di pengumpulan lain (tidak bisa dipilih lagi): {{ $tercakupDiLain->pluck('name')->implode(', ') }}
                    </div>
                    @endif

                    <div style="background: rgba(0, 47, 69, 0.08); padding:0.85rem 1.1rem; border-radius:0.85rem; font-size:0.85rem; color:#002f45; font-weight:700;">
                        Total anggota tercakup (termasuk Anda): <span id="jumlah-anggota-display">{{ $jumlahAnggotaAwal }}</span> orang
                    </div>
                </div>

                {{-- Card: Jumlah dibawa per barang --}}
                <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:1.5rem; padding:1.75rem; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom:1.5rem;">
                    <h3 style="color:#002f45; font-size:1.05rem; font-weight:800; margin:0 0 0.4rem;">🎒 Jumlah yang Dibawa</h3>
                    <p style="color:#002f45; opacity:0.6; font-size:0.8rem; margin-bottom:1rem;">
                        Target dihitung otomatis = kebutuhan per orang × jumlah anggota di atas. Isi jumlah aktual yang akan dibawa.
                    </p>

                    @if($barangsIndividu->isEmpty())
                        <p style="color:#002f45; opacity:0.5; font-size:0.85rem;">Belum ada daftar barang individu.</p>
                    @else
                    <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                        <thead>
                            <tr style="background: rgba(0, 47, 69, 0.05);">
                                <th style="padding:0.85rem 1rem; text-align:left; color:#002f45;">Nama Barang</th>
                                <th style="padding:0.85rem; text-align:center; color:#002f45;">Target</th>
                                <th style="padding:0.85rem; text-align:center; color:#002f45; min-width:130px;">Jumlah Dibawa</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($barangsIndividu as $b)
                            @php
                                $dibawaAwal = $pengumpulan ? $pengumpulan->jumlahDibawaUntuk($b->id) : 0;
                                $targetAwal = $b->jumlah_kebutuhan * $jumlahAnggotaAwal;
                            @endphp
                            <tr style="border-bottom:1px solid rgba(0,47,69,0.05);">
                                <td style="padding:0.85rem 1rem; color:#002f45; font-weight:700;">
                                    {{ $b->nama_barang }}
                                    <div style="opacity:0.5; font-size:0.7rem; font-weight:500;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}/orang</div>
                                </td>
                                <td style="padding:0.85rem; text-align:center; color:#002f45; font-weight:800;">
                                    <span class="target-display" data-per-orang="{{ $b->jumlah_kebutuhan }}">{{ $targetAwal }}</span> {{ $b->satuan }}
                                </td>
                                <td style="padding:0.85rem; text-align:center;">
                                    <input type="number" name="jumlah_dibawa[{{ $b->id }}]" value="{{ $dibawaAwal }}" min="0" max="{{ $targetAwal }}"
                                           data-per-orang="{{ $b->jumlah_kebutuhan }}" class="jumlah-input"
                                           style="width:90px; padding:0.5rem; background:rgba(255,255,255,0.7); border:1px solid rgba(0,47,69,0.2); border-radius:0.5rem; text-align:center; font-weight:700;">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                    @endif
                </div>

                {{-- Card: Foto bukti --}}
                <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:1.5rem; padding:1.75rem; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom:1.5rem;">
                    <h3 style="color:#002f45; font-size:1.05rem; font-weight:800; margin:0 0 0.75rem;">📷 Foto Bukti (opsional)</h3>

                    @if($pengumpulan && $pengumpulan->foto_bukti)
                        <div style="position:relative; display:inline-block; margin-bottom:1rem;">
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($pengumpulan->foto_bukti) }}" target="_blank">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($pengumpulan->foto_bukti) }}" style="width:90px; height:90px; object-fit:cover; border-radius:0.85rem; border:2px solid white;">
                            </a>
                            @if(!$locked)
                            <form action="{{ route('peserta.p3k.individu.hapus-foto') }}" method="POST" style="position:absolute; top:-8px; right:-8px;">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus foto?')" style="background:#ef4444; color:white; border-radius:50%; width:22px; height:22px; border:none; cursor:pointer; font-size:11px;">✕</button>
                            </form>
                            @endif
                        </div>
                        <br>
                    @endif

                    <input type="file" name="foto_bukti" accept="image/*"
                           style="font-size:0.85rem; color:#002f45;">
                </div>

                <button type="submit"
                        style="display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#d2c296; border:none; padding:0.9rem 2rem; border-radius:0.85rem; font-size:0.9rem; font-weight:800; cursor:pointer;">
                    {{ $pengumpulan ? '💾 Update Pengumpulan' : '📝 Simpan & Jadi Perwakilan' }}
                </button>

                </fieldset>
            </form>

            @if($pengumpulan && !$locked)
            <form method="POST" action="{{ route('peserta.p3k.individu.bubarkan') }}"
                  onsubmit="return confirm('Bubarkan pengumpulan ini? Semua rekan yang dititipkan ke Anda akan bebas untuk gabung ke pengumpulan lain.')"
                  style="margin-top:1rem;">
                @csrf @method('DELETE')
                <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220, 38, 38, 0.3); padding:0.7rem 1.5rem; border-radius:0.75rem; font-size:0.85rem; font-weight:700; cursor:pointer;">
                    🗑️ Bubarkan Pengumpulan
                </button>
            </form>
            @endif
        @endif

    </div>
</div>

<script>
(function () {
    function recompute() {
        var checked = document.querySelectorAll('.rekan-checkbox:checked').length;
        var total = checked + 1; // termasuk diri sendiri
        var display = document.getElementById('jumlah-anggota-display');
        if (display) display.innerText = total;

        document.querySelectorAll('.target-display').forEach(function (el) {
            var perOrang = parseInt(el.getAttribute('data-per-orang'), 10) || 0;
            el.innerText = perOrang * total;
        });

        document.querySelectorAll('.jumlah-input').forEach(function (input) {
            var perOrang = parseInt(input.getAttribute('data-per-orang'), 10) || 0;
            var targetBaru = perOrang * total;
            input.max = targetBaru;
            if (parseInt(input.value, 10) > targetBaru) {
                input.value = targetBaru;
            }
        });
    }

    document.querySelectorAll('.rekan-checkbox').forEach(function (cb) {
        cb.addEventListener('change', recompute);
    });

    document.querySelectorAll('.jumlah-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var max = parseInt(input.max, 10);
            if (!isNaN(max) && parseInt(input.value, 10) > max) {
                input.value = max;
            }
        });
    });
})();
</script>
@endsection
