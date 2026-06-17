{{-- Partial: status pengumpulan kolektif barang individu (ringkasan di halaman utama) --}}
@if(!$pengumpulanSaya)
<div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:1.5rem; padding:2rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.25); margin-bottom:2rem;">
    <div style="font-size:2rem; margin-bottom:0.5rem;">🎒</div>
    <p style="color:#002f45; font-weight:700; margin-bottom:0.4rem;">Anda belum terdaftar di pengumpulan manapun.</p>
    <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin-bottom:1.5rem;">
        Barang individu sekarang dikumpulkan lewat satu perwakilan per kelompok — Anda bisa jadi perwakilan (mengumpulkan untuk diri sendiri dan/atau rekan yang nitip), atau menitipkan ke perwakilan lain.
    </p>
    <a href="{{ route('peserta.p3k.individu') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#d2c296; text-decoration:none; padding:0.85rem 1.75rem; border-radius:0.85rem; font-size:0.9rem; font-weight:800;">
        📝 Buka Form Pengumpulan
    </a>
</div>
@else
@php
    $sudahAcc = $pengumpulanSaya->is_validated;
@endphp
<div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:1.5rem; overflow:hidden; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom:2rem;">

    <div style="padding:1.5rem 1.5rem 1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
        <div>
            @if($isPerwakilanSaya)
                <span style="background: rgba(0, 47, 69, 0.1); color:#002f45; font-size:0.75rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:999px;">🧑‍🤝‍🧑 Anda Perwakilan Pengumpulan ({{ $pengumpulanSaya->jumlah_anggota }} orang)</span>
            @else
                <span style="background: rgba(0, 47, 69, 0.1); color:#002f45; font-size:0.75rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:999px;">📦 Dititipkan ke {{ $pengumpulanSaya->perwakilan->name }}</span>
            @endif
        </div>
        <div>
            @if($sudahAcc)
                <span style="background: rgba(34, 197, 94, 0.15); color:#166534; font-size:0.75rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:999px;">🛡️ Sudah di-ACC P3K</span>
            @else
                <span style="background: rgba(245, 158, 11, 0.15); color:#92400e; font-size:0.75rem; font-weight:800; padding:0.4rem 0.8rem; border-radius:999px;">Menunggu ACC P3K</span>
            @endif
        </div>
    </div>

    @if($dataIndividu->isEmpty())
        <p style="color:#002f45; opacity:0.5; font-size:0.85rem; text-align:center; padding:1rem 1.5rem 1.5rem;">Belum ada daftar barang individu.</p>
    @else
    <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
        <thead>
            <tr style="background: rgba(0, 47, 69, 0.05);">
                <th style="padding:0.85rem 1.5rem; text-align:left; color:#002f45;">Nama Barang</th>
                <th style="padding:0.85rem; text-align:center; color:#002f45;">Target Kami</th>
                <th style="padding:0.85rem; text-align:center; color:#002f45;">Sudah Dibawa</th>
                <th style="padding:0.85rem; text-align:center; color:#002f45;">Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($dataIndividu as $item)
        <tr style="border-bottom:1px solid rgba(0,47,69,0.05);">
            <td style="padding:0.85rem 1.5rem; color:#002f45; font-weight:700;">{{ $item['barang']->nama_barang }}</td>
            <td style="padding:0.85rem; text-align:center; color:#002f45; opacity:0.7;">{{ $item['target'] }} {{ $item['barang']->satuan }}</td>
            <td style="padding:0.85rem; text-align:center; color:#002f45; font-weight:800;">{{ $item['jumlah_dibawa'] }}</td>
            <td style="padding:0.85rem; text-align:center;">
                @if($item['is_lengkap'])
                    <span style="color:#15803d; font-weight:800;">✓ Lengkap</span>
                @else
                    <span style="color:#b45309; font-weight:800;">Kurang</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif

    <div style="padding:1.25rem 1.5rem; border-top:1px solid rgba(0,47,69,0.05);">
        <a href="{{ route('peserta.p3k.individu') }}"
           style="display:inline-flex; align-items:center; gap:0.5rem; background:#002f45; color:#d2c296; text-decoration:none; padding:0.7rem 1.5rem; border-radius:0.75rem; font-size:0.85rem; font-weight:800;">
            {{ $isPerwakilanSaya ? '⚙️ Kelola Pengumpulan' : '🔍 Lihat Detail' }}
        </a>
    </div>
</div>
@endif
