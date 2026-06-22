@extends('layouts.app')

@section('content')
<style>
@media (max-width: 640px) {
    .page-wrap { padding: 1.25rem 0.85rem !important; }
    .header-flex { flex-direction: column !important; }
    details summary { flex-direction: column !important; align-items: flex-start !important; gap: 0.4rem !important; }
    table { font-size: 0.72rem !important; }
    td, th { padding: 0.4rem 0.5rem !important; }
    .stok-mini-grid { grid-template-columns: repeat(2,1fr) !important; }
}
</style>
@php
$menuConfig = ['logistik'=>['label'=>'Logistik','icon'=>'🎒'], 'konsumsi'=>['label'=>'Konsumsi','icon'=>'🥘'], 'p3k'=>['label'=>'P3K','icon'=>'🩹']];
@endphp
<div class="page-wrap" style="min-height:calc(100vh - 64px); padding:1.75rem 1rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1200px; margin:0 auto;">

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <div>
            <a href="{{ route('panitia.p3k.index') }}" style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.85rem; font-weight:600; display:inline-flex; align-items:center; gap:0.4rem; margin-bottom:0.75rem;">← Kembali</a>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">📊 Rekap Seluruh Kelompok</h1>
        </div>
        <a href="{{ route('panitia.p3k.export') }}" style="display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#d2c296; text-decoration:none; padding:0.75rem 1.4rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">📥 Export Excel</a>
    </div>

    {{-- Stok Global Individu per menu (read-only; kontrol di halaman Index) --}}
    @foreach($menus as $menu)
    @if($stokIndividuByMenu[$menu]->isNotEmpty())
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.1rem; font-weight:800; margin:1.5rem 0 0.75rem; padding-left:0.25rem;">
        {{ $menuConfig[$menu]['icon'] }} Stok Global Individu — {{ $menuConfig[$menu]['label'] }}
    </h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    @foreach($stokIndividuByMenu[$menu] as $s)
    @if($s['total_terkumpul'] > 0)
    @php $b=$s['barang']; $pct=$s['total_terkumpul']>0?round(($s['total_sisa']/$s['total_terkumpul'])*100):0; $cSisa=$s['total_sisa']>0?'#16a34a':($s['total_terkumpul']>0?'#dc2626':'#94a3b8'); @endphp
    <div style="background:rgba(255,255,255,0.3); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.4); border-radius:1.25rem; padding:1rem 1.25rem;">
        <div style="color:#002f45; font-weight:800; font-size:0.88rem; margin-bottom:0.6rem;">{{ $b->nama_barang }} <span style="opacity:0.4; font-size:0.6rem;">{{ $b->satuan }}</span></div>
        <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:0.5rem;">
            <div style="text-align:center;"><div style="color:#002f45; opacity:0.4; font-size:0.58rem; text-transform:uppercase;">Terkumpul</div><div style="color:#002f45; font-weight:800; font-size:1.1rem;">{{ $s['total_terkumpul'] }}</div></div>
            <div style="text-align:center;"><div style="color:#92400e; opacity:0.6; font-size:0.58rem; text-transform:uppercase;">Terpakai</div><div style="color:#d97706; font-weight:800; font-size:1.1rem;">{{ $s['total_terpakai'] }}</div></div>
            <div style="text-align:center;"><div style="color:{{ $cSisa }}; opacity:0.6; font-size:0.58rem; text-transform:uppercase;">Sisa</div><div style="color:{{ $cSisa }}; font-weight:800; font-size:1.1rem;">{{ $s['total_sisa'] }}</div></div>
        </div>
        <div style="background:rgba(0,47,69,0.06); border-radius:999px; height:5px; overflow:hidden;"><div style="background:{{ $cSisa }}; height:100%; border-radius:999px; width:{{ $pct }}%;"></div></div>
    </div>
    @endif
    @endforeach
    </div>
    @endif
    @endforeach


    {{-- Rekap per kelompok --}}
    @foreach($kelompoks as $k)
    <details style="background:rgba(255,255,255,0.2); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.4); border-radius:1.5rem; margin-bottom:1rem; overflow:hidden;">
        <summary style="padding:1.1rem 1.5rem; cursor:pointer; color:#002f45; font-weight:800; font-size:1rem; list-style:none; display:flex; align-items:center; justify-content:space-between;">
            <span>Kelompok {{ $k }}</span>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                @foreach($menus as $menu)
                @php
                    $allDone = collect($rekapKelompokByMenu[$k][$menu] ?? [])->every(fn($r)=>$r['is_lengkap']);
                    $indvDone = collect($summaryIndividuByKelompokMenu[$k][$menu] ?? [])->every(fn($r)=>$r['is_lengkap']);
                    $hasBarang = count($rekapKelompokByMenu[$k][$menu]??[]) > 0 || count($summaryIndividuByKelompokMenu[$k][$menu]??[]) > 0;
                @endphp
                @if($hasBarang)
                <span style="font-size:0.7rem; padding:0.2rem 0.6rem; border-radius:999px; font-weight:700;
                    background:{{ ($allDone && $indvDone) ? 'rgba(34,197,94,0.2)' : 'rgba(245,158,11,0.2)' }};
                    color:{{ ($allDone && $indvDone) ? '#166534' : '#92400e' }};">
                    {{ $menuConfig[$menu]['icon'] }} {{ $menuConfig[$menu]['label'] }}
                </span>
                @endif
                @endforeach
            </div>
        </summary>

        <div style="padding:0 1.5rem 1.5rem;">
            @foreach($menus as $menu)
            @php $barangsKMenu = $rekapKelompokByMenu[$k][$menu] ?? collect(); $pengupMenu = $pengumpulanByKelompokMenu[$k][$menu] ?? collect(); $barangsIMenu = $summaryIndividuByKelompokMenu[$k][$menu] ?? collect(); @endphp
            @if($barangsKMenu->isNotEmpty() || $barangsIMenu->isNotEmpty())
            <div style="margin-top:1rem;">
                <div style="color:#002f45; font-weight:800; font-size:0.85rem; margin-bottom:0.6rem; display:flex; align-items:center; gap:0.5rem;">
                    {{ $menuConfig[$menu]['icon'] }} {{ $menuConfig[$menu]['label'] }}
                    @if($anggotaBelumTercakupByKelompokMenu[$k][$menu]->isNotEmpty())
                    <span style="font-size:0.68rem; color:#991b1b; font-weight:700; background:rgba(239,68,68,0.1); padding:0.15rem 0.5rem; border-radius:999px;">
                        ⚠️ {{ $anggotaBelumTercakupByKelompokMenu[$k][$menu]->count() }} belum tercakup
                    </span>
                    @endif
                </div>

                {{-- Barang kelompok rows --}}
                @if($barangsKMenu->isNotEmpty())
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem; margin-bottom:0.75rem;">
                    <thead><tr style="background:rgba(0,47,69,0.04);">
                        <th style="padding:0.5rem 0.75rem; text-align:left; color:#002f45; font-size:0.7rem;">Barang Kelompok</th>
                        <th style="padding:0.5rem; text-align:center; color:#002f45; font-size:0.7rem;">Terkumpul / Target</th>
                        <th style="padding:0.5rem; text-align:center; color:#002f45; font-size:0.7rem;">Status</th>
                    </tr></thead>
                    <tbody>
                    @foreach($barangsKMenu as $row)
                    <tr style="border-bottom:1px solid rgba(0,47,69,0.04);">
                        <td style="padding:0.5rem 0.75rem; color:#002f45; font-weight:700;">{{ $row['barang']->nama_barang }}</td>
                        <td style="padding:0.5rem; text-align:center; color:#002f45;">{{ $row['jumlah_terkumpul'] }} / {{ $row['barang']->jumlah_kebutuhan }} {{ $row['barang']->satuan }}</td>
                        <td style="padding:0.5rem; text-align:center;">
                            @if($row['is_lengkap'])<span style="color:#16a34a; font-weight:800; font-size:0.75rem;">✓ Lengkap</span>
                            @else<span style="color:#dc2626; font-weight:800; font-size:0.75rem;">Belum</span>@endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif

                {{-- Pengumpulan kolektif individu --}}
                @if($barangsIMenu->isNotEmpty() && $pengupMenu->isNotEmpty())
                <table style="width:100%; border-collapse:collapse; font-size:0.78rem;">
                    <thead><tr style="background:rgba(0,47,69,0.04);">
                        <th style="padding:0.5rem 0.75rem; text-align:left; color:#002f45; font-size:0.68rem; min-width:180px;">Perwakilan Individu</th>
                        @foreach($barangsIMenu as $sum)
                        <th style="padding:0.5rem; text-align:center; color:#002f45; font-size:0.68rem; min-width:90px;">{{ $sum['barang']->nama_barang }}</th>
                        @endforeach
                        <th style="padding:0.5rem; text-align:center; color:#002f45; font-size:0.68rem;">ACC</th>
                    </tr></thead>
                    <tbody>
                    @foreach($pengupMenu as $p)
                    @php $namaLain = $p->anggota->pluck('peserta.name')->reject(fn($n)=>$n===$p->perwakilan->name)->values(); @endphp
                    <tr style="border-bottom:1px solid rgba(0,47,69,0.04);">
                        <td style="padding:0.5rem 0.75rem; color:#002f45; font-weight:700;">
                            {{ $p->perwakilan->name }}
                            @if($namaLain->isNotEmpty())
                            <table style="width:100%; border-collapse:collapse; margin-top:0.2rem;">
                                @foreach($namaLain as $i => $n)
                                <tr><td style="padding:0 0.3rem 0 0; font-size:0.6rem; color:#002f45; opacity:0.4; width:14px; vertical-align:top;">{{ $i+1 }}.</td><td style="font-size:0.65rem; color:#002f45; opacity:0.65;">{{ $n }}</td></tr>
                                @endforeach
                            </table>
                            @endif
                        </td>
                        @foreach($barangsIMenu as $sum)
                        @php $dibawa=$p->jumlahDibawaUntuk($sum['barang']->id); $target=$p->targetUntuk($sum['barang']); $ok=$dibawa>=$target; @endphp
                        <td style="padding:0.5rem; text-align:center; background:{{ $ok?'rgba(34,197,94,0.1)':($dibawa>0?'rgba(245,158,11,0.1)':'transparent') }};">
                            <span style="color:{{ $ok?'#166534':($dibawa>0?'#92400e':'#991b1b') }}; font-weight:800; font-size:0.8rem;">{{ $dibawa }}/{{ $target }}</span>
                        </td>
                        @endforeach
                        <td style="padding:0.5rem; text-align:center;">
                            @if($p->is_validated)
                            <span style="background:rgba(34,197,94,0.15); color:#166534; font-size:0.65rem; font-weight:800; padding:0.2rem 0.5rem; border-radius:6px;">✓ ACC</span>
                            @if($p->updatedBy)<div style="font-size:0.58rem; color:#002f45; opacity:0.4; margin-top:0.15rem;">oleh {{ $p->updatedBy->name }}</div>@endif
                            @else
                            <span style="background:rgba(245,158,11,0.15); color:#92400e; font-size:0.65rem; font-weight:800; padding:0.2rem 0.5rem; border-radius:6px;">Belum</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    {{-- Summary total individu --}}
                    <tr style="background:rgba(0,47,69,0.04);">
                        <td style="padding:0.5rem 0.75rem; color:#002f45; font-weight:800; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.03em;">Total Kelompok</td>
                        @foreach($barangsIMenu as $sum)
                        <td style="padding:0.5rem; text-align:center;">
                            <span style="color:{{ $sum['is_lengkap']?'#166534':'#dc2626' }}; font-weight:800; font-size:0.8rem;">{{ $sum['total_kelompok'] }}/{{ $sum['target_kelompok'] }}</span>
                        </td>
                        @endforeach
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                @endif
            </div>
            @endif
            @endforeach
        </div>
    </details>
    @endforeach

    {{-- Obat Pribadi --}}
    @if($obatPribadi->isNotEmpty())
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.15rem; font-weight:800; margin:2rem 0 0.75rem;">💊 Obat Pribadi</h3>
    @include('panitia.p3k.partials.obat-pribadi', ['obatPribadi' => $obatPribadi, 'kelompok' => null])
    @endif

</div>
</div>
@endsection
