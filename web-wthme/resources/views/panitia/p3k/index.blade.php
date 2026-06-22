@extends('layouts.app')

@section('content')
<style>
@media (max-width: 640px) {
    .stats-grid { grid-template-columns: repeat(2,1fr) !important; gap: 0.75rem !important; }
    .kelompok-grid { grid-template-columns: repeat(2,1fr) !important; gap: 0.85rem !important; }
    .header-card { flex-direction: column !important; align-items: flex-start !important; }
    .header-actions { flex-direction: row; flex-wrap: wrap; }
    .stok-cards { grid-template-columns: 1fr !important; }
    .page-pad { padding: 1.25rem 1rem !important; }
    .back-link { margin-bottom: 1.25rem !important; }
    h1.main-title { font-size: 1.4rem !important; }
    .tab-btn { padding: 0.5rem 0.85rem !important; font-size: 0.78rem !important; }
}
@media (max-width: 380px) {
    .kelompok-grid { grid-template-columns: 1fr !important; }
}
</style>

<div class="page-pad" style="min-height:calc(100vh - 64px); padding:2rem 1.25rem; background:linear-gradient(135deg,#e0decd 0%,#bdd1d3 100%);">
<div style="max-width:1000px; margin:0 auto;">

    <a href="{{ route('panitia.index') }}" class="back-link"
       style="color:#002f45; opacity:0.7; text-decoration:none; font-size:0.88rem; font-weight:600; display:inline-flex; align-items:center; gap:0.4rem; margin-bottom:1.75rem;">
        ← Kembali ke Dashboard
    </a>

    {{-- Header --}}
    <div class="header-card" style="background:rgba(255,255,255,0.3); backdrop-filter:blur(15px); border:1px solid rgba(255,255,255,0.4); border-radius:1.5rem; padding:1.75rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; box-shadow:0 8px 32px rgba(0,47,69,0.1);">
        <div>
            <h1 class="main-title" style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.85rem; font-weight:800; margin:0;">
                📦 Pengumpulan Barang Peserta
            </h1>
            <p style="color:#002f45; opacity:0.55; font-size:0.88rem; margin-top:0.4rem; font-weight:500;">
                Logistik · Konsumsi · P3K — pantau per kelompok
            </p>
        </div>
        <div class="header-actions" style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            @if(auth()->user()->role === 'admin' || strtoupper(auth()->user()->divisi ?? '') === 'P3K')
            <a href="{{ route('panitia.p3k.manage') }}"
               style="text-decoration:none; background:rgba(255,255,255,0.5); color:#002f45; border:1px solid rgba(0,47,69,0.2); padding:0.65rem 1.1rem; border-radius:1rem; font-size:0.83rem; font-weight:700;">
                ⚙️ Kelola Barang
            </a>
            @endif
            @if(auth()->user()->role === 'admin' || auth()->user()->isKorlap())
            <a href="{{ route('panitia.p3k.rekap') }}"
               style="text-decoration:none; background:rgba(0,47,69,0.85); color:#d2c296; padding:0.65rem 1.1rem; border-radius:1rem; font-size:0.83rem; font-weight:700;">
                📊 Rekap Seluruh
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.2); border:1px solid rgba(34,197,94,0.3); color:#166534; padding:0.85rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem; font-size:0.88rem; font-weight:600;">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.2); color:#991b1b; padding:0.85rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem; font-size:0.88rem; font-weight:600;">⚠️ {{ $errors->first() }}</div>
    @endif

    {{-- Summary Stats --}}
    @php
        $totalBarang  = $barangsKelompok->count() + $barangsIndividu->count();
        $totalKelompok= $kelompoks->count();
        $totalLengkap = collect($summary)->filter(fn($s) => $s['lengkap'] == $s['total'] && $s['total'] > 0)->count();
        $totalObatBelum = collect($summary)->sum('obat_belum');
    @endphp
    <div class="stats-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem;">
        @foreach([['Jenis Barang',$totalBarang],['Kelompok',$totalKelompok],['Sudah Lengkap',$totalLengkap],['Obat Belum Terima',$totalObatBelum]] as [$label,$val])
        <div style="background:rgba(0,47,69,0.8); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:1.25rem; padding:1.25rem 1rem; text-align:center;">
            <div style="color:#bdd1d3; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.4rem; font-weight:600;">{{ $label }}</div>
            <div style="color:#d2c296; font-size:2rem; font-weight:800; line-height:1;">{{ $val }}</div>
        </div>
        @endforeach
    </div>

    {{-- Grid Kelompok --}}
    @if($kelompoks->isEmpty())
    <div style="background:rgba(255,255,255,0.2); backdrop-filter:blur(10px); border-radius:1.5rem; padding:3rem; text-align:center; border:2px dashed rgba(0,47,69,0.2); margin-bottom:2rem;">
        <div style="font-size:3rem; margin-bottom:1rem; opacity:0.5;">👥</div>
        <p style="color:#002f45; font-weight:600; opacity:0.6; font-size:0.9rem;">
            @if(auth()->user()->role !== 'admin' && !auth()->user()->isKorlap())
                Belum ada kelompok yang menjadi tanggung jawab Anda.
            @else
                Belum ada data kelompok peserta.
            @endif
        </p>
    </div>
    @else
    <div class="kelompok-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1.25rem; margin-bottom:2.5rem;">
        @foreach($kelompoks as $k)
        @php $s=$summary[$k]; $pct=$s['total']>0?round(($s['lengkap']/$s['total'])*100):0; $allDone=$s['lengkap']==$s['total']&&$s['total']>0; @endphp
        <a href="{{ route('panitia.p3k.kelompok', $k) }}"
           style="text-decoration:none; background:rgba(255,255,255,0.25); backdrop-filter:blur(10px); border:1px solid {{ $allDone?'rgba(22,163,74,0.4)':'rgba(255,255,255,0.4)' }}; border-radius:1.4rem; padding:1.25rem; display:block;"
           onmouseover="this.style.transform='translateY(-5px)'; this.style.background='rgba(255,255,255,0.4)';"
           onmouseout="this.style.transform=''; this.style.background='rgba(255,255,255,0.25)';">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.85rem;">
                <div style="font-size:1.5rem;">{{ $allDone ? '✅' : '📦' }}</div>
                @if($s['obat_belum']>0)
                <span style="background:rgba(245,158,11,0.2); color:#92400e; border:1px solid rgba(245,158,11,0.3); font-size:0.6rem; font-weight:800; padding:0.2rem 0.5rem; border-radius:6px; text-transform:uppercase;">{{ $s['obat_belum'] }} Obat</span>
                @elseif($allDone)
                <span style="background:rgba(34,197,94,0.2); color:#166534; border:1px solid rgba(34,197,94,0.3); font-size:0.6rem; font-weight:800; padding:0.2rem 0.5rem; border-radius:6px; text-transform:uppercase;">Lengkap</span>
                @endif
            </div>
            <div style="color:#002f45; font-weight:800; font-size:1.05rem; margin-bottom:0.25rem;">Kelompok {{ $k }}</div>
            <div style="color:#002f45; opacity:0.55; font-size:0.78rem; margin-bottom:0.85rem;">{{ $s['lengkap'] }}/{{ $s['total'] }} barang &middot; {{ $s['obat_total'] }} obat</div>
            <div style="background:rgba(0,47,69,0.07); border-radius:999px; height:7px; overflow:hidden;">
                <div style="background:{{ $allDone?'#16a34a':($pct>50?'#d97706':'#dc2626') }}; height:100%; border-radius:999px; width:{{ $pct }}%;"></div>
            </div>
            <div style="color:#002f45; opacity:0.4; font-size:0.7rem; margin-top:0.35rem; text-align:right;">{{ $pct }}%</div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Stok & Pemakaian Barang Individu — CARDS, AJAX --}}
    @php $canEditStok = auth()->user()->role==='admin' || strtoupper(auth()->user()->divisi??'')==='P3K'; @endphp
    @if(!empty($stokIndividuByMenu) && collect($stokIndividuByMenu)->flatten(1)->isNotEmpty())
    @php $menuConfig=['logistik'=>['label'=>'Logistik','icon'=>'🎒'],'konsumsi'=>['label'=>'Konsumsi','icon'=>'🥘'],'p3k'=>['label'=>'P3K','icon'=>'🩹']]; @endphp

    <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.25rem; font-weight:800; margin:0 0 0.4rem; padding-left:0.25rem;">📊 Stok & Pemakaian Barang Individu</h3>
    <p style="color:#002f45; opacity:0.4; font-size:0.75rem; margin:0 0 1.25rem 0.25rem;">Total dari semua kelompok · Klik tombol — langsung tercatat tanpa reload.</p>

    @foreach($stokIndividuByMenu as $menu => $barangsStok)
    @if($barangsStok->isNotEmpty())
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:0.78rem; font-weight:700; color:#002f45; opacity:0.6; margin-bottom:0.5rem; padding-left:0.2rem;">
            {{ $menuConfig[$menu]['icon'] }} {{ $menuConfig[$menu]['label'] }}
        </div>
        <div class="stok-cards" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:1rem;">
        @foreach($barangsStok as $s)
        @php $b=$s['barang']; $sisa=$s['total_sisa']; $terk=$s['total_terkumpul']; $terp=$s['total_terpakai']; $pct=$terk>0?round($sisa/$terk*100):0; $cSisa=$sisa<=0?'#dc2626':($sisa<=$terk*0.25?'#d97706':'#16a34a'); @endphp
        <div style="background:rgba(255,255,255,0.3); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,0.45); border-radius:1.35rem; overflow:hidden;"
             data-barang="{{ $b->id }}" data-terkumpul="{{ $terk }}">

            {{-- Card header --}}
            <div style="padding:0.85rem 1.1rem 0.6rem; background:rgba(0,47,69,0.04); border-bottom:1px solid rgba(0,47,69,0.06);">
                <div style="color:#002f45; font-weight:800; font-size:0.9rem;">{{ $b->nama_barang }}</div>
                <div style="color:#002f45; opacity:0.35; font-size:0.62rem; text-transform:uppercase; letter-spacing:0.04em;">{{ $b->satuan }}</div>
            </div>

            {{-- 3 angka --}}
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; padding:0.85rem 1.1rem 0.65rem; gap:0.5rem;">
                <div style="text-align:center;">
                    <div style="color:#002f45; opacity:0.4; font-size:0.55rem; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">Terkumpul</div>
                    <div class="val-terkumpul" style="color:#002f45; font-weight:800; font-size:1.5rem; line-height:1;">{{ $terk }}</div>
                </div>
                <div style="text-align:center; border-left:1px solid rgba(0,47,69,0.07); border-right:1px solid rgba(0,47,69,0.07);">
                    <div style="color:#92400e; opacity:0.6; font-size:0.55rem; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">Terpakai</div>
                    <div class="val-terpakai" style="color:#d97706; font-weight:800; font-size:1.5rem; line-height:1;">{{ $terp }}</div>
                </div>
                <div style="text-align:center;">
                    <div style="color:{{ $cSisa }}; opacity:0.6; font-size:0.55rem; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.2rem;">Sisa</div>
                    <div class="val-sisa" style="color:{{ $cSisa }}; font-weight:800; font-size:1.5rem; line-height:1;">{{ $sisa }}</div>
                </div>
            </div>

            {{-- Progress bar --}}
            <div style="padding:0 1.1rem 0.7rem;">
                <div style="background:rgba(0,47,69,0.07); border-radius:999px; height:5px; overflow:hidden;">
                    <div class="val-bar" style="background:{{ $cSisa }}; height:100%; border-radius:999px; width:{{ $pct }}%; transition:width 0.3s;"></div>
                </div>
            </div>

            {{-- Kontrol --}}
            @if($canEditStok)
            <div style="padding:0.6rem 1.1rem 0.9rem; border-top:1px solid rgba(0,47,69,0.06); display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap;">
                <button type="button" class="btn-stok" onclick="stokAjax(this)"
                    data-url="{{ route('panitia.p3k.stok.global.adjust', $b->id) }}"
                    data-payload='{"delta":1}'
                    {{ $sisa<=0 ? 'disabled' : '' }}
                    style="flex:1; min-width:60px; padding:0.45rem 0.5rem; border-radius:0.6rem; font-size:0.75rem; font-weight:800; cursor:{{ $sisa<=0?'not-allowed':'pointer' }}; border:1px solid {{ $sisa<=0?'rgba(0,47,69,0.08)':'rgba(217,119,6,0.35)' }}; background:{{ $sisa<=0?'transparent':'rgba(217,119,6,0.1)' }}; color:{{ $sisa<=0?'#002f45':'#92400e' }}; opacity:{{ $sisa<=0?'0.3':'1' }}; text-align:center;">
                    ▼ Pakai
                </button>
                <button type="button" class="btn-stok" onclick="stokAjax(this)"
                    data-url="{{ route('panitia.p3k.stok.global.adjust', $b->id) }}"
                    data-payload='{"delta":-1}'
                    {{ $terp<=0 ? 'disabled' : '' }}
                    style="flex:1; min-width:60px; padding:0.45rem 0.5rem; border-radius:0.6rem; font-size:0.75rem; font-weight:700; cursor:{{ $terp<=0?'not-allowed':'pointer' }}; border:1px solid rgba(0,47,69,0.1); background:rgba(0,47,69,0.04); color:#002f45; opacity:{{ $terp<=0?'0.25':'0.65' }}; text-align:center;">
                    ▲ Batal
                </button>
                <input type="number" class="inp-set" min="0" max="{{ $terk }}" value="{{ $terp }}"
                    style="width:44px; padding:0.4rem 0.3rem; border:1px solid rgba(0,47,69,0.18); border-radius:0.5rem; text-align:center; font-size:0.75rem; font-weight:700; background:white; color:#002f45; flex-shrink:0;">
                <button type="button" class="btn-stok" onclick="stokSet(this)"
                    data-url="{{ route('panitia.p3k.stok.global.set', $b->id) }}"
                    style="padding:0.45rem 0.6rem; border-radius:0.6rem; font-size:0.72rem; font-weight:700; cursor:pointer; border:none; background:#002f45; color:white; white-space:nowrap; flex-shrink:0;">
                    Set
                </button>
            </div>
            @endif

        </div>
        @endforeach
        </div>
    </div>
    @endif
    @endforeach
    @endif

