{{-- Partial: tabel daftar barang P3K (manage) --}}
@if($barangs->isEmpty())
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius:1.5rem; padding:3rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2); margin-bottom:1.5rem;">
    <p style="color:#002f45; font-weight:600; opacity:0.6;">Belum ada barang terdaftar di kategori ini.</p>
</div>
@else
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; margin-bottom:1.5rem;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <thead>
                <tr style="background: rgba(0, 47, 69, 0.05);">
                    <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Nama Barang</th>
                    <th style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Kuantitas</th>
                    <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Keterangan</th>
                    <th style="padding:1.25rem 1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($barangs as $b)
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.3);" id="tr-{{ $b->id }}">
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
                            <button onclick="toggleEdit({{ $b->id }})" style="background: rgba(0, 47, 69, 0.1); color:#002f45; border:none; padding:0.5rem 0.8rem; border-radius:10px; font-size:0.75rem; font-weight:800; cursor:pointer;">
                                ✏️ Edit
                            </button>
                            <form method="POST" action="{{ route('panitia.p3k.manage.destroy', $b->id) }}" onsubmit="return confirm('Hapus barang ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: rgba(239, 68, 68, 0.1); color:#dc2626; border:none; padding:0.5rem 0.8rem; border-radius:10px; cursor:pointer;">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>

                <tr id="edit-row-{{ $b->id }}" style="display:none; background: rgba(255, 255, 255, 0.5);">
                    <td colspan="4" style="padding:1.5rem;">
                        <form method="POST" action="{{ route('panitia.p3k.manage.update', $b->id) }}"
                              style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr 2fr auto; gap:1rem; align-items:end;">
                            @csrf @method('PUT')
                            <div>
                                <input type="text" name="nama_barang" value="{{ $b->nama_barang }}" style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                            </div>
                            <div>
                                <select name="kategori" style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                                    <option value="kelompok" {{ $b->kategori=='kelompok'?'selected':'' }}>Kelompok</option>
                                    <option value="individu" {{ $b->kategori=='individu'?'selected':'' }}>Individu</option>
                                </select>
                            </div>
                            <div>
                                <input type="number" name="jumlah_kebutuhan" value="{{ $b->jumlah_kebutuhan }}" min="1" style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                            </div>
                            <div>
                                <select name="satuan" style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
                                    @foreach(['buah','botol','lembar','pasang','set','sachet','pcs','kotak'] as $s)
                                    <option value="{{ $s }}" {{ $b->satuan==$s?'selected':'' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="text" name="keterangan" value="{{ $b->keterangan }}" style="width:100%; padding:0.6rem; border:1px solid #002f45; border-radius:0.6rem; font-size:0.85rem;">
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
