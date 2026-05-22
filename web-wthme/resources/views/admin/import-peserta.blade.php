@extends('layouts.app')

@section('content')
<div style="max-width:600px; margin:0 auto; padding:2rem 1.5rem;">

    <a href="{{ route('admin.index') }}" style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
        ← Kembali ke Dashboard
    </a>

    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:700; margin-bottom:0.5rem;">
        Import Data Peserta
    </h1>
    <p style="color:#002f45; opacity:0.5; font-size:0.875rem; margin-bottom:2rem;">
        Massal membuat akun peserta WTHME 2025 via Excel.
    </p>

    {{-- Petunjuk Format Peserta --}}
    <div style="background:#002f45; border-radius:1rem; padding:1.5rem; margin-bottom:1.5rem;">
        <h3 style="color:#d2c296; font-weight:700; margin-bottom:1rem;">📋 Format Kolom Peserta</h3>
        <div style="color:#bdd1d3; font-size:0.875rem; line-height:1.8;">
            <p>Urutan kolom harus sesuai (5 Kolom):</p>
            <div style="background:rgba(255,255,255,0.1); border-radius:0.5rem; padding:0.75rem; margin:0.75rem 0; font-family:monospace; font-size:0.8rem; color:#fff;">
                nama | nim | angkatan | kelompok | gender
            </div>
            <p>• <strong style="color:#d2c296;">Gender:</strong> Isi dengan <strong style="color:#fff;">L</strong> atau <strong style="color:#fff;">P</strong></p>
            <p>• <strong style="color:#d2c296;">Kelompok:</strong> Isi angka saja (contoh: 1)</p>
            <p>• Password awal otomatis = <strong style="color:#d2c296;">NIM</strong></p>
        </div>
        <a href="{{ route('admin.template.peserta') }}"
           style="display:inline-block; margin-top:1rem; padding:0.6rem 1.25rem; background:#d2c296; color:#002f45; 
                  border-radius:0.6rem; text-decoration:none; font-size:0.875rem; font-weight:700;">
            ⬇ Download Template Peserta
        </a>
    </div>

    {{-- Form Upload --}}
    <div style="background:white; border-radius:1rem; padding:2rem; border:2px solid #bdd1d3;">
        @if ($errors->any())
        <div style="margin-bottom:1.5rem; padding:1rem; background:#fee2e2; border-radius:0.75rem; color:#991b1b; font-size:0.875rem;">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.import.peserta.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.75rem; text-transform:uppercase;">
                    File Excel Peserta
                </label>

                <label for="file-upload"
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center; 
                           padding:2.5rem; border:2px dashed #bdd1d3; border-radius:0.75rem; cursor:pointer;
                           background:#f9f8f6;">
                    <div style="font-size:2.5rem; margin-bottom:0.75rem;">👥</div>
                    <p style="color:#002f45; font-weight:600;">Pilih File Peserta</p>
                    <p style="color:#002f45; opacity:0.4; font-size:0.8rem;" id="file-name">Maks 5MB (.xlsx)</p>
                    <input type="file" id="file-upload" name="file" accept=".xlsx,.xls" required
                        style="display:none;"
                        onchange="document.getElementById('file-name').textContent = this.files[0]?.name">
                </label>
            </div>

            <button type="submit"
                style="width:100%; padding:0.875rem; background:#002f45; color:#d2c296; font-weight:700; border:none; border-radius:0.75rem; cursor:pointer;">
                Import Sekarang
            </button>
        </form>
    </div>
</div>
@endsection