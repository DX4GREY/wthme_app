@php
    $totalBarangIndividu = $barangsIndividu->count();
    $lengkapBarangIndividu = $summaryIndividuKelompok->where('is_lengkap', true)->count();
    $persen = $totalBarangIndividu > 0 ? round(($lengkapBarangIndividu / $totalBarangIndividu) * 100) : 0;
    $canEdit = auth()->user()->role === 'admin' || strtoupper(auth()->user()->divisi ?? '') === 'P3K';
@endphp

{{-- Progress keseluruhan --}}
<div
    style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.25rem 2rem; margin-bottom: 1.5rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
    <div style="text-align: center;">
        <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em;">Progress</div>
        <div style="color:#d2c296; font-size:1.75rem; font-weight:800; line-height:1;">{{ $persen }}%</div>
    </div>
    <div style="flex:1; min-width:200px;">
        <div style="background: rgba(255,255,255,0.1); border-radius:999px; height:10px; overflow:hidden;">
            <div
                style="background: linear-gradient(90deg, #d2c296, #bdd1d3); height:100%; border-radius:999px; width:{{ $persen }}%;">
            </div>
        </div>
        <div style="color:#bdd1d3; font-size:0.75rem; margin-top:0.4rem;">{{ $lengkapBarangIndividu }} dari
            {{ $totalBarangIndividu }} jenis barang sudah terkumpul penuh di kelompok ini</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ANGGOTA BELUM TERCAKUP
     ══════════════════════════════════════════════════════ --}}
<div
    style="background: {{ $anggotaBelumTercakup->isNotEmpty() ? 'rgba(0, 47, 69, 0.05)' : 'rgba(34,197,94,0.1)' }}; border:1px solid {{ $anggotaBelumTercakup->isNotEmpty() ? 'rgba(0, 47, 69, 0.15)' : 'rgba(34,197,94,0.2)' }}; border-radius:1.25rem; padding:1.5rem; margin-bottom:2rem;">
    @if ($anggotaBelumTercakup->isEmpty())
        <div style="display:flex; align-items:center; gap:0.5rem;">
            <span style="color:#166534; font-weight:700; font-size:0.85rem;">✅ Semua anggota kelompok ini sudah tercakup
                di pengumpulan manapun.</span>
        </div>
    @else
        <div
            style="color:#002f45; font-weight:800; font-size:0.9rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
            <span>👥</span>
            <span>{{ $anggotaBelumTercakup->count() }} Anggota Belum Tercakup di Pengumpulan Manapun</span>
        </div>

        <div style="overflow-x: auto; border-radius: 0.75rem; border: 1px solid rgba(0, 47, 69, 0.1);">
            <table
                style="width:100%; border-collapse:collapse; font-size:0.85rem; text-align:left; background:rgba(255,255,255,0.5);">
                <thead>
                    <tr
                        style="background:rgba(0, 47, 69, 0.08); color:#002f45; border-bottom:1px solid rgba(0, 47, 69, 0.15);">
                        <th style="padding:0.75rem 1rem; font-weight:800; width:60px; text-align:center;">No</th>
                        <th style="padding:0.75rem 1rem; font-weight:800;">Nama Anggota</th>
                        <th style="padding:0.75rem 1rem; font-weight:800;">NIM / Identitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($anggotaBelumTercakup as $index => $p)
                        <tr
                            style="border-bottom: 1px solid rgba(0, 47, 69, 0.06); color:#002f45; background: {{ $index % 2 === 0 ? 'rgba(255,255,255,0.4)' : 'transparent' }};">
                            <td style="padding:0.65rem 1rem; text-align:center; font-weight:600; opacity:0.7;">
                                {{ $index + 1 }}</td>
                            <td style="padding:0.65rem 1rem; font-weight:700;">{{ $p->name }}</td>
                            <td style="padding:0.65rem 1rem; opacity:0.7;">{{ $p->nim ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════
     STOK PER BARANG (per kelompok ini)
     ══════════════════════════════════════════════════════ --}}
