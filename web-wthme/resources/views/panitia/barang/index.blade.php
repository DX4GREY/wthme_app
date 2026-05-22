@extends('layouts.app')

@section('content')
    {{-- Background Wrapper dengan Gradient --}}
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
        <div style="max-width:1000px; margin:0 auto;">
            
            {{-- Back Link --}}
            <a href="{{ route('panitia.index') }}"
                style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.9rem; font-weight:600; display:inline-flex; align-items:center; margin-bottom:2rem; transition: 0.3s;">
                <span style="margin-right:8px;">←</span> Kembali ke Dashboard
            </a>

            {{-- Header Section (Glass Card) --}}
            <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem; box-shadow: 0 8px 32px rgba(0, 47, 69, 0.1);">
                <div>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                        📦 Pengumpulan Barang
                    </h1>
                    <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-top:0.5rem; font-weight:500;">
                        Pantau status logistik per kelompok secara real-time
                    </p>
                </div>
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    @if (auth()->user()->role === 'admin' || strtolower(auth()->user()->divisi ?? '') === 'LOGISTIK')
                        <a href="{{ route('panitia.barang.manage') }}"
                            style="text-decoration:none; background: rgba(255, 255, 255, 0.5); color:#002f45; border:1px solid rgba(0, 47, 69, 0.2); padding:0.75rem 1.25rem; border-radius:1rem; font-size:0.85rem; font-weight:700; backdrop-filter: blur(5px); transition: 0.3s;">
                            ⚙️ Kelola Barang
                        </a>
                    @endif
                    <a href="{{ route('panitia.barang.rekap') }}"
                        style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.75rem 1.25rem; border-radius:1rem; font-size:0.85rem; font-weight:700; backdrop-filter: blur(5px); transition: 0.3s; box-shadow: 0 4px 15px rgba(0, 47, 69, 0.2);">
                        📊 Rekap Seluruh
                    </a>
                </div>
            </div>

            {{-- Summary Stats (Glass Pills) --}}
            @php
                $totalBarang = $barangs->count();
                $totalKelompok = $kelompoks->count();
                $totalLengkap = collect($summary)->filter(fn($s) => $s['lengkap'] == $s['total'] && $s['total'] > 0)->count();
            @endphp
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-bottom:2.5rem;">
                <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                    <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Jenis Barang</div>
                    <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalBarang }}</div>
                </div>
                <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                    <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Total Kelompok</div>
                    <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalKelompok }}</div>
                </div>
                <div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; padding: 1.5rem; text-align: center;">
                    <div style="color:#bdd1d3; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem; font-weight:600;">Sudah Lengkap</div>
                    <div style="color:#d2c296; font-size:2.25rem; font-weight:800;">{{ $totalLengkap }}</div>
                </div>
            </div>

            {{-- Grid Kelompok --}}
            @if ($kelompoks->isEmpty())
                <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius:1.5rem; padding:4rem; text-align:center; border:2px dashed rgba(0, 47, 69, 0.2);">
                    <div style="font-size:3.5rem; margin-bottom:1rem; opacity:0.5;">👥</div>
                    <p style="color:#002f45; font-weight:600; opacity:0.6;">Belum ada data kelompok peserta.</p>
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1.5rem;">
                    @foreach ($kelompoks as $k)
                        @php
                            $s = $summary[$k];
                            $pct = $s['total'] > 0 ? round(($s['lengkap'] / $s['total']) * 100) : 0;
                            $allDone = $s['lengkap'] == $s['total'] && $s['total'] > 0;
                        @endphp
                        <a href="{{ route('panitia.barang.kelompok', $k) }}"
                            style="text-decoration:none; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); 
                                   border: 1px solid {{ $allDone ? 'rgba(22, 163, 74, 0.4)' : 'rgba(255, 255, 255, 0.4)' }};
                                   border-radius:1.5rem; padding:1.5rem; display:block; transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);"
                            onmouseover="this.style.transform='translateY(-8px)'; this.style.background='rgba(255, 255, 255, 0.4)'; this.style.boxShadow='0 15px 30px rgba(0,47,69,0.1)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.background='rgba(255, 255, 255, 0.25)'; this.style.boxShadow='none';">

                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                <div style="font-size:1.75rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">{{ $allDone ? '✅' : '📦' }}</div>
                                @if ($allDone)
                                    <span style="background: rgba(34, 197, 94, 0.2); color:#166534; border: 1px solid rgba(34, 197, 94, 0.3); font-size:0.65rem; font-weight:800; padding:0.25rem 0.6rem; border-radius:8px; text-transform:uppercase; letter-spacing:0.5px;">Lengkap</span>
                                @endif
                            </div>

                            <div style="color:#002f45; font-weight:800; font-size:1.15rem; margin-bottom:0.35rem;">
                                Kelompok {{ $k }}
                            </div>
                            <div style="color:#002f45; opacity:0.6; font-size:0.85rem; margin-bottom:1rem; font-weight:500;">
                                {{ $s['lengkap'] }} dari {{ $s['total'] }} barang
                            </div>

                            {{-- Progress Bar Container --}}
                            <div style="background: rgba(0, 47, 69, 0.05); border-radius:999px; height:8px; overflow:hidden; border: 1px solid rgba(255,255,255,0.3);">
                                <div
                                    style="background: {{ $allDone ? '#16a34a' : ($pct > 50 ? '#d97706' : '#dc2626') }};
                                           height:100%; border-radius:999px; width:{{ $pct }}%; transition:width 0.6s ease-out; box-shadow: 0 0 10px {{ $allDone ? 'rgba(22, 163, 74, 0.2)' : 'rgba(0,0,0,0)' }};">
                                </div>
                            </div>
                            <div style="color:#002f45; font-weight:700; opacity:0.5; font-size:0.75rem; margin-top:0.5rem; text-align:right;">
                                {{ $pct }}%
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
@endsection