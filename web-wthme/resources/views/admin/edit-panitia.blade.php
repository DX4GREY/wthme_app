@extends('layouts.app')

@section('content')
<div style="max-width:500px; margin:0 auto; padding:2rem 1.5rem;">

    <a href="{{ route('admin.panitia') }}" style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
        ← Kembali
    </a>

    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:700; margin-bottom:2rem;">
        Edit Panitia
    </h1>

    <div style="background:white; border-radius:1rem; padding:2rem; border:2px solid #bdd1d3;">
        @if ($errors->any())
        <div style="margin-bottom:1.5rem; padding:1rem; background:#fee2e2; border-radius:0.75rem; color:#991b1b; font-size:0.875rem;">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.panitia.update', $panitia->id) }}" style="display:flex; flex-direction:column; gap:1.25rem;">
            @csrf
            @method('PUT')

            @foreach([
                ['nama', 'Nama Lengkap', 'name', 'text'],
                ['nim', 'NIM', 'nim', 'text'],
                ['angkatan', 'Angkatan', 'angkatan', 'text'],
                ['divisi', 'Divisi', 'divisi', 'text'],
                ['email', 'Email', 'email', 'email'],
            ] as [$label_lower, $label, $field, $type])
            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                    {{ $label }}
                </label>
                <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $panitia->$field) }}" required
                    style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'">
            </div>
            @endforeach

            <button type="submit"
                style="padding:0.875rem; background:#002f45; color:#d2c296; font-weight:700; border:none; border-radius:0.75rem; cursor:pointer; font-size:0.95rem;"
                onmouseover="this.style.background='#00405e'" onmouseout="this.style.background='#002f45'">
                Simpan Perubahan
            </button>
        </form>
    </div>

</div>
@endsection