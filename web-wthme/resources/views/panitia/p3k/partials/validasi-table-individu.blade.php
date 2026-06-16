{{-- Partial: Barang Individu — Summary per Kelompok + Matrix per Peserta --}}
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
@endphp

{{-- Progress keseluruhan --}}
<div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.25rem 2rem; margin-bottom: 1rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
    <div style="text-align: center;">
        <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em;">Progress</div>
        <div style="color:#d2c296; font-size:1.75rem; font-weight:800; line-height:1;">{{ $persen }}%</div>
    </div>
    <div style="flex:1; min-width:200px;">
        <div style="background: rgba(255,255,255,0.1); border-radius:999px; height:10px; overflow:hidden;">
            <div style="background: linear-gradient(90deg, #d2c296, #bdd1d3); height:100%; border-radius:999px; width:{{ $persen }}%;"></div>
        </div>
        <div style="color:#bdd1d3; font-size:0.75rem; margin-top:0.4rem;">{{ $lengkapCell }} dari {{ $totalCell }} (peserta x barang) lengkap</div>
    </div>
</div>

{{-- Summary per Barang: Total Kelompok vs Target, + info Stok Global --}}
@if($summaryIndividuKelompok->isNotEmpty())
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    @foreach($summaryIndividuKelompok as $s)
    @php
        $b = $s['barang'];
        $pctKlp = $s['target_kelompok'] > 0 ? round(($s['total_kelompok'] / $s['target_kelompok']) * 100) : 0;
        $colorKlp = $s['is_lengkap'] ? '#16a34a' : ($s['total_kelompok'] > 0 ? '#d97706' : '#dc2626');
    @endphp
    <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.25rem; padding: 1.1rem 1.25rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.6rem;">
            <span style="color:#002f45; font-weight:800; font-size:0.9rem;">{{ $b->nama_barang }}</span>
            @if($s['is_lengkap'])
            <span style="background: rgba(34,197,94,0.15); color:#166534; font-size:0.6rem; font-weight:800; padding:0.2rem 0.5rem; border-radius:6px; text-transform:uppercase;">Lengkap</span>
            @endif
        </div>

        <div style="display:flex; align-items:baseline; gap:0.35rem; margin-bottom:0.3rem;">
            <span style="color:{{ $colorKlp }}; font-size:1.5rem; font-weight:800; line-height:1;">{{ $s['total_kelompok'] }}</span>
            <span style="color:#002f45; opacity:0.4; font-size:0.8rem;">/ {{ $s['target_kelompok'] }} {{ $b->satuan }}</span>
        </div>
        <div style="color:#002f45; opacity:0.45; font-size:0.7rem; margin-bottom:0.6rem;">Total dibawa kelompok ini</div>

        <div style="background: rgba(0,47,69,0.06); border-radius:999px; height:6px; overflow:hidden; margin-bottom:0.7rem;">
            <div style="background:{{ $colorKlp }}; height:100%; border-radius:999px; width:{{ min(100, $pctKlp) }}%;"></div>
        </div>

        {{-- Stok terpakai per kelompok ini --}}
        <div style="border-top:1px solid rgba(0,47,69,0.08); padding-top:0.7rem; margin-top:0.2rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.68rem; color:#002f45; opacity:0.55; margin-bottom:0.45rem;">
                <span>Terkumpul kelompok: <strong>{{ $s['total_terkumpul'] }}</strong></span>
                <span>Sisa: <strong style="color:{{ $s['total_sisa'] > 0 ? '#16a34a' : '#dc2626' }};">{{ $s['total_sisa'] }}</strong></span>
            </div>

            {{-- Kontrol terpakai (hanya untuk admin & divisi P3K) --}}
            @php $canEdit = auth()->user()->role === 'admin' || strtoupper(auth()->user()->divisi ?? '') === 'P3K'; @endphp
            @if($canEdit)
            <div style="display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap;">
                <form action="{{ route('panitia.p3k.stok.adjust', [$b->id, $kelompok]) }}" method="POST" style="flex:1; min-width:70px;">
                    @csrf
                    <input type="hidden" name="delta" value="1">
                    <button type="submit" {{ $s['total_sisa'] <= 0 ? 'disabled' : '' }}
                        style="width:100%; background: rgba(217,119,6,0.1); color:#92400e; border:1px solid rgba(217,119,6,0.2); padding:0.35rem 0.4rem; border-radius:0.6rem; font-size:0.68rem; font-weight:800; cursor:pointer; {{ $s['total_sisa'] <= 0 ? 'opacity:0.4; cursor:not-allowed;' : '' }}">
                        − Pakai 1
                    </button>
                </form>
                <form action="{{ route('panitia.p3k.stok.adjust', [$b->id, $kelompok]) }}" method="POST" style="flex:1; min-width:70px;">
                    @csrf
                    <input type="hidden" name="delta" value="-1">
                    <button type="submit" {{ $s['total_terpakai'] <= 0 ? 'disabled' : '' }}
                        style="width:100%; background: rgba(0,47,69,0.06); color:#002f45; border:1px solid rgba(0,47,69,0.12); padding:0.35rem 0.4rem; border-radius:0.6rem; font-size:0.68rem; font-weight:700; cursor:pointer; {{ $s['total_terpakai'] <= 0 ? 'opacity:0.4; cursor:not-allowed;' : '' }}">
                        ↺ Batal
                    </button>
                </form>
                <form action="{{ route('panitia.p3k.stok.terpakai', [$b->id, $kelompok]) }}" method="POST" style="display:flex; align-items:center; gap:0.3rem; flex:2; min-width:120px;">
                    @csrf
                    <input type="number" name="total_terpakai" value="{{ $s['total_terpakai'] }}" min="0" max="{{ $s['total_terkumpul'] }}"
                        style="width:52px; padding:0.3rem 0.35rem; border:1px solid rgba(0,47,69,0.15); border-radius:0.5rem; text-align:center; font-size:0.7rem; background:rgba(255,255,255,0.8); flex-shrink:0;">
                    <button type="submit"
                        style="background: rgba(0,47,69,0.08); color:#002f45; border:none; padding:0.3rem 0.6rem; border-radius:0.5rem; font-size:0.65rem; font-weight:700; cursor:pointer; white-space:nowrap;">
                        Set
                    </button>
                </form>
            </div>
            @else
            <div style="font-size:0.68rem; color:#002f45; opacity:0.45;">
                Terpakai: <strong>{{ $s['total_terpakai'] }}</strong> {{ $b->satuan }}
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Matrix per Peserta --}}
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
                                    <button type="submit" title="{{ $lengkap ? 'Sudah di-ACC penuh' : 'ACC cicilan - klik untuk batal' }}"
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
