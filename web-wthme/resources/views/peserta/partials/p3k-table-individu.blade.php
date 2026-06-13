{{-- Partial: tabel input barang INDIVIDU P3K (milik peserta sendiri) --}}
@if($data->isEmpty())
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(15px); border-radius:2rem; padding:2.5rem 2rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2); margin-bottom:2rem;">
    <p style="color:#002f45; opacity:0.6; font-weight:600;">Belum ada daftar barang individu.</p>
</div>
@else
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(15px); border-radius:1.5rem; overflow:hidden; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom:2rem;">
    <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
        <thead>
            <tr style="background: rgba(0, 47, 69, 0.05);">
                <th style="padding:1.25rem 1.5rem; text-align:left; color:#002f45;">Nama Barang</th>
                <th style="padding:1.25rem; text-align:center; color:#002f45;">Target Saya</th>
                <th style="padding:1.25rem; text-align:center; color:#002f45;">Status</th>
                <th style="padding:1.25rem; text-align:center; color:#002f45; min-width:140px;">Input Jumlah Dibawa</th>
                <th style="padding:1.25rem; text-align:center; color:#002f45;">Bukti Foto</th>
                <th style="padding:1.25rem; text-align:center; color:#002f45;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $item)
        @php
            $b = $item['barang'];
            $dibawa = $item['jumlah_dibawa'];
            $lengkap = $item['is_lengkap'];
            $fotoUrl = $item['foto_url'];
            $isValidated = $item['is_validated'];
            $isLocked = $isValidated && $lengkap;
            $bgRow = $isLocked ? 'rgba(34, 197, 94, 0.08)' : ($isValidated ? 'rgba(217, 119, 6, 0.08)' : 'transparent');
            $formId = 'ind-form-' . $b->id;
            $fileId = 'ind-file-' . $b->id;
        @endphp
        <tr style="background:{{ $bgRow }}; border-bottom:1px solid rgba(0, 47, 69, 0.05);">

            <td style="padding:1.25rem 1.5rem;">
                <div style="color:#002f45; font-weight:700;">{{ $b->nama_barang }}</div>
                <div style="color:#002f45; opacity:0.5; font-size:0.75rem;">{{ $b->keterangan ?? '-' }}</div>
                @if($item['pengumpulan'] && $item['pengumpulan']->updated_at)
                    <div style="color:#002f45; opacity:0.4; font-size:0.65rem; margin-top:0.4rem;">Update: {{ $item['pengumpulan']->updated_at->format('d/m H:i') }}</div>
                @endif
            </td>

            <td style="padding:1.25rem; text-align:center;">
                <div style="color:#002f45; font-weight:800;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}</div>
                <div style="color:#002f45; opacity:0.4; font-size:0.65rem;">per orang</div>
            </td>

            <td style="padding:1.25rem; text-align:center;">
                <span style="font-weight:800; color:{{ $lengkap ? '#15803d' : '#b45309' }};">
                    {{ $dibawa }}/{{ $b->jumlah_kebutuhan }}
                </span>
            </td>


            <td style="padding:1.25rem; text-align:center;">
                <form method="POST" action="{{ route('peserta.p3k.individu.update', $b->id) }}" enctype="multipart/form-data" id="{{ $formId }}">
                    @csrf @method('PATCH')
                    <input type="number" name="jumlah_dibawa" value="{{ $dibawa }}" min="{{ $isValidated ? $dibawa : 0 }}"
                        {{ $isLocked ? 'disabled' : '' }}
                        style="width:75px; padding:0.4rem; background:{{ $isLocked ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.7)' }}; border:1px solid rgba(0,47,69,0.2); border-radius:0.5rem; text-align:center; font-weight:700; margin-bottom:0.5rem;">
                    <input type="file" name="foto_bukti" id="{{ $fileId }}" accept="image/*" style="display:none;" onchange="document.getElementById('{{ $formId }}').submit();">
                </form>
            </td>

            <td style="padding:1.25rem; text-align:center;">
                @if($fotoUrl)
                    <div style="position:relative; display:inline-block;">
                        <a href="{{ $fotoUrl }}" target="_blank">
                            <img src="{{ $fotoUrl }}" style="width:50px; height:50px; object-fit:cover; border-radius:0.75rem; border:2px solid white;">
                        </a>
                        @if(!$isLocked)
                            <form action="{{ route('peserta.p3k.individu.hapus-foto', $b->id) }}" method="POST" style="position:absolute; top:-8px; right:-8px;">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus foto?')" style="background:#ef4444; color:white; border-radius:50%; width:20px; height:20px; border:none; cursor:pointer; font-size:10px;">✕</button>
                            </form>
                        @endif
                    </div>
                    @if(!$isLocked)
                        <label for="{{ $fileId }}" style="display:block; margin-top:0.5rem; cursor:pointer; font-size:0.7rem; color:#002f45; font-weight:700; opacity:0.6;">Ganti Foto</label>
                    @endif
                @else
                    @if(!$isLocked)
                        <label for="{{ $fileId }}" style="cursor:pointer; display:inline-flex; flex-direction:column; align-items:center; gap:0.25rem; font-size:0.7rem; color:#002f45; background:rgba(255,255,255,0.4); padding:0.5rem 0.75rem; border-radius:0.75rem; border:1px dashed rgba(0,47,69,0.3);">
                            <span>📷 Upload</span>
                        </label>
                    @else
                        <div style="font-size: 1.25rem; opacity: 0.3;">🔒</div>
                    @endif
                @endif
            </td>

            <td style="padding:1.25rem; text-align:center;">
                @if($isLocked)
                    <div style="background: rgba(34, 197, 94, 0.15); color: #16a34a; padding: 0.5rem; border-radius: 0.75rem; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.25rem; border: 1px solid rgba(34, 197, 94, 0.2);">
                        🛡️ Selesai (ACC)
                    </div>
                @else
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        <button type="submit" form="{{ $formId }}" style="background:#002f45; color:#d2c296; border:none; padding:0.5rem; border-radius:0.75rem; font-size:0.75rem; font-weight:700; cursor:pointer;">
                            Simpan
                        </button>
                        @if($isValidated && !$lengkap)
                            <div style="color: #b45309; font-size: 0.65rem; font-weight: 800;">⚠️ ACC Cicilan</div>
                        @endif
                        @if($item['pengumpulan'])
                        <form method="POST" action="{{ route('peserta.p3k.individu.reset', $b->id) }}" onsubmit="return confirm('Reset data barang ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:transparent; color:#dc2626; border:1px solid rgba(220, 38, 38, 0.3); padding:0.3rem; border-radius:0.75rem; font-size:0.7rem; width:100%;">Reset</button>
                        </form>
                        @endif
                    </div>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
