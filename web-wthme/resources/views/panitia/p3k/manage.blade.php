@extends('layouts.app')

@section('content')
<style>
@media (max-width: 640px) {
    .page-wrap { padding: 1.25rem 0.85rem !important; }
    .form-grid { grid-template-columns: 1fr 1fr !important; }
    .form-grid-1 { grid-template-columns: 1fr !important; }
    table { font-size: 0.72rem !important; }
    td, th { padding: 0.4rem 0.5rem !important; }
}
</style>
@php $menuConfig = ['logistik'=>['label'=>'Logistik','icon'=>'🎒'], 'konsumsi'=>['label'=>'Konsumsi','icon'=>'🥘'], 'p3k'=>['label'=>'P3K','icon'=>'🩹']]; @endphp
<div class="page-wrap" style="min-height:calc(100vh - 64px); padding:1.75rem 1rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1100px; margin:0 auto;">

    <div style="background:rgba(255,255,255,0.3); backdrop-filter:blur(15px); border:1px solid rgba(255,255,255,0.4); border-radius:1.5rem; padding:2rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
        <div>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.85rem; font-weight:800; margin:0;">Kelola Barang</h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem;">⚙️ Konfigurasi kebutuhan barang semua menu</p>
        </div>
        <a href="{{ route('panitia.p3k.index') }}" style="text-decoration:none; background:rgba(0,47,69,0.85); color:#d2c296; padding:0.7rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">← Kembali</a>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.2); border:1px solid rgba(34,197,94,0.3); color:#166534; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.2); color:#991b1b; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; font-size:0.9rem;">
        @foreach($errors->all() as $e) <div>⚠️ {{ $e }}</div> @endforeach
    </div>
    @endif

    {{-- Form Tambah --}}
    <div style="background:rgba(255,255,255,0.25); backdrop-filter:blur(15px); border:1px solid rgba(255,255,255,0.4); border-radius:1.5rem; padding:2rem; margin-bottom:2.5rem;">
        <h2 style="color:#002f45; font-size:1.1rem; font-weight:800; margin:0 0 1.5rem;">➕ Tambah Barang</h2>
        <form method="POST" action="{{ route('panitia.p3k.manage.store') }}">
            @csrf
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Nama Barang</label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="cth: Senter" required
                           style="width:100%; padding:0.7rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Menu</label>
                    <select name="menu" style="width:100%; padding:0.7rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.9rem;">
                        <option value="logistik" {{ old('menu')=='logistik'?'selected':'' }}>🎒 Logistik</option>
                        <option value="konsumsi"  {{ old('menu')=='konsumsi' ?'selected':'' }}>🥘 Konsumsi</option>
                        <option value="p3k"       {{ old('menu')=='p3k'      ?'selected':'' }}>🩹 P3K</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Tipe</label>
                    <select name="tipe" style="width:100%; padding:0.7rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.9rem;">
                        <option value="kelompok" {{ old('tipe')=='kelompok'?'selected':'' }}>📦 Kelompok</option>
                        <option value="individu"  {{ old('tipe')=='individu' ?'selected':'' }}>🎒 Individu</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Jumlah</label>
                    <input type="number" name="jumlah_kebutuhan" value="{{ old('jumlah_kebutuhan') }}" placeholder="0" min="1" required
                           style="width:100%; padding:0.7rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Satuan</label>
                    <select name="satuan" style="width:100%; padding:0.7rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.9rem;">
                        @foreach(['buah','botol','lembar','pasang','set','sachet','pcs','kotak','pack','liter','strip','bungkus'] as $s)
                        <option value="{{ $s }}" {{ old('satuan')==$s?'selected':'' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; color:#002f45; font-size:0.72rem; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">Keterangan (opsional)</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="cth: Per kelompok minimal 1 unit"
                       style="width:100%; padding:0.7rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.9rem;">
            </div>
            <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.8rem 2rem; border-radius:1rem; font-size:0.9rem; font-weight:800; cursor:pointer;">➕ Tambahkan</button>
        </form>
    </div>

    {{-- Daftar per menu --}}
    @foreach(['logistik','konsumsi','p3k'] as $menu)
    <div style="margin-bottom:2.5rem;">
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.25rem; font-weight:800; margin-bottom:0.75rem; padding-left:0.25rem;">
            {{ $menuConfig[$menu]['icon'] }} {{ $menuConfig[$menu]['label'] }}
        </h3>

        {{-- Kelompok --}}
        @if($barangsByMenuTipe[$menu]['kelompok']->isNotEmpty())
        <p style="color:#002f45; font-size:0.78rem; font-weight:700; opacity:0.55; margin:0 0 0.4rem 0.25rem; text-transform:uppercase; letter-spacing:0.04em;">📦 Kelompok</p>
        @include('panitia.p3k.partials.manage-table', ['barangs' => $barangsByMenuTipe[$menu]['kelompok']])
        @endif

        {{-- Individu --}}
        @if($barangsByMenuTipe[$menu]['individu']->isNotEmpty())
        <p style="color:#002f45; font-size:0.78rem; font-weight:700; opacity:0.55; margin:1rem 0 0.4rem 0.25rem; text-transform:uppercase; letter-spacing:0.04em;">🎒 Individu</p>
        @include('panitia.p3k.partials.manage-table', ['barangs' => $barangsByMenuTipe[$menu]['individu']])
        @endif

        @if($barangsByMenuTipe[$menu]['kelompok']->isEmpty() && $barangsByMenuTipe[$menu]['individu']->isEmpty())
        <div style="background:rgba(255,255,255,0.2); border-radius:1.25rem; padding:2rem; text-align:center; border:2px dashed rgba(0,47,69,0.15);">
            <p style="color:#002f45; opacity:0.5; font-size:0.85rem;">Belum ada barang {{ $menuConfig[$menu]['label'] }}.</p>
        </div>
        @endif
    </div>
    @endforeach

</div>
</div>

<script>
function toggleEdit(id) {
    const editRow = document.getElementById('edit-row-' + id);
    const isHidden = editRow.style.display === 'none';
    editRow.style.display = isHidden ? 'table-row' : 'none';
}
</script>
@endsection
