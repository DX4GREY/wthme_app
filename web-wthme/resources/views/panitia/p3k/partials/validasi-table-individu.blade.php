@php
    $totalCell = 0;
    $lengkapCell = 0;
    foreach ($dataIndividu as $row) {
        foreach ($row['items'] as $it) {
            $totalCell++;
            if ($it['is_lengkap']) $lengkapCell++;
        }
    }
    $persen = $totalCell > 0 ? round(($lengkapCell / $totalCell) * 100) : 0;
    $canEdit = auth()->user()->role === 'admin' || strtoupper(auth()->user()->divisi ?? '') === 'P3K';
@endphp

{{-- Progress keseluruhan --}}
<div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.25rem 2rem; margin-bottom: 1.5rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
    <div style="text-align: center;">
        <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em;">Progress</div>
        <div style="color:#d2c296; font-size:1.75rem; font-weight:800; line-height:1;">{{ $persen }}%</div>
    </div>
    <div style="flex:1; min-width:200px;">
        <div style="background: rgba(255,255,255,0.1); border-radius:999px; height:10px; overflow:hidden;">
            <div style="background: linear-gradient(90deg, #d2c296, #bdd1d3); height:100%; border-radius:999px; width:{{ $persen }}%;"></div>
        </div>
        <div style="color:#bdd1d3; font-size:0.75rem; margin-top:0.4rem;">{{ $lengkapCell }} dari {{ $totalCell }} (peserta × barang) lengkap</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     STOK PER BARANG (per kelompok ini)
     ══════════════════════════════════════════════════════ --}}
