@extends('layouts.app')

@section('content')
{{-- Background Wrapper dengan gradien --}}
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%);">
    <div style="max-width:650px; margin:0 auto;">

        {{-- Navigasi Kembali --}}
        <a href="{{ route('peserta.index') }}" 
           style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7; transition:0.2s;"
           onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>

        {{-- Header Section --}}
        <div style="margin-bottom:2rem;">
            <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:800; margin-bottom:0.5rem;">
                🩺 Riwayat Kesehatan
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.95rem; font-weight:500; line-height:1.5;">
                Data ini diperlukan agar panitia tahu kondisi peserta WTHME 2025, dengan begitu panitia dapat lebih maksimal dalam penanganan medis di lapangan nanti.
            </p>
        </div>

        {{-- Notifikasi --}}
        @if(session('success'))
        <div style="padding:1rem 1.5rem; background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3); border-radius:1.25rem; color:#166534; margin-bottom:1.5rem; font-size:0.9rem; font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        {{-- Main Glass Card --}}
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:2rem; border:1px solid rgba(255, 255, 255, 0.5); padding:2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">

            <form method="POST" action="{{ route('peserta.riwayat.store') }}" style="display:flex; flex-direction:column; gap:1.75rem;">
                @csrf

                {{-- Info Box (Darker Glass) --}}
                <div style="background: rgba(0, 47, 69, 0.05); border-radius:1.25rem; padding:1.25rem; display:flex; gap:1.5rem; flex-wrap:wrap; border: 1px solid rgba(0, 47, 69, 0.05);">
                    <div>
                        <div style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem;">Nama Lengkap</div>
                        <div style="color:#002f45; font-weight:700; font-size:0.9rem;">{{ auth()->user()->name }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem;">NIM</div>
                        <div style="color:#002f45; font-weight:700; font-size:0.9rem;">{{ auth()->user()->nim }}</div>
                    </div>
                    <div style="margin-left:auto;">
                        <div style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem;">Kelompok</div>
                        <div style="color:#d2c296; background:#002f45; padding:0.1rem 0.6rem; border-radius:0.5rem; font-weight:700; font-size:0.85rem;">{{ auth()->user()->kelompok }}</div>
                    </div>
                </div>

                {{-- Kondisi Kesehatan (Custom Radios) --}}
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:1rem; text-transform:uppercase; letter-spacing:0.05em;">
                        Kondisi Kesehatan Saat Ini <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        @foreach(['Baik', 'Cukup', 'Perlu Perhatian'] as $kondisi)
                        @php $isSelected = old('kondisi_kesehatan', $data->kondisi_kesehatan ?? '') === $kondisi; @endphp
                        <label style="flex:1; min-width:120px; display:flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.8rem; border:2px solid {{ $isSelected ? '#002f45' : 'rgba(0,47,69,0.1)' }}; border-radius:1rem; cursor:pointer; transition:0.2s; font-size:0.875rem; font-weight:700;
                                      background: {{ $isSelected ? '#002f45' : 'rgba(255,255,255,0.4)' }}; 
                                      color: {{ $isSelected ? '#d2c296' : '#002f45' }};"
                               onmouseover="if(!{{ $isSelected ? 'true' : 'false' }}) this.style.borderColor='#002f45'"
                               onmouseout="if(!{{ $isSelected ? 'true' : 'false' }}) this.style.borderColor='rgba(0,47,69,0.1)'">
                            <input type="radio" name="kondisi_kesehatan" value="{{ $kondisi }}" style="display:none;"
                                   {{ $isSelected ? 'checked' : '' }}>
                            {{ $kondisi }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Textareas --}}
                @php
                    $fields = [
                        ['name' => 'riwayat_penyakit', 'label' => 'Riwayat Penyakit', 'sub' => '(asma, maag, jantung, dll)', 'placeholder' => 'Sebutkan jika ada...'],
                        ['name' => 'alergi', 'label' => 'Alergi', 'sub' => '(makanan, obat, debu)', 'placeholder' => 'Sebutkan jika ada...'],
                        ['name' => 'obat_rutin', 'label' => 'Obat yang Rutin Dikonsumsi', 'sub' => '(jika ada)', 'placeholder' => 'Contoh: Inhaler, Insulin...'],
                        ['name' => 'keterangan_tambahan', 'label' => 'Keterangan Lain', 'sub' => '', 'placeholder' => 'Hal lain yang perlu diketahui tim medis...']
                    ];
                @endphp

                @foreach($fields as $field)
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">
                        {{ $field['label'] }} <span style="font-weight:500; opacity:0.5; text-transform:none; letter-spacing:0;">{{ $field['sub'] }}</span>
                    </label>
                    <textarea name="{{ $field['name'] }}" rows="2"
                        style="width:100%; padding:1rem; background:rgba(255,255,255,0.4); border:1px solid rgba(0,47,69,0.1); border-radius:1rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none; resize:vertical; font-family:inherit; font-weight:600; transition:0.2s;"
                        onfocus="this.style.background='rgba(255,255,255,0.7)'; this.style.borderColor='#002f45';" 
                        onblur="this.style.background='rgba(255,255,255,0.4)'; this.style.borderColor='rgba(0,47,69,0.1)';"
                        placeholder="{{ $field['placeholder'] }}">{{ old($field['name'], $data->{$field['name']} ?? '') }}</textarea>
                </div>
                @endforeach

                {{-- Button --}}
                <button type="submit"
                    style="margin-top:0.5rem; padding:1.1rem; background:#002f45; color:#d2c296; font-weight:800; border:none; border-radius:1.25rem; cursor:pointer; font-size:1rem; box-shadow: 0 10px 20px rgba(0,47,69,0.2); transition:0.3s;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,47,69,0.3)';" 
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px rgba(0,47,69,0.2)';">
                    {{ $data ? '🔄 Perbarui Data Medis' : '💾 Simpan Data Medis' }}
                </button>

            </form>
        </div>

        <p style="text-align:center; color:#002f45; opacity:0.4; font-size:0.8rem; margin-top:2rem; font-weight:600;">
            Keamanan data Anda adalah prioritas kami.
        </p>

    </div>
</div>

{{-- Script Interaktif untuk Radio Glass --}}
<script>
document.querySelectorAll('input[name="kondisi_kesehatan"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="kondisi_kesehatan"]').forEach(r => {
            const label = r.closest('label');
            label.style.background = 'rgba(255,255,255,0.4)';
            label.style.borderColor = 'rgba(0,47,69,0.1)';
            label.style.color = '#002f45';
        });
        const selected = this.closest('label');
        selected.style.background = '#002f45';
        selected.style.borderColor = '#002f45';
        selected.style.color = '#d2c296';
    });
});
</script>
@endsection