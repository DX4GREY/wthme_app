@extends('layouts.app')

@section('content')
<div style="max-width:600px; margin:0 auto; padding:2rem 1.5rem;">

    <a href="{{ route('admin.panitia') }}" style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
        ← Kembali ke Daftar Panitia
    </a>

    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:700; margin-bottom:0.5rem;">
        Import Data Panitia
    </h1>
    <p style="color:#002f45; opacity:0.5; font-size:0.875rem; margin-bottom:2rem;">
        Upload file Excel berisi daftar panitia untuk membuat akun secara massal.
    </p>

    {{-- Petunjuk --}}
    <div style="background:#002f45; border-radius:1rem; padding:1.5rem; margin-bottom:1.5rem;">
        <h3 style="color:#d2c296; font-weight:700; margin-bottom:1rem;">📋 Petunjuk Format File</h3>
        <div style="color:#bdd1d3; font-size:0.875rem; line-height:1.8;">
            <p>File Excel harus memiliki kolom dengan urutan:</p>
            <div style="background:rgba(255,255,255,0.1); border-radius:0.5rem; padding:0.75rem; margin:0.75rem 0; font-family:monospace; font-size:0.8rem;">
                nama | nim | angkatan | divisi | email
            </div>
            <p>• Baris pertama = <strong style="color:#d2c296;">header</strong> (nama kolom)</p>
            <p>• Password awal = <strong style="color:#d2c296;">NIM masing-masing</strong></p>
            <p>• Panitia akan diminta ganti password saat pertama login</p>
            <p>• NIM dan email yang sudah terdaftar akan <strong style="color:#d2c296;">dilewati otomatis</strong></p>
        </div>
        <a href="{{ route('admin.template') }}"
           style="display:inline-block; margin-top:1rem; padding:0.6rem 1.25rem; background:#d2c296; color:#002f45; 
                  border-radius:0.6rem; text-decoration:none; font-size:0.875rem; font-weight:700;">
            ⬇ Download Template Excel
        </a>
    </div>

    {{-- Form Upload --}}
    <div style="background:white; border-radius:1rem; padding:2rem; border:2px solid #bdd1d3;">

        @if ($errors->any())
        <div style="margin-bottom:1.5rem; padding:1rem; background:#fee2e2; border-radius:0.75rem; color:#991b1b; font-size:0.875rem;">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.import.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">
                    File Excel (.xlsx / .xls)
                </label>

                {{-- Dropzone area --}}
                <label for="file-upload"
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center; 
                           padding:2.5rem; border:2px dashed #bdd1d3; border-radius:0.75rem; cursor:pointer;
                           background:#f9f8f6; transition:border-color 0.2s;"
                    onmouseover="this.style.borderColor='#002f45'"
                    onmouseout="this.style.borderColor='#bdd1d3'">
                    <div style="font-size:2.5rem; margin-bottom:0.75rem;">📊</div>
                    <p style="color:#002f45; font-weight:600; font-size:0.9rem; margin-bottom:0.25rem;">
                        Klik untuk pilih file
                    </p>
                    <p style="color:#002f45; opacity:0.4; font-size:0.8rem;" id="file-name">
                        Format: .xlsx atau .xls, maks 5MB
                    </p>
                    <input type="file" id="file-upload" name="file" accept=".xlsx,.xls" required
                        style="display:none;"
                        onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Format: .xlsx atau .xls'">
                </label>
            </div>

            <button type="submit"
                style="width:100%; padding:0.875rem; background:#002f45; color:#d2c296; font-weight:700; 
                       border:none; border-radius:0.75rem; cursor:pointer; font-size:0.95rem;"
                onmouseover="this.style.background='#00405e'" onmouseout="this.style.background='#002f45'">
                Upload & Buat Akun Panitia
            </button>
        </form>
    </div>

</div>
@endsection