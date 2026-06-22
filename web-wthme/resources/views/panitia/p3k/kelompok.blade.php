@extends('layouts.app')

@section('content')
<style>
@media (max-width: 640px) {
    .page-wrap { padding: 1.25rem 0.85rem !important; }
    .header-flex { flex-direction: column !important; align-items: flex-start !important; }
    .tab-row { gap: 0.4rem !important; }
    .tab-btn { padding: 0.45rem 0.85rem !important; font-size: 0.75rem !important; }
    .stok-mini-grid { grid-template-columns: 1fr 1fr !important; }
    table { font-size: 0.72rem !important; }
    td, th { padding: 0.45rem 0.6rem !important; }
    .anggota-grid { grid-template-columns: repeat(2,1fr) !important; }
}
</style>
<div class="page-wrap" style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1200px; margin:0 auto;">

    @php
        $menuConfig = ['logistik'=>['label'=>'Logistik','icon'=>'🎒'], 'konsumsi'=>['label'=>'Konsumsi','icon'=>'🥘'], 'p3k'=>['label'=>'P3K','icon'=>'🩹']];
        $canEdit = auth()->user()->role==='admin' || strtoupper(auth()->user()->divisi??'')!=='';
        $canAcc  = auth()->user()->role==='admin' || strtoupper(auth()->user()->divisi??'')!=='';
    @endphp

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <div>
            <a href="{{ route('panitia.p3k.index') }}" style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.85rem; font-weight:600; display:inline-flex; align-items:center; gap:0.4rem; margin-bottom:0.75rem;">← Kembali</a>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">Kelompok {{ $kelompok }}</h1>
            <p style="color:#002f45; opacity:0.55; font-size:0.9rem; margin-top:0.3rem;">{{ $anggota->count() }} peserta terdaftar</p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.2); border:1px solid rgba(34,197,94,0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.3); color:#991b1b; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">⚠️ {{ $errors->first() }}</div>
    @endif

    {{-- Tabs --}}
    <div style="display:flex; gap:0.75rem; margin-bottom:1.75rem; flex-wrap:wrap;" id="menu-tabs">
        @foreach($menus as $menu)
        <button onclick="showTab('{{ $menu }}')" id="tab-{{ $menu }}"
            style="padding:0.65rem 1.4rem; border-radius:999px; border:2px solid rgba(0,47,69,0.2); background:rgba(255,255,255,0.3); color:#002f45; font-size:0.85rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:0.5rem;">
            {{ $menuConfig[$menu]['icon'] }} {{ $menuConfig[$menu]['label'] }}
        </button>
        @endforeach
        <button onclick="showTab('obat')" id="tab-obat"
            style="padding:0.65rem 1.4rem; border-radius:999px; border:2px solid rgba(0,47,69,0.2); background:rgba(255,255,255,0.3); color:#002f45; font-size:0.85rem; font-weight:700; cursor:pointer;">
            💊 Obat Pribadi
        </button>
    </div>

    {{-- Panes per menu --}}
    @foreach($menus as $menu)
    <div id="pane-{{ $menu }}" class="tab-pane" style="display:none;">

        {{-- Barang Kelompok --}}
        @if($dataKelompokByMenu[$menu]->isNotEmpty())
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.15rem; font-weight:800; margin:0 0 0.75rem; padding-left:0.25rem;">📦 Barang Kelompok</h3>
        @include('panitia.p3k.partials.validasi-table', ['data' => $dataKelompokByMenu[$menu], 'kelompok' => $kelompok, 'withTerpakai' => true])
        @endif

        {{-- Barang Individu --}}
        @if($barangsIndividuByMenu[$menu]->isNotEmpty())
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.15rem; font-weight:800; margin:2rem 0 0.75rem; padding-left:0.25rem;">🎒 Barang Individu</h3>
        @include('panitia.p3k.partials.validasi-table-individu', [
            'pengumpulanKolektif'   => $pengumpulanByMenu[$menu],
            'anggotaBelumTercakup' => $anggotaBelumTercakupByMenu[$menu],
            'barangsIndividu'      => $barangsIndividuByMenu[$menu],
            'summaryIndividuKelompok' => $summaryIndividuByMenu[$menu],
            'kelompok'             => $kelompok,
        ])
        @endif

    </div>
    @endforeach

    {{-- Obat Pribadi pane --}}
    <div id="pane-obat" class="tab-pane" style="display:none;">
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.15rem; font-weight:800; margin:0 0 0.75rem; padding-left:0.25rem;">💊 Obat Pribadi</h3>
        @include('panitia.p3k.partials.obat-pribadi', ['obatPribadi' => $obatPribadi, 'kelompok' => $kelompok])
    </div>

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
    var btn = document.getElementById('tab-' + active);
    if (btn) { btn.style.background='#002f45'; btn.style.borderColor='#002f45'; btn.style.color='#d2c296'; }
}
document.addEventListener('DOMContentLoaded', function(){ showTab('{{ $menus[0] }}'); });
</script>
@endsection