@if ($summaryIndividuKelompok->isNotEmpty())
    <div style="margin-bottom:2rem;">
        <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.75rem;">
            <span style="font-size:1rem; font-weight:800; color:#002f45; font-family:'Playfair Display',serif;">📦 Stok
                Barang Individu</span>
            <span style="font-size:0.68rem; color:#002f45; opacity:0.4; font-weight:600;">Kelompok
                {{ $kelompok }}</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
            @foreach ($summaryIndividuKelompok as $s)
                @php
                    $b = $s['barang'];
                    $terkumpul = $s['total_terkumpul'];
                    $terpakai = $s['total_terpakai'];
                    $sisa = $s['total_sisa'];
                    $sisaColor = $sisa <= 0 ? '#dc2626' : ($sisa <= $terkumpul * 0.25 ? '#d97706' : '#16a34a');
                @endphp

                <div
                    style="background:rgba(255,255,255,0.35); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.5); border-radius:1.25rem; overflow:hidden;">

                    {{-- Header barang --}}
                    <div
                        style="background:rgba(0,47,69,0.06); padding:0.85rem 1.25rem; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(0,47,69,0.07);">
                        <span style="font-weight:800; color:#002f45; font-size:0.9rem;">{{ $b->nama_barang }}</span>
                        <span
                            style="font-size:0.65rem; color:#002f45; opacity:0.4; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">{{ $b->satuan }}</span>
                    </div>

                    <div style="padding:1rem 1.25rem;">

                        {{-- 3 angka utama --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; margin-bottom:1rem;">

                            {{-- Terkumpul --}}
                            <div
                                style="background:rgba(0,47,69,0.05); border-radius:0.75rem; padding:0.6rem 0.5rem; text-align:center;">
                                <div
                                    style="font-size:0.6rem; font-weight:700; color:#002f45; opacity:0.45; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">
                                    Terkumpul</div>
                                <div style="font-size:1.5rem; font-weight:800; color:#002f45; line-height:1;">
                                    {{ $terkumpul }}</div>
                            </div>

                            {{-- Terpakai --}}
                            <div
                                style="background:rgba(217,119,6,0.08); border-radius:0.75rem; padding:0.6rem 0.5rem; text-align:center; border:1px solid rgba(217,119,6,0.15);">
                                <div
                                    style="font-size:0.6rem; font-weight:700; color:#92400e; opacity:0.7; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">
                                    Terpakai</div>
                                <div style="font-size:1.5rem; font-weight:800; color:#d97706; line-height:1;">
                                    {{ $terpakai }}</div>
                            </div>

                            {{-- Sisa --}}
                            <div
                                style="background:{{ $sisa <= 0 ? 'rgba(220,38,38,0.08)' : 'rgba(22,163,74,0.08)' }}; border-radius:0.75rem; padding:0.6rem 0.5rem; text-align:center; border:1px solid {{ $sisa <= 0 ? 'rgba(220,38,38,0.2)' : 'rgba(22,163,74,0.15)' }};">
                                <div
                                    style="font-size:0.6rem; font-weight:700; color:{{ $sisaColor }}; opacity:0.7; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">
                                    Sisa</div>
                                <div
                                    style="font-size:1.5rem; font-weight:800; color:{{ $sisaColor }}; line-height:1;">
                                    {{ $sisa }}</div>
                            </div>
                        </div>

                        {{-- Kontrol — hanya admin & divisi P3K --}}
                        @if ($canEdit)
                            <div style="border-top:1px solid rgba(0,47,69,0.08); padding-top:0.9rem;">

                                {{-- Label kontrol --}}
                                <div
                                    style="font-size:0.65rem; font-weight:700; color:#002f45; opacity:0.4; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.6rem;">
                                    Catat Pemakaian
                                </div>

                                {{-- Tombol +1 / -1 --}}
                                <div style="display:flex; gap:0.5rem; margin-bottom:0.6rem;">

                                    {{-- Tambah terpakai (+1) --}}
                                    <form action="{{ route('panitia.p3k.stok.adjust', [$b->id, $kelompok]) }}"
                                        method="POST" style="flex:1;">
                                        @csrf
                                        <input type="hidden" name="delta" value="1">
                                        <button type="submit" {{ $sisa <= 0 ? 'disabled' : '' }}
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
                                    <form action="{{ route('panitia.p3k.stok.adjust', [$b->id, $kelompok]) }}"
                                        method="POST" style="flex:1;">
                                        @csrf
                                        <input type="hidden" name="delta" value="-1">
                                        <button type="submit" {{ $terpakai <= 0 ? 'disabled' : '' }}
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
                                <form action="{{ route('panitia.p3k.stok.terpakai', [$b->id, $kelompok]) }}"
                                    method="POST">
                                    @csrf
                                    <div
                                        style="display:flex; align-items:center; gap:0.5rem; background:rgba(0,47,69,0.04); border:1px solid rgba(0,47,69,0.1); border-radius:0.75rem; padding:0.45rem 0.75rem;">
                                        <label
                                            style="font-size:0.68rem; color:#002f45; opacity:0.5; font-weight:600; white-space:nowrap; flex:1;">
                                            Set jumlah terpakai:
                                        </label>
                                        <input type="number" name="total_terpakai" value="{{ $terpakai }}"
                                            min="0" max="{{ $terkumpul }}"
                                            style="width:56px; padding:0.3rem 0.4rem; border:1px solid rgba(0,47,69,0.18); border-radius:0.5rem; text-align:center; font-size:0.8rem; font-weight:700; background:white; color:#002f45; flex-shrink:0;">
                                        <button type="submit"
                                            style="background:#002f45; color:white; border:none; padding:0.3rem 0.75rem; border-radius:0.5rem; font-size:0.7rem; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">
                                            Simpan
                                        </button>
                                    </div>
                                    <div
                                        style="font-size:0.6rem; color:#002f45; opacity:0.35; margin-top:0.3rem; padding-left:0.25rem;">
                                        Maks. {{ $terkumpul }} {{ $b->satuan }} (sesuai yang terkumpul di
                                        kelompok ini)
                                    </div>
                                </form>
                            </div>
                        @else
                            {{-- View-only untuk non-P3K --}}
                            <div
                                style="border-top:1px solid rgba(0,47,69,0.08); padding-top:0.7rem; font-size:0.7rem; color:#002f45; opacity:0.45; text-align:center;">
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
     DAFTAR PENGUMPULAN KOLEKTIF (PER PERWAKILAN)
     ══════════════════════════════════════════════════════ --}}