@if($summaryIndividuKelompok->isNotEmpty())
<div style="margin-bottom:2rem;">
    <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.75rem;">
        <span style="font-size:1rem; font-weight:800; color:#002f45; font-family:'Playfair Display',serif;">📦 Stok Barang Individu</span>
        <span style="font-size:0.68rem; color:#002f45; opacity:0.4; font-weight:600;">Kelompok {{ $kelompok }}</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
        @foreach($summaryIndividuKelompok as $s)
        @php
            $b = $s['barang'];
            $terkumpul  = $s['total_terkumpul'];
            $terpakai   = $s['total_terpakai'];
            $sisa       = $s['total_sisa'];
            $pctTerpakai = $terkumpul > 0 ? round(($terpakai / $terkumpul) * 100) : 0;
            $pctSisa     = $terkumpul > 0 ? round(($sisa / $terkumpul) * 100) : 0;
            $sisaColor   = $sisa <= 0 ? '#dc2626' : ($sisa <= ($terkumpul * 0.25) ? '#d97706' : '#16a34a');
        @endphp

        <div style="background:rgba(255,255,255,0.35); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden;">

            {{-- Header barang --}}
            <div style="background:rgba(0,47,69,0.06); padding:0.85rem 1.25rem; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(0,47,69,0.07);">
                <span style="font-weight:800; color:#002f45; font-size:0.9rem;">{{ $b->nama_barang }}</span>
                <span style="font-size:0.65rem; color:#002f45; opacity:0.4; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">{{ $b->satuan }}</span>
            </div>

            <div style="padding:1rem 1.25rem;">

                {{-- 3 angka utama --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; margin-bottom:1rem;">

                    {{-- Terkumpul --}}
                    <div style="background:rgba(0,47,69,0.05); border-radius:0.75rem; padding:0.6rem 0.5rem; text-align:center;">
                        <div style="font-size:0.6rem; font-weight:700; color:#002f45; opacity:0.45; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">Terkumpul</div>
                        <div style="font-size:1.5rem; font-weight:800; color:#002f45; line-height:1;">{{ $terkumpul }}</div>
                    </div>

                    {{-- Terpakai --}}
                    <div style="background:rgba(217,119,6,0.08); border-radius:0.75rem; padding:0.6rem 0.5rem; text-align:center; border:1px solid rgba(217,119,6,0.15);">
                        <div style="font-size:0.6rem; font-weight:700; color:#92400e; opacity:0.7; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">Terpakai</div>
                        <div style="font-size:1.5rem; font-weight:800; color:#d97706; line-height:1;">{{ $terpakai }}</div>
                    </div>

                    {{-- Sisa --}}
                    <div style="background:{{ $sisa <= 0 ? 'rgba(220,38,38,0.08)' : 'rgba(22,163,74,0.08)' }}; border-radius:0.75rem; padding:0.6rem 0.5rem; text-align:center; border:1px solid {{ $sisa <= 0 ? 'rgba(220,38,38,0.2)' : 'rgba(22,163,74,0.15)' }};">
                        <div style="font-size:0.6rem; font-weight:700; color:{{ $sisaColor }}; opacity:0.7; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">Sisa</div>
                        <div style="font-size:1.5rem; font-weight:800; color:{{ $sisaColor }}; line-height:1;">{{ $sisa }}</div>
                    </div>
                </div>

                Progress bar terpakai
                {{-- <div style="margin-bottom:{{ $canEdit ? '1rem' : '0' }};">
                    <div style="display:flex; justify-content:space-between; font-size:0.62rem; color:#002f45; opacity:0.45; margin-bottom:0.3rem;">
                        <span>{{ $pctTerpakai }}% terpakai</span>
                        <span>{{ $pctSisa }}% tersisa</span>
                    </div>
                    <div style="background:rgba(0,47,69,0.08); border-radius:999px; height:7px; overflow:hidden; display:flex;">
                       
                        @if($pctTerpakai > 0)
                        <div style="background:#d97706; width:{{ $pctTerpakai }}%; height:100%; border-radius:{{ $pctSisa > 0 ? '999px 0 0 999px' : '999px' }};"></div>
                        @endif
                     
                        @if($pctSisa > 0)
                        <div style="background:{{ $sisaColor }}; width:{{ $pctSisa }}%; height:100%; border-radius:{{ $pctTerpakai > 0 ? '0 999px 999px 0' : '999px' }};"></div>
                        @endif
                    </div>
                </div> --}}

                {{-- Kontrol — hanya admin & divisi P3K --}}
                @if($canEdit)
                <div style="border-top:1px solid rgba(0,47,69,0.08); padding-top:0.9rem;">

                    {{-- Label kontrol --}}
                    <div style="font-size:0.65rem; font-weight:700; color:#002f45; opacity:0.4; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.6rem;">
                        Catat Pemakaian
                    </div>

                    {{-- Tombol +1 / -1 --}}
                    <div style="display:flex; gap:0.5rem; margin-bottom:0.6rem;">

                        {{-- Tambah terpakai (+1) --}}
                        <form action="{{ route('panitia.p3k.stok.adjust', [$b->id, $kelompok]) }}" method="POST" style="flex:1;">
                            @csrf
                            <input type="hidden" name="delta" value="1">
                            <button type="submit"
                                {{ $sisa <= 0 ? 'disabled' : '' }}
                                title="{{ $sisa <= 0 ? 'Stok sudah habis' : 'Tandai 1 unit dipakai' }}"
                                style="width:100%; padding:0.55rem 0.5rem; border-radius:0.7rem; font-size:0.78rem; font-weight:800; cursor:{{ $sisa <= 0 ? 'not-allowed' : 'pointer' }}; border:none; display:flex; align-items:center; justify-content:center; gap:0.3rem;
                                    background:{{ $sisa <= 0 ? 'rgba(0,47,69,0.04)' : 'rgba(217,119,6,0.12)' }};
                                    color:{{ $sisa <= 0 ? '#002f45' : '#92400e' }};
                                    opacity:{{ $sisa <= 0 ? '0.35' : '1' }};
                                    border:1px solid {{ $sisa <= 0 ? 'rgba(0,47,69,0.1)' : 'rgba(217,119,6,0.25)' }};">
                                ▼ Pakai 1
                            </button>
                        </form>

                        {{-- Kurangi terpakai (-1 / batalkan) --}}
                        <form action="{{ route('panitia.p3k.stok.adjust', [$b->id, $kelompok]) }}" method="POST" style="flex:1;">
                            @csrf
                            <input type="hidden" name="delta" value="-1">
                            <button type="submit"
                                {{ $terpakai <= 0 ? 'disabled' : '' }}
                                title="{{ $terpakai <= 0 ? 'Belum ada yang terpakai' : 'Batalkan 1 unit pemakaian' }}"
                                style="width:100%; padding:0.55rem 0.5rem; border-radius:0.7rem; font-size:0.78rem; font-weight:700; cursor:{{ $terpakai <= 0 ? 'not-allowed' : 'pointer' }}; display:flex; align-items:center; justify-content:center; gap:0.3rem;
                                    background:rgba(0,47,69,0.05);
                                    color:#002f45;
                                    opacity:{{ $terpakai <= 0 ? '0.3' : '0.7' }};
                                    border:1px solid rgba(0,47,69,0.12);">
                                ▲ Batalkan 1
                            </button>
                        </form>
                    </div>

                    {{-- Set manual --}}
                    <form action="{{ route('panitia.p3k.stok.terpakai', [$b->id, $kelompok]) }}" method="POST">
                        @csrf
                        <div style="display:flex; align-items:center; gap:0.5rem; background:rgba(0,47,69,0.04); border:1px solid rgba(0,47,69,0.1); border-radius:0.75rem; padding:0.45rem 0.75rem;">
                            <label style="font-size:0.68rem; color:#002f45; opacity:0.5; font-weight:600; white-space:nowrap; flex:1;">
                                Set jumlah terpakai:
                            </label>
                            <input type="number" name="total_terpakai"
                                value="{{ $terpakai }}" min="0" max="{{ $terkumpul }}"
                                style="width:56px; padding:0.3rem 0.4rem; border:1px solid rgba(0,47,69,0.18); border-radius:0.5rem; text-align:center; font-size:0.8rem; font-weight:700; background:white; color:#002f45; flex-shrink:0;">
                            <button type="submit"
                                style="background:#002f45; color:white; border:none; padding:0.3rem 0.75rem; border-radius:0.5rem; font-size:0.7rem; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                                Simpan
                            </button>
                        </div>
                        <div style="font-size:0.6rem; color:#002f45; opacity:0.35; margin-top:0.3rem; padding-left:0.25rem;">
                            Maks. {{ $terkumpul }} {{ $b->satuan }} (sesuai yang terkumpul di kelompok ini)
                        </div>
                    </form>
                </div>
                @else
                {{-- View-only untuk non-P3K --}}
                <div style="border-top:1px solid rgba(0,47,69,0.08); padding-top:0.7rem; font-size:0.7rem; color:#002f45; opacity:0.45; text-align:center;">
                    Hanya divisi P3K yang dapat mengubah data ini
                </div>
                @endif

            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     MATRIX PER PESERTA
     ══════════════════════════════════════════════════════ --}}
