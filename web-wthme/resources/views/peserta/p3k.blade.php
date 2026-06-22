@extends('layouts.app')

@section('content')
<style>
@media (max-width: 640px) {
    .page-wrap { padding: 1.25rem 0.85rem !important; }
    .page-header { flex-direction: column !important; align-items: flex-start !important; gap: 0.5rem !important; }
    .tab-row { gap: 0.4rem !important; overflow-x: auto; padding-bottom: 0.25rem; flex-wrap: nowrap !important; }
    .tab-btn { padding: 0.45rem 0.85rem !important; font-size: 0.76rem !important; white-space: nowrap; }
    table { font-size: 0.78rem !important; }
    td, th { padding: 0.5rem 0.75rem !important; }
    .individu-status { flex-direction: column !important; }
}
</style>
<div class="page-wrap" style="min-height:calc(100vh - 64px); padding:1.75rem 1rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1100px; margin:0 auto;">

    @php
        $menuConfig = [
            'logistik' => ['label'=>'Logistik','icon'=>'🎒','desc'=>'Perlengkapan & alat kemah'],
            'konsumsi'  => ['label'=>'Konsumsi', 'icon'=>'🥘','desc'=>'Makanan & minuman'],
            'p3k'       => ['label'=>'P3K',      'icon'=>'🩹','desc'=>'Kesehatan & obat-obatan'],
        ];
    @endphp

    {{-- Header --}}
    <div style="background:rgba(255,255,255,0.3); backdrop-filter:blur(15px); border:1px solid rgba(255,255,255,0.4); border-radius:1.5rem; padding:1.75rem 2rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
            <a href="{{ route('peserta.index') }}" style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.85rem; font-weight:600; display:inline-flex; align-items:center; gap:0.4rem; margin-bottom:0.75rem;">← Kembali</a>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.85rem; font-weight:800; margin:0;">Barang Bawaan</h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.3rem;">Kelompok {{ $kelompok }}@if($pj) &nbsp;·&nbsp; PJ: {{ $pj->name }}@endif</p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.2); border:1px solid rgba(34,197,94,0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.3); color:#991b1b; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">⚠️ {{ $errors->first() }}</div>
    @endif

    {{-- Tab menu --}}
    <div class="tab-row" style="display:flex; gap:0.75rem; margin-bottom:1.75rem; flex-wrap:wrap;" id="menu-tabs">
        @foreach($menus as $menu)
        <button onclick="showTab('{{ $menu }}')" id="tab-{{ $menu }}"
            style="padding:0.65rem 1.4rem; border-radius:999px; border:2px solid rgba(0,47,69,0.2); background:rgba(255,255,255,0.3); color:#002f45; font-size:0.85rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:0.5rem;">
            {{ $menuConfig[$menu]['icon'] }} {{ $menuConfig[$menu]['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Tab panes --}}
    @foreach($menus as $menu)
    <div id="pane-{{ $menu }}" class="tab-pane" style="display:none;">

        {{-- ═══ BARANG KELOMPOK ═══ --}}
        @if($dataKelompokByMenu[$menu]->isNotEmpty())
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.1rem; font-weight:800; margin:0 0 0.75rem; padding-left:0.5rem;">📦 Barang Kelompok</h3>
        @include('peserta.partials.p3k-table-kelompok', ['data' => $dataKelompokByMenu[$menu]])
        @endif

        {{-- ═══ BARANG INDIVIDU STATUS ═══ --}}
        @if($dataIndividuByMenu[$menu]->isNotEmpty())
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.1rem; font-weight:800; margin:1.5rem 0 0.5rem; padding-left:0.5rem;">🎒 Barang Individu — {{ ucfirst($menu) }}</h3>
        <p style="color:#002f45; opacity:0.5; font-size:0.78rem; margin:-0.3rem 0 1rem 0.5rem;">Dikumpulkan lewat perwakilan. Anda bisa jadi perwakilan atau dititipkan ke teman satu kelompok.</p>

        @php
            $pengumpulan = $pengumpulanSayaByMenu[$menu] ?? null;
            $isPerwakilan = $isPerwakilanSayaByMenu[$menu] ?? false;
            $dataIndividu = $dataIndividuByMenu[$menu];
        @endphp

        @if(!$pengumpulan)
        {{-- Belum terdaftar --}}
        <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; padding:2rem; text-align:center; border:2px dashed rgba(0,47,69,0.25); margin-bottom:2rem;">
            <div style="font-size:2rem; margin-bottom:0.5rem;">{{ $menuConfig[$menu]['icon'] }}</div>
            <p style="color:#002f45; font-weight:700; margin-bottom:0.4rem;">Anda belum terdaftar di pengumpulan {{ $menuConfig[$menu]['label'] }}.</p>
            <a href="{{ route('peserta.p3k.individu', $menu) }}" style="display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#d2c296; text-decoration:none; padding:0.75rem 1.5rem; border-radius:0.85rem; font-size:0.85rem; font-weight:800; margin-top:0.75rem;">
                📝 Buka Form Pengumpulan {{ $menuConfig[$menu]['label'] }}
            </a>
        </div>
        @else
        {{-- Sudah terdaftar --}}
        <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; overflow:hidden; border:1px solid rgba(255,255,255,0.4); margin-bottom:2rem;">
            <div style="padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; border-bottom:1px solid rgba(0,47,69,0.06);">
                <span style="background:rgba(0,47,69,0.1); color:#002f45; font-size:0.75rem; font-weight:800; padding:0.35rem 0.75rem; border-radius:999px;">
                    {{ $isPerwakilan ? '🧑‍🤝‍🧑 Anda Perwakilan (' . $pengumpulan->jumlah_anggota . ' orang)' : '📦 Dititipkan ke ' . $pengumpulan->perwakilan->name }}
                </span>
                <span style="background:{{ $pengumpulan->is_validated ? 'rgba(34,197,94,0.15)' : 'rgba(245,158,11,0.15)' }}; color:{{ $pengumpulan->is_validated ? '#166534' : '#92400e' }}; font-size:0.75rem; font-weight:800; padding:0.35rem 0.75rem; border-radius:999px;">
                    {{ $pengumpulan->is_validated ? '🛡️ Sudah di-ACC' : 'Menunggu ACC' }}
                </span>
            </div>
            <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead><tr style="background:rgba(0,47,69,0.04);">
                    <th style="padding:0.7rem 1.25rem; text-align:left; color:#002f45;">Barang</th>
                    <th style="padding:0.7rem; text-align:center; color:#002f45;">Target</th>
                    <th style="padding:0.7rem; text-align:center; color:#002f45;">Dibawa</th>
                    <th style="padding:0.7rem; text-align:center; color:#002f45;">Status</th>
                </tr></thead>
                <tbody>
                @foreach($dataIndividu as $item)
                <tr style="border-bottom:1px solid rgba(0,47,69,0.04);">
                    <td style="padding:0.75rem 1.25rem; color:#002f45; font-weight:700;">{{ $item['barang']->nama_barang }}</td>
                    <td style="padding:0.75rem; text-align:center; color:#002f45; opacity:0.6;">{{ $item['target'] }} {{ $item['barang']->satuan }}</td>
                    <td style="padding:0.75rem; text-align:center; color:#002f45; font-weight:800;">{{ $item['jumlah_dibawa'] }}</td>
                    <td style="padding:0.75rem; text-align:center;">
                        @if($item['is_lengkap'])<span style="color:#15803d; font-weight:800;">✓ Lengkap</span>
                        @else<span style="color:#b45309; font-weight:800;">Kurang</span>@endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            <div style="padding:1rem 1.5rem; border-top:1px solid rgba(0,47,69,0.05);">
                <a href="{{ route('peserta.p3k.individu', $menu) }}" style="display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#d2c296; text-decoration:none; padding:0.65rem 1.4rem; border-radius:0.75rem; font-size:0.82rem; font-weight:800;">
                    {{ $isPerwakilan ? '⚙️ Kelola Pengumpulan' : '🔍 Lihat Detail' }}
                </a>
            </div>
        </div>
        @endif
        @endif

        {{-- Obat Pribadi — hanya di menu P3K --}}
        @if($menu === 'p3k')
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.1rem; font-weight:800; margin:1.5rem 0 0.75rem; padding-left:0.5rem;">💊 Obat Pribadi</h3>

        {{-- Form tambah --}}
        <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border-radius:1.5rem; padding:1.5rem; border:1px solid rgba(255,255,255,0.4); margin-bottom:1rem;">
            <form method="POST" action="{{ route('peserta.p3k.obat.store') }}" enctype="multipart/form-data">
                @csrf
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.3rem; text-transform:uppercase;">Penyakit / Kondisi*</label>
                        <input type="text" name="penyakit" placeholder="cth: Asma, Alergi debu" required
                               style="width:100%; padding:0.6rem 0.85rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.15); border-radius:0.7rem; color:#002f45; font-size:0.85rem;">
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.3rem; text-transform:uppercase;">Nama Obat (opsional)</label>
                        <input type="text" name="nama_obat" placeholder="cth: Ventolin Inhaler"
                               style="width:100%; padding:0.6rem 0.85rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.15); border-radius:0.7rem; color:#002f45; font-size:0.85rem;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.3rem; text-transform:uppercase;">Catatan Penggunaan (opsional)</label>
                        <input type="text" name="catatan" placeholder="cth: Diminum saat kambuh"
                               style="width:100%; padding:0.6rem 0.85rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.15); border-radius:0.7rem; color:#002f45; font-size:0.85rem;">
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.3rem; text-transform:uppercase;">Foto Obat (opsional)</label>
                        <input type="file" name="foto_bukti" accept="image/*" style="font-size:0.8rem; color:#002f45; padding-top:0.4rem;">
                    </div>
                </div>
                <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.65rem 1.5rem; border-radius:0.75rem; font-size:0.85rem; font-weight:800; cursor:pointer;">
                    ➕ Lapor Obat Pribadi ke P3K
                </button>
            </form>
        </div>

        {{-- Daftar obat --}}
        @if($obatPribadiSaya->isEmpty())
        <div style="background:rgba(255,255,255,0.2); border-radius:1.25rem; padding:1.5rem; text-align:center; border:2px dashed rgba(0,47,69,0.15); margin-bottom:1.5rem;">
            <p style="color:#002f45; opacity:0.5; font-size:0.85rem; font-weight:600;">Belum ada obat pribadi yang dilaporkan.</p>
        </div>
        @else
        <div style="display:flex; flex-direction:column; gap:0.6rem; margin-bottom:1.5rem;">
            @foreach($obatPribadiSaya as $o)
            <div style="background:rgba(255,255,255,0.3); backdrop-filter:blur(10px); border-radius:1rem; padding:0.9rem 1.25rem; border:1px solid {{ $o->sudah_diserahkan ? 'rgba(34,197,94,0.3)' : 'rgba(0,47,69,0.1)' }}; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                @if($o->foto_bukti)
                <a href="{{ \Illuminate\Support\Facades\Storage::url($o->foto_bukti) }}" target="_blank" style="flex-shrink:0;">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($o->foto_bukti) }}" style="width:44px; height:44px; object-fit:cover; border-radius:0.6rem; border:2px solid white;">
                </a>
                @endif
                <div style="flex:1;">
                    <div style="color:#002f45; font-weight:800; font-size:0.9rem;">{{ $o->penyakit }}</div>
                    @if($o->nama_obat)<div style="color:#002f45; opacity:0.65; font-size:0.8rem;">{{ $o->nama_obat }}</div>@endif
                    @if($o->catatan)<div style="color:#002f45; opacity:0.45; font-size:0.75rem; font-style:italic;">"{{ $o->catatan }}"</div>@endif
                </div>
                <div style="display:flex; align-items:center; gap:0.6rem; flex-shrink:0;">
                    <span style="background:{{ $o->sudah_diserahkan ? 'rgba(34,197,94,0.15)' : 'rgba(245,158,11,0.15)' }}; color:{{ $o->sudah_diserahkan ? '#166534' : '#92400e' }}; font-size:0.7rem; font-weight:800; padding:0.3rem 0.7rem; border-radius:999px;">
                        {{ $o->sudah_diserahkan ? '✓ Diterima P3K' : 'Belum diterima' }}
                    </span>
                    @if(!$o->sudah_diserahkan)
                    <form method="POST" action="{{ route('peserta.p3k.obat.destroy', $o->id) }}" onsubmit="return confirm('Hapus data obat ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:rgba(239,68,68,0.1); color:#dc2626; border:none; padding:0.3rem 0.5rem; border-radius:0.5rem; font-size:0.75rem; cursor:pointer;">🗑</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif

    </div>
    @endforeach

</div>
</div>

<script>
function showTab(active) {
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    document.getElementById('pane-' + active).style.display = 'block';
    document.querySelectorAll('#menu-tabs button').forEach(b => {
        b.style.background = 'rgba(255,255,255,0.3)';
        b.style.borderColor = 'rgba(0,47,69,0.2)';
        b.style.color = '#002f45';
    });
    var activeBtn = document.getElementById('tab-' + active);
    activeBtn.style.background = '#002f45';
    activeBtn.style.borderColor = '#002f45';
    activeBtn.style.color = '#d2c296';
}
document.addEventListener('DOMContentLoaded', function() { showTab('{{ $menus[0] }}'); });
</script>
@endsection