@if ($pengumpulanKolektif->isEmpty() || $barangsIndividu->isEmpty())
    <div
        style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius:1.5rem; padding:2.5rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2); margin-bottom:1.5rem;">
        <p style="color:#002f45; opacity:0.6; font-weight:600;">Belum ada pengumpulan dari kelompok ini.</p>
    </div>
@else
    <div
        style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07); margin-bottom:1.5rem;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
                <thead>
                    <tr style="background: rgba(0, 47, 69, 0.05); border-bottom: 2px solid rgba(0,47,69,0.1);">
                        <th
                            style="padding:0.85rem 1.25rem; text-align:left; color:#002f45; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; position:sticky; left:0; background:rgba(224,222,205,0.97); z-index:10; min-width:220px;">
                            Perwakilan & Anggota
                        </th>
                        @foreach ($barangsIndividu as $b)
                            <th
                                style="padding:0.85rem 0.5rem; text-align:center; color:#002f45; min-width:110px; border-left:1px solid rgba(0,47,69,0.06);">
                                <div style="font-size:0.75rem; font-weight:800;">{{ $b->nama_barang }}</div>
                                <div
                                    style="opacity:0.45; font-weight:600; font-size:0.62rem; text-transform:uppercase; letter-spacing:0.04em; margin-top:0.15rem;">
                                    {{ $b->jumlah_kebutuhan }} {{ $b->satuan }}/orang
                                </div>
                            </th>
                        @endforeach
                        <th
                            style="padding:0.85rem 0.75rem; text-align:center; color:#002f45; min-width:130px; border-left:1px solid rgba(0,47,69,0.06);">
                            Status ACC
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pengumpulanKolektif as $p)
                        @php
                            $namaAnggotaLain = $p->anggota
                                ->pluck('peserta.name')
                                ->reject(fn($n) => $n === $p->perwakilan->name)
                                ->values();
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(0,47,69,0.05);">
                            <td
                                style="padding:0.9rem 1.25rem; color:#002f45; position:sticky; left:0; background:rgba(255,255,255,0.92); z-index:5;">
                                <div
                                    style="display:flex; align-items:center; gap:0.4rem; font-weight:800; font-size:0.82rem;">
                                    {{ $p->perwakilan->name }}
                                    <span
                                        style="font-size:0.62rem; font-weight:700; color:#002f45; opacity:0.45; background:rgba(0,47,69,0.06); padding:0.1rem 0.4rem; border-radius:4px;">Perwakilan</span>
                                    @if ($p->foto_bukti)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($p->foto_bukti) }}"
                                            target="_blank" title="Lihat bukti foto">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($p->foto_bukti) }}"
                                                style="width:20px; height:20px; object-fit:cover; border-radius:5px; border:1.5px solid white; box-shadow:0 1px 3px rgba(0,0,0,0.15);">
                                        </a>
                                    @endif
                                </div>

                                @if ($namaAnggotaLain->isNotEmpty())
                                    <table style="width:100%; border-collapse:collapse; margin-top:0.4rem;">
                                        @foreach ($namaAnggotaLain as $i => $nama)
                                            <tr>
                                                <td
                                                    style="padding:0.1rem 0.35rem 0.1rem 0; font-size:0.68rem; color:#002f45; opacity:0.4; width:16px; vertical-align:top;">
                                                    {{ $i + 1 }}.</td>
                                                <td
                                                    style="padding:0.1rem 0; font-size:0.7rem; color:#002f45; opacity:0.65;">
                                                    {{ $nama }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <div
                                        style="font-size:0.7rem; color:#002f45; opacity:0.4; margin-top:0.3rem; font-style:italic;">
                                        mengumpulkan sendiri (tidak ada yang nitip)
                                    </div>
                                @endif

                                <div
                                    style="font-size:0.65rem; color:#002f45; opacity:0.4; margin-top:0.35rem; font-weight:700;">
                                    {{ $p->jumlah_anggota }} orang tercakup
                                </div>
                            </td>
                            @foreach ($barangsIndividu as $b)
                                @php
                                    $dibawa = $p->jumlahDibawaUntuk($b->id);
                                    $target = $p->targetUntuk($b);
                                    $lengkap = $dibawa >= $target;

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
                                <td
                                    style="padding:0.6rem 0.5rem; text-align:center; background:{{ $cellBg }}; border-left:1px solid rgba(0,47,69,0.04); vertical-align:middle;">
                                    <span
                                        style="color:{{ $statusColor }}; font-weight:800; font-size:0.85rem;">{{ $dibawa }}/{{ $target }}</span>
                                </td>
                            @endforeach
                            <td
                                style="padding:0.6rem 0.75rem; text-align:center; border-left:1px solid rgba(0,47,69,0.04); vertical-align:middle;">
                                @if ($canEdit)
                                    <form action="{{ route('panitia.p3k.kolektif.validasi', $p->id) }}"
                                        method="POST">
                                        @csrf
                                        @if ($p->is_validated)
                                            <button type="submit" title="Sudah di-ACC — klik untuk batal"
                                                style="background:#16a34a; color:white; border:none; padding: 0.35rem 0.7rem; border-radius:6px; font-size:0.7rem; font-weight:700; cursor:pointer; text-transform: uppercase; letter-spacing: 0.05em;">
                                                ✓ SUDAH ACC
                                            </button>
                                        @else
                                            <button type="submit" title="Klik untuk ACC"
                                                style="background: rgba(0,47,69,0.08); color:#002f45; border:1px solid rgba(0,47,69,0.15); padding: 0.35rem 0.7rem; border-radius:6px; font-size:0.7rem; font-weight:700; cursor:pointer; text-transform: uppercase; letter-spacing: 0.05em;">
                                                BELUM ACC
                                            </button>
                                        @endif
                                    </form>
                                @else
                                    <span
                                        style="background: {{ $p->is_validated ? 'rgba(34,197,94,0.15)' : 'rgba(0,47,69,0.08)' }}; color: {{ $p->is_validated ? '#16a34a' : '#002f45' }}; padding: 0.35rem 0.7rem; border-radius:6px; font-size:0.7rem; font-weight:700;">
                                        {{ $p->is_validated ? '✓ SUDAH ACC' : 'BELUM ACC' }}
                                    </span>
                                @endif

                                @if ($p->is_validated && $p->updatedBy)
                                    <div style="font-size:0.62rem; color:#002f45; opacity:0.45; margin-top:0.35rem;">
                                        oleh {{ $p->updatedBy->name }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div
        style="display:flex; gap:1.25rem; flex-wrap:wrap; margin:-0.75rem 0 1.5rem 0.25rem; font-size:0.7rem; color:#002f45; opacity:0.55;">
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span
                style="display:inline-block; width:10px; height:10px; border-radius:3px; background:rgba(34,197,94,0.5);"></span>
            Lengkap
        </div>
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span
                style="display:inline-block; width:10px; height:10px; border-radius:3px; background:rgba(245,158,11,0.5);"></span>
            Sebagian
        </div>
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span
                style="display:inline-block; width:10px; height:10px; border-radius:3px; background:rgba(239,68,68,0.25);"></span>
            Belum
        </div>
    </div>
@endif
