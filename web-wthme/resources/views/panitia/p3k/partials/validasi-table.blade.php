{{-- Partial: tabel validasi barang P3K (panitia) --}}
@php
    $totalBarang = $data->count();
    $barangLengkap = $data->where('is_lengkap', true)->count();
    $persen = $totalBarang > 0 ? round(($barangLengkap / $totalBarang) * 100) : 0;
@endphp

<div
    style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.25rem 2rem; margin-bottom: 1rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
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
        <div style="color:#bdd1d3; font-size:0.75rem; margin-top:0.4rem;">{{ $barangLengkap }} dari {{ $totalBarang }}
            barang lengkap</div>
    </div>
</div>

<div
    style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07); margin-bottom:1.5rem;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
                <tr style="background: rgba(0, 47, 69, 0.05);">
                    <th
                        style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                        Barang</th>
                    <th
                        style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                        Target</th>
                    <th
                        style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                        Terkumpul</th>
                    @if ($withTerpakai)
                        <th
                            style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                            Terpakai</th>
                        <th
                            style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                            Sisa</th>
                    @endif
                    <th
                        style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                        Bukti</th>
                    <th
                        style="padding:1rem; text-align:center; color:#002f45; font-size:0.7rem; text-transform:uppercase;">
                        Status Validasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    @php
                        $b = $item['barang'];
                        $terkumpul = $item['jumlah_terkumpul'];
                        $lengkap = $item['is_lengkap'];
                        $isValidated = $item['is_validated'];
                    @endphp
                    <tr
                        style="border-bottom: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, {{ $isValidated ? '0.25' : '0.05' }});">
                        <td style="padding:1rem 1.5rem;">
                            <div style="color:#002f45; font-weight:700;">{{ $b->nama_barang }}</div>
                            @if ($b->keterangan)
                                <div style="color:#002f45; opacity:0.5; font-size:0.75rem; font-style:italic;">
                                    {{ $b->keterangan }}</div>
                            @endif
                            @if ($item['updated_by_name'])
                                <div style="color:#002f45; opacity:0.4; font-size:0.65rem; margin-top:0.4rem;">👤
                                    {{ $item['updated_by_name'] }}</div>
                            @endif
                        </td>
                        <td style="padding:1rem; text-align:center;">
                            <div style="color:#002f45; font-weight:700;">{{ $b->jumlah_kebutuhan }}</div>
                            <div style="color:#002f45; opacity:0.5; font-size:0.7rem; text-transform:uppercase;">
                                {{ $b->satuan }}</div>
                        </td>
                        <td style="padding:1rem; text-align:center;">
                            <div
                                style="font-size:1.1rem; font-weight:800; color:{{ $lengkap ? '#16a34a' : ($terkumpul > 0 ? '#d97706' : '#dc2626') }};">
                                {{ $terkumpul }}</div>
                            <div style="color:#002f45; opacity:0.5; font-size:0.7rem; text-transform:uppercase;">
                                {{ $b->satuan }}</div>
                        </td>

                        @if ($withTerpakai)
                            <td style="padding:1rem; text-align:center;">
                                <form action="{{ route('panitia.p3k.terpakai', [$b->id, $kelompok]) }}" method="POST"
                                    style="display:flex; gap:0.4rem; justify-content:center; align-items:center;">
                                    @csrf
                                    <input type="number" name="jumlah_terpakai" value="{{ $item['jumlah_terpakai'] }}"
                                        min="0" max="{{ $terkumpul }}"
                                        style="width:55px; padding:0.3rem; border:1px solid rgba(0,47,69,0.2); border-radius:0.5rem; text-align:center; font-weight:700; font-size:0.8rem;">
                                    <button type="submit"
                                        style="background: rgba(0,47,69,0.1); color:#002f45; border:none; padding:0.3rem 0.5rem; border-radius:0.5rem; font-size:0.7rem; cursor:pointer; font-weight:700;">💾</button>
                                </form>
                            </td>
                            <td style="padding:1rem; text-align:center;">
                                <span style="font-weight:800; color:#002f45;">{{ $item['jumlah_sisa'] }}</span>
                                <div style="color:#002f45; opacity:0.5; font-size:0.7rem; text-transform:uppercase;">
                                    {{ $b->satuan }}</div>
                            </td>
                        @endif

                        <td style="padding:1rem; text-align:center;">
                            @if ($item['foto'])
                                <a href="{{ $item['foto'] }}" target="_blank">
                                    <img src="{{ $item['foto'] }}"
                                        style="width:50px; height:50px; object-fit:cover; border-radius:12px; border:2px solid rgba(255,255,255,0.8);">
                                </a>
                            @else
                                <div
                                    style="width:50px; height:50px; background: rgba(0,0,0,0.05); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#002f45; opacity:0.2;">
                                    📷</div>
                            @endif
                        </td>

                        <td style="padding:1rem; text-align:center;">
                            @if ($item['pengumpulan'])
                                <form action="{{ route('panitia.p3k.validasi', [$b->id, $kelompok]) }}" method="POST">
                                    @csrf
                                    @if ($isValidated)
                                        <button type="submit"
                                            style="background: {{ $lengkap ? '#16a34a' : '#d97706' }}; color: white; border: none; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.75rem; font-weight: 800; cursor: pointer; text-transform: uppercase; width: 110px;">
                                            {{ $lengkap ? '✓ Ter-ACC' : 'ACC Cicilan' }}
                                        </button>
                                        {{-- Tampilkan siapa yang ACC — posisi di sini, setelah tombol --}}
                                        @if ($item['updated_by'])
                                            <div
                                                style="font-size:0.62rem; color:#002f45; opacity:0.45; margin-top:0.35rem;">
                                                oleh {{ $item['updated_by'] }}
                                            </div>
                                        @endif
                                    @else
                                        <button type="submit"
                                            style="background: rgba(0, 47, 69, 0.1); color: #002f45; border: 1px solid rgba(0, 47, 69, 0.2); padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-transform: uppercase; width: 110px;">
                                            Belum ACC
                                        </button>
                                    @endif
                                </form>
                            @else
                                <span
                                    style="background: rgba(239, 68, 68, 0.1); color:#991b1b; font-size:0.7rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:10px; text-transform:uppercase;">Belum
                                    Input</span>
                            @endif

                            <div style="margin-top: 0.5rem;">
                                @if ($lengkap)
                                    <span style="color:#166534; font-size:0.65rem; font-weight:700;">(Target
                                        Terpenuhi)</span>
                                @elseif($terkumpul > 0)
                                    <span style="color:#92400e; font-size:0.65rem; font-weight:700;">(Kurang
                                        {{ $b->jumlah_kebutuhan - $terkumpul }})</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