@if($dataIndividu->isEmpty() || $barangsIndividu->isEmpty())
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius:1.5rem; padding:2.5rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2); margin-bottom:1.5rem;">
    <p style="color:#002f45; opacity:0.6; font-weight:600;">Belum ada peserta atau daftar barang individu.</p>
</div>
@else
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07); margin-bottom:1.5rem;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
            <thead>
                <tr style="background: rgba(0, 47, 69, 0.05); border-bottom: 2px solid rgba(0,47,69,0.1);">
                    <th style="padding:0.85rem 1.25rem; text-align:left; color:#002f45; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; position:sticky; left:0; background:rgba(224,222,205,0.97); z-index:10; min-width:180px;">
                        Nama Peserta
                    </th>
                    @foreach($barangsIndividu as $b)
                    <th style="padding:0.85rem 0.5rem; text-align:center; color:#002f45; min-width:110px; border-left:1px solid rgba(0,47,69,0.06);">
                        <div style="font-size:0.75rem; font-weight:800;">{{ $b->nama_barang }}</div>
                        <div style="opacity:0.45; font-weight:600; font-size:0.62rem; text-transform:uppercase; letter-spacing:0.04em; margin-top:0.15rem;">
                            Target {{ $b->jumlah_kebutuhan }} {{ $b->satuan }}
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($dataIndividu as $row)
            @php $peserta = $row['peserta']; @endphp
            <tr style="border-bottom: 1px solid rgba(0,47,69,0.05);">
                <td style="padding:0.9rem 1.25rem; color:#002f45; font-weight:700; font-size:0.8rem; position:sticky; left:0; background:rgba(255,255,255,0.92); z-index:5; white-space:nowrap;">
                    {{ $peserta->name }}
                </td>
                @foreach($row['items'] as $item)
                @php
                    $b = $item['barang'];
                    $dibawa = $item['jumlah_dibawa'];
                    $lengkap = $item['is_lengkap'];
                    $isValidated = $item['is_validated'];

                    if ($lengkap) {
                        $statusColor = '#16a34a';
                        $cellBg = 'rgba(34,197,94,0.06)';
                    } elseif ($dibawa > 0) {
                        $statusColor = '#d97706';
                        $cellBg = 'rgba(245,158,11,0.06)';
                    } else {
                        $statusColor = '#dc2626';
                        $cellBg = 'transparent';
                    }
                @endphp
                <td style="padding:0.6rem 0.5rem; text-align:center; background:{{ $cellBg }}; border-left:1px solid rgba(0,47,69,0.04); vertical-align:middle;">
                    @if(!$item['pengumpulan'])
                        <span style="color:{{ $statusColor }}; font-weight:800; font-size:0.85rem;">{{ $dibawa }}/{{ $b->jumlah_kebutuhan }}</span>
                        <div style="margin-top:0.25rem;">
                            <span style="background: rgba(239,68,68,0.1); color:#991b1b; font-size:0.58rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:6px; text-transform:uppercase; letter-spacing:0.04em;">Belum Input</span>
                        </div>
                    @else
                        <div style="display:flex; align-items:center; justify-content:center; gap:0.4rem; flex-wrap:nowrap;">
                            <div style="display:flex; align-items:center; gap:0.3rem;">
                                <span style="color:{{ $statusColor }}; font-weight:800; font-size:0.85rem;">{{ $dibawa }}/{{ $b->jumlah_kebutuhan }}</span>
                                @if($item['foto'])
                                <a href="{{ $item['foto'] }}" target="_blank" title="Lihat bukti foto" style="display:flex;">
                                    <img src="{{ $item['foto'] }}" style="width:22px; height:22px; object-fit:cover; border-radius:5px; border:1.5px solid white; box-shadow:0 1px 3px rgba(0,0,0,0.15);">
                                </a>
                                @endif
                            </div>

                            <form action="{{ route('panitia.p3k.individu.validasi', [$b->id, $peserta->id]) }}" method="POST" style="display:flex;">
                                @csrf
                                @if($isValidated)
                                    <button type="submit" title="{{ $lengkap ? 'Sudah di-ACC penuh' : 'ACC cicilan — klik untuk batal' }}"
                                        style="background:{{ $lengkap ? '#16a34a' : '#d97706' }}; color:white; border:none; width:24px; height:24px; border-radius:6px; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        ✓
                                    </button>
                                @else
                                    <button type="submit" title="Klik untuk ACC"
                                        style="background: rgba(0,47,69,0.08); color:#002f45; border:1px solid rgba(0,47,69,0.15); width:24px; height:24px; border-radius:6px; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        ○
                                    </button>
                                @endif
                            </form>
                        </div>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Legend --}}
