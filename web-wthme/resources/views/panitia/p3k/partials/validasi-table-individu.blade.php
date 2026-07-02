@php
    $totalBarangIndividu = $barangsIndividu->count();
    $lengkapBarangIndividu = $summaryIndividuKelompok->where('is_lengkap', true)->count();
    $persen = $totalBarangIndividu > 0 ? round(($lengkapBarangIndividu / $totalBarangIndividu) * 100) : 0;

    // Mapping menu → divisi yang berwenang, harus konsisten dengan
    // P3kBarangController::MENU_DIVISI (divisi di DB: LOGISTIK, KONSUM, P3K)
    $menuDivisi = [
        'logistik' => 'LOGISTIK',
        'konsumsi' => 'KONSUM',
        'p3k'      => 'P3K',
    ];

    $divisiUser = strtoupper(auth()->user()->divisi ?? '');
    $divisiDibutuhkan = $menuDivisi[$menu ?? ''] ?? null;

    $canEdit = auth()->user()->role === 'admin' || ($divisiDibutuhkan !== null && $divisiUser === $divisiDibutuhkan);
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
    style="background: {{ $anggotaBelumTercakup->isNotEmpty() ? 'rgba(239,68,68,0.1)' : 'rgba(34,197,94,0.1)' }}; border:1px solid {{ $anggotaBelumTercakup->isNotEmpty() ? 'rgba(239,68,68,0.2)' : 'rgba(34,197,94,0.2)' }}; border-radius:1.25rem; padding:1rem 1.5rem; margin-bottom:2rem;">
    @if ($anggotaBelumTercakup->isEmpty())
        <span style="color:#166534; font-weight:700; font-size:0.85rem;">✅ Semua anggota kelompok ini sudah
            tercakup di pengumpulan manapun.</span>
    @else
        <div style="color:#991b1b; font-weight:800; font-size:0.85rem; margin-bottom:0.5rem;">⚠️
            {{ $anggotaBelumTercakup->count() }} anggota BELUM tercakup di pengumpulan manapun:</div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            @foreach ($anggotaBelumTercakup as $p)
                <span
                    style="background:rgba(255,255,255,0.6); color:#991b1b; font-size:0.78rem; font-weight:700; padding:0.35rem 0.75rem; border-radius:999px;">{{ $p->name }}</span>
            @endforeach
        </div>
    @endif
</div>

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
                            $namaAnggotaLain = $p->anggota->pluck('peserta.name')
                                ->reject(fn($n) => $n === $p->perwakilan->name)
                                ->values();
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(0,47,69,0.05);">
                            <td
                                style="padding:0.9rem 1.25rem; color:#002f45; position:sticky; left:0; background:rgba(255,255,255,0.92); z-index:5;">
                                <div style="display:flex; align-items:center; gap:0.4rem; font-weight:800; font-size:0.82rem;">
                                    {{ $p->perwakilan->name }}
                                    <span style="font-size:0.62rem; font-weight:700; color:#002f45; opacity:0.45; background:rgba(0,47,69,0.06); padding:0.1rem 0.4rem; border-radius:4px;">Perwakilan</span>
                                    @if ($p->foto_bukti)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($p->foto_bukti) }}" target="_blank" title="Lihat bukti foto">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($p->foto_bukti) }}"
                                                style="width:20px; height:20px; object-fit:cover; border-radius:5px; border:1.5px solid white; box-shadow:0 1px 3px rgba(0,0,0,0.15);">
                                        </a>
                                    @endif
                                </div>

                                @if ($namaAnggotaLain->isNotEmpty())
                                    <table style="width:100%; border-collapse:collapse; margin-top:0.4rem;">
                                        @foreach ($namaAnggotaLain as $i => $nama)
                                            <tr>
                                                <td style="padding:0.1rem 0.35rem 0.1rem 0; font-size:0.68rem; color:#002f45; opacity:0.4; width:16px; vertical-align:top;">{{ $i + 1 }}.</td>
                                                <td style="padding:0.1rem 0; font-size:0.7rem; color:#002f45; opacity:0.65;">{{ $nama }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <div style="font-size:0.7rem; color:#002f45; opacity:0.4; margin-top:0.3rem; font-style:italic;">
                                        mengumpulkan sendiri (tidak ada yang nitip)
                                    </div>
                                @endif

                                <div style="font-size:0.65rem; color:#002f45; opacity:0.4; margin-top:0.35rem; font-weight:700;">
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
                                    <span style="color:{{ $statusColor }}; font-weight:800; font-size:0.85rem;">{{ $dibawa }}/{{ $target }}</span>
                                </td>
                            @endforeach
                            <td
                                style="padding:0.6rem 0.75rem; text-align:center; border-left:1px solid rgba(0,47,69,0.04); vertical-align:middle;">
                                @if ($canEdit)
                                    <form action="{{ route('panitia.p3k.kolektif.validasi', $p->id) }}" method="POST">
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