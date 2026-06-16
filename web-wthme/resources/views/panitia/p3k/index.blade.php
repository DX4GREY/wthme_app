@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1000px; margin:0 auto;">

        <a href="{{ route('panitia.index') }}"
            style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; font-weight:600; display:inline-flex; align-items:center; margin-bottom:2rem;">
            <span style="margin-right:8px;">←</span> Kembali ke Dashboard
        </a>

        {{-- Header --}}
        <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem; box-shadow: 0 8px 32px rgba(0, 47, 69, 0.1);">
            <div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                    🩹 Pengumpulan Barang P3K
                </h1>
                <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-top:0.5rem; font-weight:500;">
                    Pantau status logistik medis & obat pribadi per kelompok
                </p>
            </div>
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                @if(auth()->user()->role === 'admin' || strtoupper(auth()->user()->divisi ?? '') === 'P3K')
                    <a href="{{ route('panitia.p3k.manage') }}"
                        style="text-decoration:none; background: rgba(255, 255, 255, 0.5); color:#002f45; border:1px solid rgba(0, 47, 69, 0.2); padding:0.75rem 1.25rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">
                        ⚙️ Kelola Barang & PJ
                    </a>
                @endif
                @if(auth()->user()->role === 'admin' || auth()->user()->isKorlap())
                <a href="{{ route('panitia.p3k.rekap') }}"
                    style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.75rem 1.25rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">
                    📊 Rekap Seluruh
                </a>
                @endif
            </div>
        </div>

        {{-- Summary Stats --}}
        @php
            $totalBarang = $barangsKelompok->count() + $barangsIndividu->count();
            $totalKelompok = $kelompoks->count();
            $totalLengkap = collect($summary)->filter(fn($s) => $s['lengkap'] == $s['total'] && $s['total'] > 0)->count();
            $totalObatBelum = collect($summary)->sum('obat_belum');
        @endphp
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; margin-bottom:2.5rem;">
            <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Jenis Barang</div>
                <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalBarang }}</div>
            </div>
            <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Kelompok</div>
                <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalKelompok }}</div>
            </div>
            <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Sudah Lengkap</div>
                <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalLengkap }}</div>
            </div>
            <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Obat Belum Terima</div>
                <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalObatBelum }}</div>
            </div>
        </div>

        {{-- Grid Kelompok --}}
        @if ($kelompoks->isEmpty())
            <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius:1.5rem; padding:4rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2);">
                <div style="font-size:3.5rem; margin-bottom:1rem; opacity:0.5;">👥</div>
                <p style="color:#002f45; font-weight:600; opacity:0.6;">
                    @if(auth()->user()->role !== 'admin' && !auth()->user()->isKorlap())
                        Belum ada kelompok yang menjadi tanggung jawab Anda. Hubungi koordinator P3K untuk pengaturan PJ.
                    @else
                        Belum ada data kelompok peserta.
                    @endif
                </p>
            </div>
        @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1.5rem;">
                @foreach ($kelompoks as $k)
                    @php
                        $s = $summary[$k];
                        $pct = $s['total'] > 0 ? round(($s['lengkap'] / $s['total']) * 100) : 0;
                        $allDone = $s['lengkap'] == $s['total'] && $s['total'] > 0;
                    @endphp
                    <a href="{{ route('panitia.p3k.kelompok', $k) }}"
                        style="text-decoration:none; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(10px);
                               border: 1px solid {{ $allDone ? 'rgba(22, 163, 74, 0.4)' : 'rgba(255, 255, 255, 0.4)' }};
                               border-radius:1.5rem; padding:1.5rem; display:block; transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);"
                        onmouseover="this.style.transform='translateY(-8px)'; this.style.background='rgba(255, 255, 255, 0.4)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.background='rgba(255, 255, 255, 0.25)';">

                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                            <div style="font-size:1.75rem;">{{ $allDone ? '✅' : '🩹' }}</div>
                            @if ($s['obat_belum'] > 0)
                                <span style="background: rgba(245, 158, 11, 0.2); color:#92400e; border: 1px solid rgba(245, 158, 11, 0.3); font-size:0.65rem; font-weight:800; padding:0.25rem 0.6rem; border-radius:8px; text-transform:uppercase;">{{ $s['obat_belum'] }} Obat</span>
                            @elseif ($allDone)
                                <span style="background: rgba(34, 197, 94, 0.2); color:#166534; border: 1px solid rgba(34, 197, 94, 0.3); font-size:0.65rem; font-weight:800; padding:0.25rem 0.6rem; border-radius:8px; text-transform:uppercase;">Lengkap</span>
                            @endif
                        </div>

                        <div style="color:#002f45; font-weight:800; font-size:1.15rem; margin-bottom:0.35rem;">
                            Kelompok {{ $k }}
                        </div>
                        <div style="color:#002f45; opacity:0.6; font-size:0.85rem; margin-bottom:1rem; font-weight:500;">
                            {{ $s['lengkap'] }} dari {{ $s['total'] }} barang &middot; {{ $s['obat_total'] }} obat pribadi
                        </div>

                        <div style="background: rgba(0, 47, 69, 0.05); border-radius:999px; height:8px; overflow:hidden; border: 1px solid rgba(255,255,255,0.3);">
                            <div style="background: {{ $allDone ? '#16a34a' : ($pct > 50 ? '#d97706' : '#dc2626') }};
                                       height:100%; border-radius:999px; width:{{ $pct }}%; transition:width 0.6s ease-out;">
                            </div>
                        </div>
                        <div style="color:#002f45; font-weight:700; opacity:0.5; font-size:0.75rem; margin-top:0.5rem; text-align:right;">
                            {{ $pct }}%
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Stok Global Barang Individu — read-only, kontrol ada di halaman per kelompok --}}
        @if($stokIndividu->isNotEmpty())
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin:2.5rem 0 1rem; padding-left:0.5rem;">📊 Stok Global Barang Individu</h3>
        <p style="color:#002f45; opacity:0.5; font-size:0.8rem; margin-top:-0.6rem; margin-bottom:1.25rem; padding-left:0.5rem;">
            Rekap agregat lintas semua kelompok. Untuk mengatur terpakai, buka halaman masing-masing kelompok.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem;">
            @foreach($stokIndividu as $s)
            @php
                $b = $s['barang'];
                $pctSisa = $s['total_terkumpul'] > 0 ? round(($s['total_sisa'] / $s['total_terkumpul']) * 100) : 0;
                $colorSisa = $s['total_sisa'] > 0 ? '#16a34a' : ($s['total_terkumpul'] > 0 ? '#dc2626' : '#94a3b8');
            @endphp
            <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.25rem; padding: 1.25rem 1.5rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                    <span style="color:#002f45; font-weight:800; font-size:0.95rem;">{{ $b->nama_barang }}</span>
                    <span style="background:rgba(0,47,69,0.06); color:#002f45; opacity:0.45; font-size:0.6rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:6px; text-transform:uppercase; letter-spacing:0.04em;">Global</span>
                </div>

                <div style="display:flex; justify-content:space-between; gap:0.75rem; margin-bottom:0.85rem;">
                    <div style="text-align:center; flex:1;">
                        <div style="color:#002f45; opacity:0.45; font-size:0.62rem; text-transform:uppercase; letter-spacing:0.04em;">Terkumpul</div>
                        <div style="color:#002f45; font-size:1.3rem; font-weight:800;">{{ $s['total_terkumpul'] }}</div>
                        <div style="color:#002f45; opacity:0.3; font-size:0.6rem;">{{ $b->satuan }}</div>
                    </div>
                    <div style="text-align:center; flex:1; border-left:1px solid rgba(0,47,69,0.08); border-right:1px solid rgba(0,47,69,0.08);">
                        <div style="color:#002f45; opacity:0.45; font-size:0.62rem; text-transform:uppercase; letter-spacing:0.04em;">Terpakai</div>
                        <div style="color:#d97706; font-size:1.3rem; font-weight:800;">{{ $s['total_terpakai'] }}</div>
                        <div style="color:#002f45; opacity:0.3; font-size:0.6rem;">{{ $b->satuan }}</div>
                    </div>
                    <div style="text-align:center; flex:1;">
                        <div style="color:#002f45; opacity:0.45; font-size:0.62rem; text-transform:uppercase; letter-spacing:0.04em;">Sisa</div>
                        <div style="color:{{ $colorSisa }}; font-size:1.3rem; font-weight:800;">{{ $s['total_sisa'] }}</div>
                        <div style="color:#002f45; opacity:0.3; font-size:0.6rem;">{{ $b->satuan }}</div>
                    </div>
                </div>

                <div style="background: rgba(0,47,69,0.06); border-radius:999px; height:8px; overflow:hidden;">
                    <div style="background:{{ $colorSisa }}; height:100%; border-radius:999px; width:{{ $pctSisa }}%; transition:width 0.4s ease;"></div>
                </div>
                <div style="color:#002f45; opacity:0.35; font-size:0.65rem; margin-top:0.4rem; text-align:right;">
                    {{ $pctSisa }}% sisa dari total terkumpul
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>
@endsection
