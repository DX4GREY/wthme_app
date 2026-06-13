@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1100px; margin:0 auto;">

        <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.85rem; font-weight:800; margin:0;">
                    Kelola P3K
                </h1>
                <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem; font-weight:500;">
                    🛠️ Konfigurasi barang kebutuhan & pembagian PJ per kelompok
                </p>
            </div>
            <a href="{{ route('panitia.p3k.index') }}" style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.7rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">
                ← Kembali
            </a>
        </div>

        @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); color:#166534; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.2); color:#991b1b; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; font-size:0.9rem;">
            @foreach($errors->all() as $e) <div>⚠️ {{ $e }}</div> @endforeach
        </div>
        @endif

        {{-- Form Tambah Barang --}}
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2.5rem;">
            <h2 style="color:#002f45; font-size:1.1rem; font-weight:800; margin:0 0 1.5rem;">➕ Tambah Kebutuhan Barang P3K</h2>
            <form method="POST" action="{{ route('panitia.p3k.manage.store') }}">
                @csrf
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">Nama Barang</label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="cth: Minyak Kayu Putih"
                               style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">Kategori</label>
                        <select name="kategori" style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                            <option value="kelompok">Kelompok</option>
                            <option value="individu">Individu</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">Jumlah</label>
                        <input type="number" name="jumlah_kebutuhan" value="{{ old('jumlah_kebutuhan') }}" placeholder="0" min="1"
                               style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">Satuan</label>
                        <select name="satuan" style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                            @foreach(['buah','botol','lembar','pasang','set','sachet','pcs','kotak'] as $s)
                            <option value="{{ $s }}" {{ old('satuan')==$s?'selected':'' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">Keterangan Spesifik</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="cth: Per kelompok minimal 1 botol"
                           style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                </div>
                <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.8rem 2rem; border-radius:1rem; font-size:0.9rem; font-weight:800; cursor:pointer;">
                    ➕ Tambahkan Barang
                </button>
            </form>
        </div>

        {{-- Mapping PJ per Kelompok
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2.5rem;">
            <h2 style="color:#002f45; font-size:1.1rem; font-weight:800; margin:0 0 1.5rem;">🛡️ Pembagian PJ P3K per Kelompok</h2>
            <form method="POST" action="{{ route('panitia.p3k.manage.pj.store') }}" style="display:grid; grid-template-columns:1fr 2fr auto; gap:1.25rem; align-items:end; margin-bottom:1.5rem;">
                @csrf
                <div>
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">Kelompok</label>
                    <input type="text" name="kelompok" placeholder="cth: 1" required
                           style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase;">PJ P3K</label>
                    <select name="pj_p3k_id" required style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem;">
                        <option value="">-- Pilih Panitia P3K --</option>
                        @foreach($panitiaP3k as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.8rem 1.5rem; border-radius:1rem; font-size:0.9rem; font-weight:800; cursor:pointer;">
                    Simpan
                </button>
            </form>

            @if($pjKelompok->isEmpty())
                <p style="color:#002f45; opacity:0.5; font-size:0.85rem;">Belum ada pembagian PJ.</p>
            @else
            <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                @foreach($pjKelompok as $row)
                <div style="background: rgba(0,47,69,0.08); border-radius:0.75rem; padding:0.6rem 1rem; font-size:0.8rem; color:#002f45; font-weight:700;">
                    Kelompok {{ $row->kelompok }} → {{ $row->pj->name ?? '-' }}
                </div>
                @endforeach
            </div>
            @endif
            <p style="color:#002f45; opacity:0.4; font-size:0.75rem; margin-top:1rem;">
                Tips: dari contoh data, biasanya kelompok 1-5 → PJ A, 6-10 → PJ B, 11-15 → PJ C. Input satu per satu sesuai pembagian.
            </p>
        </div> --}}

        {{-- Daftar Barang Kelompok --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">📦 Barang Kelompok</h3>
        @include('panitia.p3k.partials.manage-table', ['barangs' => $barangsKelompok])

        {{-- Daftar Barang Individu --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin:2rem 0 1rem; padding-left:0.5rem;">🎒 Barang Individu</h3>
        @include('panitia.p3k.partials.manage-table', ['barangs' => $barangsIndividu])

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