</div>
</div>

<script>
const _csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function updateCard(btn, data) {
    const card = btn.closest('[data-barang]');
    if (!card) return;
    const terk = parseInt(card.getAttribute('data-terkumpul')) || 0;
    const sisa = data.total_sisa;
    const terp = data.total_terpakai;
    const cSisa = sisa <= 0 ? '#dc2626' : (sisa <= terk * 0.25 ? '#d97706' : '#16a34a');

    card.querySelector('.val-terpakai').textContent = terp;
    card.querySelector('.val-sisa').textContent     = sisa;
    card.querySelector('.val-sisa').style.color     = cSisa;

    const bar = card.querySelector('.val-bar');
    if (bar) { bar.style.background = cSisa; bar.style.width = (terk > 0 ? Math.round(sisa/terk*100) : 0) + '%'; }

    const inp = card.querySelector('.inp-set');
    if (inp) inp.value = terp;

    card.querySelectorAll('.btn-stok').forEach(b => {
        const p = b.getAttribute('data-payload') ? JSON.parse(b.getAttribute('data-payload')) : null;
        if (p?.delta === 1)  { b.disabled = sisa <= 0; b.style.opacity = sisa <= 0  ? '0.3'  : '1'; }
        if (p?.delta === -1) { b.disabled = terp <= 0; b.style.opacity = terp <= 0  ? '0.25' : '0.65'; }
    });
}

async function stokAjax(btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    try {
        const res = await fetch(btn.getAttribute('data-url'), {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':_csrf },
            body: JSON.stringify(JSON.parse(btn.getAttribute('data-payload'))),
        });
        if (!res.ok) throw new Error();
        updateCard(btn, await res.json());
    } catch { alert('Gagal menyimpan. Coba lagi.'); }
    btn.disabled = false;
}

async function stokSet(btn) {
    const card = btn.closest('[data-barang]');
    const inp  = card?.querySelector('.inp-set');
    if (!inp) return;
    const val = parseInt(inp.value), max = parseInt(inp.max) || 0;
    if (isNaN(val) || val < 0 || val > max) { inp.style.borderColor = '#dc2626'; return; }
    inp.style.borderColor = '';
    btn.disabled = true;
    try {
        const res = await fetch(btn.getAttribute('data-url'), {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':_csrf },
            body: JSON.stringify({ total_terpakai: val }),
        });
        if (!res.ok) throw new Error();
        updateCard(btn, await res.json());
    } catch { alert('Gagal menyimpan. Coba lagi.'); }
    btn.disabled = false;
}
</script>
@endsection
