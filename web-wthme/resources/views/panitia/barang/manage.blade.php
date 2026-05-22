@extends('layouts.app')

@section('content')
{{-- Background Wrapper dengan Gradient --}}
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1000px; margin:0 auto;">

        {{-- Header Section (Glass Card) --}}
        <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem; box-shadow: 0 8px 32px rgba(0, 47, 69, 0.1);">
            <div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.85rem; font-weight:800; margin:0;">
                    Kelola Daftar Barang
                </h1>
                <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem; font-weight:500;">
                    🛠️ Konfigurasi kebutuhan logistik per kelompok — Divisi Logistik
                </p>
            </div>
            <a href="{{ route('panitia.barang.index') }}"
               style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.7rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700; backdrop-filter: blur(5px); transition: 0.3s;">
                ← Kembali ke Rekap
            </a>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); color:#166534; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.2); color:#991b1b; padding:1rem; border-radius:1rem; margin-bottom:1.5rem; font-size:0.9rem;">
            @foreach($errors->all() as $e) <div style="margin-bottom:2px;">⚠️ {{ $e }}</div> @endforeach
        </div>
        @endif

        {{-- Form Tambah Barang (Glass Card) --}}
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2.5rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);">
            <h2 style="color:#002f45; font-size:1.1rem; font-weight:800; margin:0 0 1.5rem; display:flex; align-items:center; gap:0.5rem;">
                <span style="background:#002f45; color:white; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:0.8rem;">+</span>
                Tambah Kebutuhan Barang
            </h2>
            <form method="POST" action="{{ route('panitia.barang.manage.store') }}">
                @csrf
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.5px;">Nama Barang</label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="cth: Senter, P3K..."
                               style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem; outline:none; transition:0.3s;"
                               onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.5px;">Jumlah</label>
                        <input type="number" name="jumlah_kebutuhan" value="{{ old('jumlah_kebutuhan') }}" placeholder="0" min="1"
                               style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem; outline:none; transition:0.3s;"
                               onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                    </div>
                    <div>
                        <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.5px;">Satuan</label>
                        <select name="satuan" style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem; outline:none; appearance:none;">
                            @foreach(['buah','biji','lembar','pasang','set','botol','liter','kg','pcs'] as $s)
                            <option value="{{ $s }}" {{ old('satuan')==$s?'selected':'' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; color:#002f45; font-size:0.75rem; font-weight:700; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.5px;">Keterangan Spesifik</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="cth: Minimal 60 lumen, warna putih..."
                           style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:0.8rem; color:#002f45; font-size:0.95rem; outline:none; transition:0.3s;"
                           onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                </div>
                <button type="submit"
                        style="background:#002f45; color:#d2c296; border:none; padding:0.8rem 2rem; border-radius:1rem; font-size:0.9rem; font-weight:800; cursor:pointer; transition:0.3s; box-shadow: 0 4px 15px rgba(0,47,69,0.2);"
                        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    ➕ Tambahkan Barang
                </button>
            </form>
        </div>

        {{-- Tabel Daftar Barang (Glass Card) --}}
        @if($barangs->isEmpty())
        <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius:1.5rem; padding:4rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2);">
            <div style="font-size:3.5rem; margin-bottom:1rem; opacity:0.5;">📦</div>
            <p style="color:#002f45; font-weight:600; opacity:0.6;">Belum ada barang terdaftar.</p>
        </div>
        @else
        <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);">
            <div style="background: rgba(0, 47, 69, 0.8); padding: 1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between;">
                <span style="color:#d2c296; font-weight:800; font-size:0.95rem; letter-spacing:0.5px;">
                    📋 Daftar Kebutuhan ({{ $barangs->count() }} Item)
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                    <thead>
                        <tr style="background: rgba(0, 47, 69, 0.05);">
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Nama Barang</th>
                            <th style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Kuantitas</th>
                            <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Keterangan</th>
                            <th style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangs as $b)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.3); transition: 0.3s;" id="tr-{{ $b->id }}">
                            <td style="padding:1.25rem 1.5rem;">
                                <span style="color:#002f45; font-weight:700; font-size:1rem;">{{ $b->nama_barang }}</span>
                            </td>
                            <td style="padding:1.25rem 1rem; text-align:center;">
                                <span style="background: rgba(0, 47, 69, 0.1); color:#002f45; font-weight:800; font-size:0.85rem; padding:0.4rem 0.8rem; border-radius:10px;">
                                    {{ $b->jumlah_kebutuhan }} {{ $b->satuan }}
                                </span>
                            </td>
                            <td style="padding:1.25rem 1.5rem;">
                                <span style="color:#002f45; opacity:0.6; font-size:0.85rem;">{{ $b->keterangan ?? '—' }}</span>
                            </td>
                            <td style="padding:1.25rem 1rem; text-align:center;">
                                <div style="display:flex; gap:0.5rem; justify-content:center;">
                                    <button onclick="toggleEdit({{ $b->id }})"
                                            style="background: rgba(0, 47, 69, 0.1); color:#002f45; border:none; padding:0.5rem 0.8rem; border-radius:10px; font-size:0.75rem; font-weight:800; cursor:pointer; transition:0.2s;">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" action="{{ route('panitia.barang.manage.destroy', $b->id) }}"
                                          onsubmit="return confirm('Hapus barang ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background: rgba(239, 68, 68, 0.1); color:#dc2626; border:none; padding:0.5rem 0.8rem; border-radius:10px; cursor:pointer;">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row edit (Glass Overlay Mode) --}}
                        <tr id="edit-row-{{ $b->id }}" style="display:none; background: rgba(255, 255, 255, 0.5);">
                            <td colspan="4" style="padding:1.5rem;">
                                <form method="POST" action="{{ route('panitia.barang.manage.update', $b->id) }}"
                                      style="display:grid; grid-template-columns: 2fr 1fr 1fr 2fr auto; gap:1rem; align-items:end;">
                                    @csrf @method('PUT')
                                    <div>
                                        <input type="text" name="nama_barang" value="{{ $b->nama_barang }}"
                                               style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                                    </div>
                                    <div>
                                        <input type="number" name="jumlah_kebutuhan" value="{{ $b->jumlah_kebutuhan }}" min="1"
                                               style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                                    </div>
                                    <div>
                                        <select name="satuan" style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                                            @foreach(['buah','biji','lembar','pasang','set','botol','liter','kg','pcs'] as $s)
                                            <option value="{{ $s }}" {{ $b->satuan==$s?'selected':'' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text" name="keterangan" value="{{ $b->keterangan }}"
                                               style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                                    </div>
                                    <div style="display:flex; gap:0.4rem;">
                                        <button type="submit" style="background:#002f45; color:#d2c296; border:none; padding:0.6rem 1rem; border-radius:0.6rem; font-size:0.8rem; font-weight:800; cursor:pointer;">💾</button>
                                        <button type="button" onclick="toggleEdit({{ $b->id }})" style="background:white; border:1px solid rgba(0,0,0,0.1); padding:0.6rem; border-radius:0.6rem; cursor:pointer;">✕</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
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