<div style="display:flex; gap:1.25rem; flex-wrap:wrap; margin:-0.75rem 0 1.5rem 0.25rem; font-size:0.7rem; color:#002f45; opacity:0.55;">
    <div style="display:flex; align-items:center; gap:0.35rem;">
        <span style="display:inline-block; width:10px; height:10px; border-radius:3px; background:rgba(34,197,94,0.5);"></span> Lengkap
    </div>
    <div style="display:flex; align-items:center; gap:0.35rem;">
        <span style="display:inline-block; width:10px; height:10px; border-radius:3px; background:rgba(245,158,11,0.5);"></span> Sebagian
    </div>
    <div style="display:flex; align-items:center; gap:0.35rem;">
        <span style="display:inline-block; width:10px; height:10px; border-radius:3px; background:rgba(239,68,68,0.25);"></span> Belum
    </div>
    <div style="display:flex; align-items:center; gap:0.35rem;">
        <span style="display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:4px; background:#16a34a; color:white; font-size:0.55rem;">✓</span> Sudah ACC
    </div>
    <div style="display:flex; align-items:center; gap:0.35rem;">
        <span style="display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:4px; background:rgba(0,47,69,0.08); border:1px solid rgba(0,47,69,0.15); color:#002f45; font-size:0.55rem;">○</span> Belum ACC
    </div>
</div>
@endif