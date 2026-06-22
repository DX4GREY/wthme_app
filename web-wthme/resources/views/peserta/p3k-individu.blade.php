@extends('layouts.app')

@section('content')
<style>
@media (max-width: 640px) {
    .page-wrap { padding: 1.25rem 0.85rem !important; }
    table { font-size: 0.78rem !important; }
    td, th { padding: 0.5rem 0.65rem !important; }
    .rekan-grid { grid-template-columns: repeat(2,1fr) !important; }
    .btn-row { flex-wrap: wrap !important; }
}
</style>
<div class="page-wrap" style="min-height:calc(100vh - 64px); padding:1.75rem 1rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1000px; margin:0 auto;">

    <a href="{{ route('peserta.p3k') }}" style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Barang Bawaan
    </a>

    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0 0 0.4rem;">{{ $menuLabel }} — Individu</h1>
    <p style="color:#002f45; opacity:0.55; font-size:0.9rem; margin:0 0 1.75rem;">Kelompok {{ $kelompok }} · Pengumpulan kolektif lewat perwakilan</p>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.2); border:1px solid rgba(34,197,94,0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.3); color:#991b1b; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">⚠️ {{ $errors->first() }}</div>
    @endif

    @if($pengumpulan && !$isPerwakilan)
    {{-- ══ DITITIPKAN KE ORANG LAIN (read-only) ══ --}}
    <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; padding:2rem; border:1px solid rgba(255,255,255,0.4); margin-bottom:1.5rem;">
        <p style="color:#002f45; font-weight:700; margin-bottom:0.25rem;">📦 Barang {{ $menuLabel }} Anda dititipkan ke <strong>{{ $pengumpulan->perwakilan->name }}</strong></p>
        <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin-bottom:1.5rem;">Total {{ $pengumpulan->jumlah_anggota }} orang tercakup dalam pengumpulan ini.</p>

        <table style="width:100%; border-collapse:collapse; font-size:0.85rem; margin-bottom:1.5rem;">
            <thead><tr style="background:rgba(0,47,69,0.05);">
                <th style="padding:0.75rem 1rem; text-align:left; color:#002f45;">Barang</th>
                <th style="padding:0.75rem; text-align:center; color:#002f45;">Target</th>
                <th style="padding:0.75rem; text-align:center; color:#002f45;">Dibawa</th>
                <th style="padding:0.75rem; text-align:center; color:#002f45;">Status</th>
            </tr></thead>
            <tbody>
            @foreach($barangsIndividu as $b)
            @php $dibawa=$pengumpulan->jumlahDibawaUntuk($b->id); $target=$pengumpulan->targetUntuk($b); @endphp
            <tr style="border-bottom:1px solid rgba(0,47,69,0.05);">
                <td style="padding:0.75rem 1rem; color:#002f45; font-weight:700;">{{ $b->nama_barang }}</td>
                <td style="padding:0.75rem; text-align:center; color:#002f45; opacity:0.65;">{{ $target }} {{ $b->satuan }}</td>
                <td style="padding:0.75rem; text-align:center; color:#002f45; font-weight:800;">{{ $dibawa }}</td>
                <td style="padding:0.75rem; text-align:center;">
                    @if($dibawa>=$target)<span style="color:#15803d; font-weight:800;">✓</span>@else<span style="color:#b45309;">Kurang</span>@endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>

        @if(!$pengumpulan->is_validated)
        <form method="POST" action="{{ route('peserta.p3k.individu.keluar', $menu) }}" onsubmit="return confirm('Keluar dari pengumpulan {{ $menuLabel }} ini?')">
            @csrf
            <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220,38,38,0.3); padding:0.65rem 1.4rem; border-radius:0.75rem; font-size:0.85rem; font-weight:700; cursor:pointer;">
                Keluar dari Pengumpulan Ini
            </button>
        </form>
        @else
        <div style="background:rgba(34,197,94,0.12); color:#166534; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.82rem; font-weight:700;">🛡️ Sudah di-ACC panitia.</div>
        @endif
    </div>

    @else
    {{-- ══ FORM (Perwakilan / Belum terdaftar) ══ --}}
    @php $locked = $pengumpulan && $pengumpulan->is_validated; $jumlahAwalAnggota = $pengumpulan ? $pengumpulan->jumlah_anggota : 1; $idAnggotaSaya = $pengumpulan ? $pengumpulan->anggota->pluck('user_id') : collect(); @endphp

    @if($locked)
    <div style="background:rgba(34,197,94,0.15); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.85rem; font-weight:700;">🛡️ Pengumpulan Anda sudah di-ACC dan terkunci. Hubungi panitia jika ada kesalahan.</div>
    @endif

    <form method="POST" action="{{ route('peserta.p3k.individu.store', $menu) }}" enctype="multipart/form-data">
        @csrf
        <fieldset {{ $locked ? 'disabled' : '' }} style="border:none; padding:0; margin:0;">

        {{-- Card: Checklist rekan --}}
        <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; padding:1.75rem; border:1px solid rgba(255,255,255,0.4); margin-bottom:1.5rem;">
            <h3 style="color:#002f45; font-size:1rem; font-weight:800; margin:0 0 0.4rem;">👥 Rekan yang kolektif ke Anda</h3>
            <p style="color:#002f45; opacity:0.55; font-size:0.78rem; margin-bottom:1rem;">Centang nama rekan yang menitipkan barang individu {{ $menuLabel }} ke Anda. Anda otomatis terhitung 1 orang.</p>

            @if($kandidatChecklist->isEmpty())
            <p style="color:#002f45; opacity:0.5; font-size:0.82rem;">Tidak ada rekan yang bisa dipilih saat ini.</p>
            @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:0.5rem; margin-bottom:0.75rem;">
            @foreach($kandidatChecklist as $rekan)
            @php $checked = $idAnggotaSaya->contains($rekan->id); @endphp
            <label style="display:flex; align-items:center; gap:0.5rem; background:rgba(255,255,255,0.5); padding:0.55rem 0.75rem; border-radius:0.7rem; cursor:pointer; font-size:0.83rem; color:#002f45; font-weight:600; border:1px solid rgba(0,47,69,0.08);">
                <input type="checkbox" name="anggota_ids[]" value="{{ $rekan->id }}" class="rekan-checkbox" {{ $checked ? 'checked' : '' }}>
                {{ $rekan->name }}
            </label>
            @endforeach
            </div>
            @endif

            @if($tercakupDiLain->isNotEmpty())
            <div style="background:rgba(0,47,69,0.05); padding:0.65rem 0.9rem; border-radius:0.7rem; font-size:0.73rem; color:#002f45; opacity:0.65; margin-bottom:0.75rem;">
                ℹ️ Sudah di pengumpulan lain (tidak bisa dipilih): {{ $tercakupDiLain->pluck('name')->implode(', ') }}
            </div>
            @endif

            <div style="background:rgba(0,47,69,0.07); padding:0.75rem 1rem; border-radius:0.8rem; font-size:0.85rem; color:#002f45; font-weight:700;">
                Total anggota (termasuk Anda): <span id="jumlah-anggota-display">{{ $jumlahAwalAnggota }}</span> orang
            </div>
        </div>

        {{-- Card: Jumlah dibawa --}}
        @if($barangsIndividu->isNotEmpty())
        <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; padding:1.75rem; border:1px solid rgba(255,255,255,0.4); margin-bottom:1.5rem;">
            <h3 style="color:#002f45; font-size:1rem; font-weight:800; margin:0 0 0.4rem;">🎒 Jumlah yang Dibawa</h3>
            <p style="color:#002f45; opacity:0.55; font-size:0.78rem; margin-bottom:1rem;">Target = kebutuhan/orang × jumlah anggota. Isi jumlah aktual.</p>
            <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead><tr style="background:rgba(0,47,69,0.04);">
                    <th style="padding:0.75rem 1rem; text-align:left; color:#002f45;">Barang</th>
                    <th style="padding:0.75rem; text-align:center; color:#002f45;">Target</th>
                    <th style="padding:0.75rem; text-align:center; color:#002f45; min-width:120px;">Dibawa</th>
                </tr></thead>
                <tbody>
                @foreach($barangsIndividu as $b)
                @php $dibawaAwal = $pengumpulan ? $pengumpulan->jumlahDibawaUntuk($b->id) : 0; $targetAwal = $b->jumlah_kebutuhan * $jumlahAwalAnggota; @endphp
                <tr style="border-bottom:1px solid rgba(0,47,69,0.05);">
                    <td style="padding:0.75rem 1rem; color:#002f45; font-weight:700;">
                        {{ $b->nama_barang }}<div style="opacity:0.45; font-size:0.68rem;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}/orang</div>
                    </td>
                    <td style="padding:0.75rem; text-align:center; font-weight:800; color:#002f45;">
                        <span class="target-display" data-per-orang="{{ $b->jumlah_kebutuhan }}">{{ $targetAwal }}</span> {{ $b->satuan }}
                    </td>
                    <td style="padding:0.75rem; text-align:center;">
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

        {{-- Card: Foto --}}
        <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; padding:1.75rem; border:1px solid rgba(255,255,255,0.4); margin-bottom:1.5rem;">
            <h3 style="color:#002f45; font-size:1rem; font-weight:800; margin:0 0 0.75rem;">📷 Foto Bukti (opsional)</h3>
            @if($pengumpulan && $pengumpulan->foto_bukti)
            <div style="position:relative; display:inline-block; margin-bottom:1rem;">
                <a href="{{ \Illuminate\Support\Facades\Storage::url($pengumpulan->foto_bukti) }}" target="_blank">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($pengumpulan->foto_bukti) }}" style="width:90px; height:90px; object-fit:cover; border-radius:0.85rem; border:2px solid white;">
                </a>
                @if(!$locked)
                <form action="{{ route('peserta.p3k.individu.hapus-foto', $menu) }}" method="POST" style="position:absolute; top:-8px; right:-8px;">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Hapus foto?')" style="background:#ef4444; color:white; border-radius:50%; width:22px; height:22px; border:none; cursor:pointer; font-size:11px;">✕</button>
                </form>
                @endif
            </div><br>
            @endif
            <input type="file" name="foto_bukti" accept="image/*" style="font-size:0.85rem; color:#002f45;">
        </div>

        <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.9rem 2rem; border-radius:0.85rem; font-size:0.9rem; font-weight:800; cursor:pointer;">
            {{ $pengumpulan ? '💾 Update Pengumpulan' : '📝 Simpan & Jadi Perwakilan' }}
        </button>
        </fieldset>
    </form>

    @if($pengumpulan && !$locked)
    <form method="POST" action="{{ route('peserta.p3k.individu.bubarkan', $menu) }}"
          onsubmit="return confirm('Bubarkan pengumpulan {{ $menuLabel }}? Semua yang dititipkan akan bebas.')"
          style="margin-top:1rem;">
        @csrf @method('DELETE')
        <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220,38,38,0.3); padding:0.65rem 1.4rem; border-radius:0.75rem; font-size:0.85rem; font-weight:700; cursor:pointer;">
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
        var total = document.querySelectorAll('.rekan-checkbox:checked').length + 1;
        document.getElementById('jumlah-anggota-display').innerText = total;
        document.querySelectorAll('.target-display').forEach(function(el) {
            el.innerText = parseInt(el.getAttribute('data-per-orang'),10) * total;
        });
        document.querySelectorAll('.jumlah-input').forEach(function(input) {
            var max = parseInt(input.getAttribute('data-per-orang'),10) * total;
            input.max = max;
            if (parseInt(input.value,10) > max) input.value = max;
        });
    }
    document.querySelectorAll('.rekan-checkbox').forEach(function(cb){ cb.addEventListener('change', recompute); });
    document.querySelectorAll('.jumlah-input').forEach(function(input){
        input.addEventListener('input', function(){
            if (parseInt(input.value,10) > parseInt(input.max,10)) input.value = input.max;
        });
    });
})();
</script>
@endsection
