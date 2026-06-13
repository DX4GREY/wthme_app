@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:1100px; margin:0 auto;">

        {{-- Header --}}
        <div style="background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem; box-shadow: 0 8px 32px rgba(0, 47, 69, 0.1);">
            <div>
                <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin:0;">
                    Kelompok {{ $kelompok }} — P3K
                </h1>
                <p style="color:#002f45; opacity:0.6; font-size:0.9rem; margin-top:0.4rem; font-weight:500;">
                    🛡️ Panel Validasi P3K · ACC pengumpulan barang & terima obat pribadi
                </p>
            </div>
            <a href="{{ route('panitia.p3k.index') }}" style="text-decoration:none; background: rgba(0, 47, 69, 0.85); color:#d2c296; padding:0.75rem 1.5rem; border-radius:1rem; font-size:0.85rem; font-weight:700;">
                ← Kembali
            </a>
        </div>

        @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); color:#166534; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.3); color:#991b1b; padding:1rem 1.5rem; border-radius:1.25rem; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ⚠️ {{ $errors->first() }}
        </div>
        @endif

        {{-- Barang Kelompok --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin-bottom:1rem; padding-left:0.5rem;">📦 Barang Kelompok</h3>
        @include('panitia.p3k.partials.validasi-table', ['data' => $dataKelompok, 'kelompok' => $kelompok, 'withTerpakai' => true])

        {{-- Barang Individu --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin:2rem 0 1rem; padding-left:0.5rem;">🎒 Barang Individu (Bawaan Pribadi Tiap Peserta)</h3>
        @include('panitia.p3k.partials.validasi-table-individu', ['dataIndividu' => $dataIndividu, 'barangsIndividu' => $barangsIndividu, 'summaryIndividuKelompok' => $summaryIndividuKelompok])

        {{-- Obat Pribadi --}}
        <h3 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.3rem; font-weight:800; margin:2rem 0 1rem; padding-left:0.5rem;">💊 Obat Pribadi Peserta</h3>
        @include('panitia.p3k.partials.obat-pribadi', ['obatPribadi' => $obatPribadi])

    </div>
</div>
@endsection
