{{-- Partial: Obat Pribadi Peserta (panitia) — dikelompokkan per peserta, dengan summary --}}
@php
    $totalObat = $obatPribadi->count();
    $sudahDiserahkan = $obatPribadi->where('sudah_diserahkan', true)->count();
    $belumDiserahkan = $totalObat - $sudahDiserahkan;
    $persenObat = $totalObat > 0 ? round(($sudahDiserahkan / $totalObat) * 100) : 0;

    // Group by peserta, urutkan: peserta dengan obat belum diterima duluan
    $grouped = $obatPribadi->groupBy('user_id')->sortBy(function ($items) {
        return $items->where('sudah_diserahkan', false)->count() > 0 ? 0 : 1;
    });
@endphp

{{-- Summary Bar --}}
<div style="background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1.25rem 2rem; margin-bottom: 1rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
    <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
        <div style="text-align:center;">
            <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em;">Total Lapor</div>
            <div style="color:#d2c296; font-size:1.75rem; font-weight:800; line-height:1;">{{ $totalObat }}</div>
        </div>
        <div style="text-align:center;">
            <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em;">Sudah Diterima</div>
            <div style="color:#86efac; font-size:1.75rem; font-weight:800; line-height:1;">{{ $sudahDiserahkan }}</div>
        </div>
        <div style="text-align:center;">
            <div style="color:#bdd1d3; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em;">Belum Diterima</div>
            <div style="color:#fcd34d; font-size:1.75rem; font-weight:800; line-height:1;">{{ $belumDiserahkan }}</div>
        </div>
    </div>
    <div style="flex:1; min-width:200px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:0.4rem;">
            <span style="color:white; font-size:0.8rem; font-weight:600;">Progress Penerimaan</span>
            <span style="color:#d2c296; font-size:0.8rem; font-weight:800;">{{ $persenObat }}%</span>
        </div>
        <div style="background: rgba(255,255,255,0.1); border-radius:999px; height:10px; overflow:hidden;">
            <div style="background: linear-gradient(90deg, #d2c296, #86efac); height:100%; border-radius:999px; width:{{ $persenObat }}%;"></div>
        </div>
    </div>
</div>

{{-- List per peserta --}}
<div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);">
    @if($obatPribadi->isEmpty())
    <div style="padding:3rem 2rem; text-align:center;">
        <div style="font-size:2.5rem; margin-bottom:0.75rem; opacity:0.4;">💊</div>
        <p style="color:#002f45; opacity:0.5; font-weight:600; margin:0;">Belum ada peserta yang melaporkan obat pribadi.</p>
    </div>
    @else
    <div>
        @foreach($grouped as $userId => $items)
        @php
            $peserta = $items->first()->peserta;
            $jumlahBelum = $items->where('sudah_diserahkan', false)->count();
            $jumlahTotal = $items->count();
            $semuaSelesai = $jumlahBelum === 0;
        @endphp
        <div style="border-bottom: 1px solid rgba(0,47,69,0.06);">

            {{-- Header peserta --}}
            <div style="padding:1.1rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; background: {{ $semuaSelesai ? 'rgba(34,197,94,0.04)' : 'rgba(245,158,11,0.04)' }};">
                <div style="display:flex; align-items:center; gap:0.85rem;">
                    <div style="width:40px; height:40px; border-radius:50%; background:{{ $semuaSelesai ? 'rgba(34,197,94,0.15)' : 'rgba(245,158,11,0.15)' }}; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">
                        {{ $semuaSelesai ? '✅' : '💊' }}
                    </div>
                    <div>
                        <div style="color:#002f45; font-weight:800; font-size:0.95rem;">{{ $peserta->name ?? 'Peserta tidak ditemukan' }}</div>
                        <div style="color:#002f45; opacity:0.5; font-size:0.75rem;">
                            {{ $jumlahTotal }} item dilaporkan
                            @if(!$semuaSelesai)
                                &middot; <span style="color:#b45309; font-weight:700;">{{ $jumlahBelum }} belum diterima</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($semuaSelesai)
                <span style="background: rgba(34,197,94,0.15); color:#166534; font-size:0.65rem; font-weight:800; padding:0.35rem 0.85rem; border-radius:999px; text-transform:uppercase; letter-spacing:0.05em;">
                    Semua Diterima
                </span>
                @else
                <span style="background: rgba(245,158,11,0.15); color:#92400e; font-size:0.65rem; font-weight:800; padding:0.35rem 0.85rem; border-radius:999px; text-transform:uppercase; letter-spacing:0.05em;">
                    Perlu Tindak Lanjut
                </span>
                @endif
            </div>

            {{-- Item-item obat milik peserta ini --}}
            <div style="padding:0 1.5rem 1.1rem 1.5rem; display:flex; flex-direction:column; gap:0.6rem;">
                @foreach($items as $o)
                <div style="background: rgba(255,255,255,0.5); border: 1px solid rgba(0,47,69,0.06); border-radius:1rem; padding:0.85rem 1.1rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">

                    {{-- Foto bukti (jika ada) --}}
                    @if($o->foto_bukti)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($o->foto_bukti) }}" target="_blank" style="flex-shrink:0;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($o->foto_bukti) }}" style="width:44px; height:44px; object-fit:cover; border-radius:0.6rem; border:2px solid white; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                    </a>
                    @else
                    <div style="width:44px; height:44px; background:rgba(0,47,69,0.04); border-radius:0.6rem; display:flex; align-items:center; justify-content:center; font-size:1.1rem; opacity:0.3; flex-shrink:0;">📋</div>
                    @endif

                    {{-- Detail --}}
                    <div style="flex:1; min-width:180px;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.2rem; flex-wrap:wrap;">
                            <span style="color:#002f45; font-weight:800; font-size:0.9rem;">{{ $o->penyakit }}</span>
                            @if($o->nama_obat)
                            <span style="color:#002f45; opacity:0.45; font-size:0.8rem;">—</span>
                            <span style="color:#002f45; opacity:0.75; font-size:0.85rem; font-weight:600;">{{ $o->nama_obat }}</span>
                            @endif
                        </div>
                        @if($o->catatan)
                        <div style="color:#002f45; opacity:0.5; font-size:0.75rem; font-style:italic;">"{{ $o->catatan }}"</div>
                        @endif
                    </div>

                    {{-- Toggle status --}}
                    <form action="{{ route('panitia.p3k.obat.toggle', $o->id) }}" method="POST" style="flex-shrink:0;">
                        @csrf
                        @if($o->sudah_diserahkan)
                            <button type="submit" style="background:#16a34a; color:white; border:none; padding:0.5rem 1rem; border-radius:0.7rem; font-size:0.7rem; font-weight:800; cursor:pointer; text-transform:uppercase; letter-spacing:0.03em; display:flex; align-items:center; gap:0.35rem; box-shadow:0 2px 8px rgba(22,163,74,0.25);">
                                ✓ Diterima
                            </button>
                        @else
                            <button type="submit" style="background: rgba(0,47,69,0.08); color:#002f45; border:1px solid rgba(0,47,69,0.15); padding:0.5rem 1rem; border-radius:0.7rem; font-size:0.7rem; font-weight:700; cursor:pointer; text-transform:uppercase; letter-spacing:0.03em; transition:0.2s;"
                                onmouseover="this.style.background='#002f45'; this.style.color='#d2c296';"
                                onmouseout="this.style.background='rgba(0,47,69,0.08)'; this.style.color='#002f45';">
                                Tandai Diterima
                            </button>
                        @endif
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
