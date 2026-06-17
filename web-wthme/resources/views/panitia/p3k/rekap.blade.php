@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
<div style="max-width:1200px; margin:0 auto;">

    <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
        <div>
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                Rekap Global P3K
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem; font-weight:500;">
                📊 Monitoring logistik medis & obat pribadi seluruh kelompok
            </p>
        </div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('panitia.p3k.export') }}" style="text-decoration:none; background: rgba(22, 163, 74, 0.85); color:white; padding:0.75rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:800;">
                ⬇️ Download Excel
            </a>
            <a href="{{ route('panitia.p3k.index') }}" style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.75rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Matrix Barang Kelompok --}}
    @if($barangsKelompok->isNotEmpty() && $kelompoks->isNotEmpty())
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">📦 Matrix Barang Kelompok</h3>
    <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; margin-bottom:2rem;">
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
                <tr style="background: rgba(255, 255, 255, 0.1); border-bottom: 2px solid rgba(0, 47, 69, 0.1);">
                    <th style="padding:1rem; text-align:left; color:#002f45; font-weight:800; position:sticky; left:0; background:rgba(224, 222, 205, 0.95); z-index:10;">Kelompok</th>
                    @foreach($barangsKelompok as $b)
                    <th style="padding:1rem; text-align:center; color:#002f45; font-weight:700; min-width:100px;">
                        <div style="font-size:0.85rem;">{{ $b->nama_barang }}</div>
                        <div style="color:#002f45; opacity:0.5; font-weight:500; font-size:0.7rem;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($kelompoks as $k)
            <tr style="border-bottom: 1px solid rgba(0, 0, 0, 0.05);">
                <td style="padding:1rem; font-weight:800; color:#002f45; position:sticky; left:0; background:rgba(255, 255, 255, 0.85); z-index:5;">Klp {{ $k }}</td>
                @foreach($rekapKelompok[$k] as $row)
                @php
                    $bgStatus = $row['is_lengkap'] ? 'rgba(34, 197, 94, 0.2)' : ($row['jumlah_terkumpul'] > 0 ? 'rgba(245, 158, 11, 0.2)' : 'rgba(239, 68, 68, 0.1)');
                    $colorTxt = $row['is_lengkap'] ? '#166534' : ($row['jumlah_terkumpul'] > 0 ? '#92400e' : '#991b1b');
                @endphp
                <td style="padding:1rem; text-align:center; background:{{ $bgStatus }};">
                    <span style="color:{{ $colorTxt }}; font-weight:800; font-size:0.9rem;">{{ $row['jumlah_terkumpul'] }}</span>
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Stok Global Barang Individu --}}
    @if($stokIndividu->isNotEmpty())
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">📊 Stok Global Barang Individu</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
        @foreach($stokIndividu as $s)
        @php $b = $s['barang']; @endphp
        <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.25rem; padding: 1.1rem 1.25rem;">
            <div style="color:#002f45; font-weight:800; font-size:0.9rem; margin-bottom:0.6rem;">{{ $b->nama_barang }}</div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:#002f45;">
                <div style="text-align:center; flex:1;">
                    <div style="opacity:0.45; font-size:0.6rem; text-transform:uppercase;">Kumpul</div>
                    <div style="font-weight:800; font-size:1.1rem;">{{ $s['total_terkumpul'] }}</div>
                </div>
                <div style="text-align:center; flex:1; border-left:1px solid rgba(0,47,69,0.08); border-right:1px solid rgba(0,47,69,0.08);">
                    <div style="opacity:0.45; font-size:0.6rem; text-transform:uppercase;">Pakai</div>
                    <div style="font-weight:800; font-size:1.1rem; color:#d97706;">{{ $s['total_terpakai'] }}</div>
                </div>
                <div style="text-align:center; flex:1;">
                    <div style="opacity:0.45; font-size:0.6rem; text-transform:uppercase;">Sisa</div>
                    <div style="font-weight:800; font-size:1.1rem; color:{{ $s['total_sisa'] > 0 ? '#16a34a' : '#dc2626' }};">{{ $s['total_sisa'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Barang Individu per Kelompok --}}
    @if($barangsIndividu->isNotEmpty() && $kelompoks->isNotEmpty())
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">🎒 Barang Individu — Pengumpulan Kolektif per Kelompok</h3>

    @foreach($kelompoks as $k)
    <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; margin-bottom:1.5rem;">
        <div style="background: rgba(0, 47, 69, 0.8); padding:0.85rem 1.5rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                <span style="color:#d2c296; font-weight:800; font-size:0.9rem;">Kelompok {{ $k }}</span>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    @foreach($summaryIndividuPerKelompok[$k] as $sum)
                    @php $b = $sum['barang']; @endphp
                    <span style="background: rgba(255,255,255,0.1); color:{{ $sum['is_lengkap'] ? '#86efac' : '#fcd34d' }}; font-size:0.7rem; font-weight:700; padding:0.25rem 0.6rem; border-radius:0.5rem;">
                        {{ $b->nama_barang }}: {{ $sum['total_kelompok'] }}/{{ $sum['target_kelompok'] }}
                    </span>
                    @endforeach
                </div>
            </div>
            @if($anggotaBelumTercakupPerKelompok[$k]->isNotEmpty())
            <div style="margin-top:0.6rem; font-size:0.7rem; color:#fca5a5; font-weight:700;">
                ⚠️ Belum tercakup: {{ $anggotaBelumTercakupPerKelompok[$k]->pluck('name')->implode(', ') }}
            </div>
            @endif
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
            <thead>
                <tr style="background: rgba(255, 255, 255, 0.1); border-bottom: 2px solid rgba(0, 47, 69, 0.1);">
                    <th style="padding:0.85rem; text-align:left; color:#002f45; font-weight:800; position:sticky; left:0; background:rgba(224, 222, 205, 0.95); z-index:10; min-width:200px;">Perwakilan & Anggota</th>
                    @foreach($barangsIndividu as $b)
                    <th style="padding:0.85rem; text-align:center; color:#002f45; font-weight:700; min-width:120px;">
                        <div style="font-size:0.8rem;">{{ $b->nama_barang }}</div>
                        <div style="color:#002f45; opacity:0.5; font-weight:500; font-size:0.65rem;">{{ $b->jumlah_kebutuhan }} {{ $b->satuan }}/orang</div>
                    </th>
                    @endforeach
                    <th style="padding:0.85rem; text-align:center; color:#002f45; font-weight:700; min-width:100px;">Status ACC</th>
                </tr>
            </thead>
            <tbody>
            @forelse($pengumpulanKolektifPerKelompok[$k] as $p)
            @php
                $namaAnggotaLain = $p->anggota->pluck('peserta.name')->reject(fn($n) => $n === $p->perwakilan->name)->values();
            @endphp
            <tr style="border-bottom: 1px solid rgba(0, 0, 0, 0.05);">
                <td style="padding:0.85rem; font-weight:700; color:#002f45; position:sticky; left:0; background:rgba(255, 255, 255, 0.85); z-index:5;">
                    {{ $p->perwakilan->name }}
                    <span style="font-size:0.6rem; font-weight:700; color:#002f45; opacity:0.45; background:rgba(0,47,69,0.06); padding:0.1rem 0.4rem; border-radius:4px; margin-left:0.25rem;">Perwakilan</span>
                    @if($namaAnggotaLain->isNotEmpty())
                        <table style="width:100%; border-collapse:collapse; margin-top:0.35rem;">
                            @foreach($namaAnggotaLain as $i => $nama)
                            <tr>
                                <td style="padding:0.08rem 0.3rem 0.08rem 0; font-size:0.62rem; color:#002f45; opacity:0.4; width:14px; vertical-align:top;">{{ $i + 1 }}.</td>
                                <td style="padding:0.08rem 0; font-size:0.65rem; color:#002f45; opacity:0.65; font-weight:500;">{{ $nama }}</td>
                            </tr>
                            @endforeach
                        </table>
                    @else
                        <div style="font-size:0.65rem; opacity:0.4; font-weight:600; font-style:italic; margin-top:0.15rem;">mengumpulkan sendiri</div>
                    @endif
                </td>
                @foreach($barangsIndividu as $b)
                @php
                    $dibawa = $p->jumlahDibawaUntuk($b->id);
                    $target = $p->targetUntuk($b);
                    $lengkap = $dibawa >= $target;
                    $bgStatus = $lengkap ? 'rgba(34, 197, 94, 0.15)' : ($dibawa > 0 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(239, 68, 68, 0.08)');
                    $colorTxt = $lengkap ? '#166534' : ($dibawa > 0 ? '#92400e' : '#991b1b');
                @endphp
                <td style="padding:0.85rem; text-align:center; background:{{ $bgStatus }};">
                    <span style="color:{{ $colorTxt }}; font-weight:800;">
                        {{ $dibawa }} / {{ $target }}
                    </span>
                </td>
                @endforeach
                <td style="padding:0.85rem; text-align:center;">
                    @if($p->is_validated)
                    <span style="background: rgba(34, 197, 94, 0.15); color:#166534; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px;">✓ ACC</span>
                    @if($p->updatedBy)
                    <div style="font-size:0.6rem; color:#002f45; opacity:0.45; margin-top:0.25rem;">oleh {{ $p->updatedBy->name }}</div>
                    @endif
                    @else
                    <span style="background: rgba(245, 158, 11, 0.15); color:#92400e; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px;">Belum ACC</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ $barangsIndividu->count() + 2 }}" style="padding:1.5rem; text-align:center; color:#002f45; opacity:0.5;">Belum ada pengumpulan dari kelompok ini.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @endforeach
    @endif

    {{-- Obat Pribadi --}}
    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">💊 Obat Pribadi — Seluruh Peserta</h3>
    <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; margin-bottom:2rem;">
        @if($obatPribadi->isEmpty())
        <div style="padding:2.5rem; text-align:center; color:#002f45; opacity:0.5; font-weight:600;">Belum ada data obat pribadi.</div>
        @else
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
                <tr style="background: rgba(0, 47, 69, 0.05);">
                    <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Nama Peserta</th>
                    <th style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Kelompok</th>
                    <th style="padding:1rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Penyakit</th>
                    <th style="padding:1rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Obat</th>
                    <th style="padding:1rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">PJ P3K</th>
                    <th style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach($obatPribadi as $o)
            <tr style="border-bottom:1px solid rgba(0,0,0,0.03); background:{{ $o->sudah_diserahkan ? 'rgba(34,197,94,0.05)' : 'transparent' }};">
                <td style="padding:1rem 1.5rem; color:#002f45; font-weight:700;">{{ $o->peserta->name ?? '-' }}</td>
                <td style="padding:1rem; text-align:center; color:#002f45;">{{ $o->kelompok }}</td>
                <td style="padding:1rem; color:#002f45;">{{ $o->penyakit }}</td>
                <td style="padding:1rem; color:#002f45;">{{ $o->nama_obat ?? '-' }}</td>
                <td style="padding:1rem; color:#002f45; opacity:0.7;">{{ $o->pj->name ?? '-' }}</td>
                <td style="padding:1rem; text-align:center;">
                    @if($o->sudah_diserahkan)
                    <span style="background: rgba(34, 197, 94, 0.15); color:#166534; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px;">Diterima</span>
                    @else
                    <span style="background: rgba(245, 158, 11, 0.15); color:#92400e; font-size:0.65rem; font-weight:800; padding:0.3rem 0.6rem; border-radius:8px;">Belum</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

</div>
</div>
@endsection
