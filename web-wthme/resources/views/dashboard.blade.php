@extends('layouts.app')

@section('content')
{{-- Background Ambient --}}
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem;">
    <div style="max-width: 1000px; margin: 0 auto;">

        {{-- Header Section --}}
        <div style="text-align: center; margin-bottom: 4rem;">
            <span style="display: inline-block; padding: 0.5rem 1.25rem; background: rgba(0,47,69,0.05); border-radius: 2rem; color: #002f45; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1rem;">
                {{ date('l, d F Y') }}
            </span>
            <h1 style="font-family: 'Playfair Display', serif; color: #002f45; font-size: 3rem; font-weight: 700; margin: 0; letter-spacing: -0.02em;">
                Halo, {{ explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p style="color: #002f45; opacity: 0.5; font-size: 1.1rem; margin-top: 0.75rem; font-weight: 400;">
                Pilih portal yang sesuai dengan peranmu hari ini.
            </p>
        </div>

        {{-- Main Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            
            {{-- Card Admin --}}
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.index') }}" class="glass-card" style="text-decoration: none; background: rgba(210,194,150,0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.4); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 2rem; margin-bottom: 1.5rem;">⚙️</div>
                    <h3 style="color: #002f45; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">Panel Admin</h3>
                    <p style="color: #002f45; opacity: 0.7; font-size: 0.9rem; line-height: 1.6; margin: 0;">Kelola infrastruktur sistem, kontrol penuh data, dan manajemen otoritas.</p>
                </div>
                <div style="margin-top: 2rem; color: #002f45; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                    AKSES SISTEM <span style="font-size: 1.2rem;">→</span>
                </div>
            </a>
            @endif

            {{-- Card Panitia --}}
            @php $isPanitia = auth()->user()->isPanitia(); @endphp
            <a href="{{ $isPanitia ? route('panitia.index') : '#' }}" 
               style="text-decoration: none; background: {{ $isPanitia ? 'rgba(0,47,69,0.95)' : 'rgba(0,47,69,0.1)' }}; backdrop-filter: blur(10px); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between; border: 1px solid rgba(255,255,255,0.1); cursor: {{ $isPanitia ? 'pointer' : 'not-allowed' }};"
               @if($isPanitia) onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 30px 60px rgba(0,47,69,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'" @endif>
                <div>
                    <div style="width: 48px; height: 48px; background: rgba(210,194,150,0.2); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 24px; height: 24px; color: #d2c296;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 style="color: {{ $isPanitia ? '#d2c296' : 'rgba(0,47,69,0.4)' }}; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">Portal Panitia</h3>
                    <p style="color: #bdd1d3; opacity: {{ $isPanitia ? '0.8' : '0.3' }}; font-size: 0.9rem; line-height: 1.6; margin: 0;">Kelola operasional, absensi real-time, dan manajemen logistik event.</p>
                </div>
                <div style="margin-top: 2rem; color: #d2c296; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; opacity: {{ $isPanitia ? '1' : '0.3' }};">
                    {{ $isPanitia ? 'MASUK PORTAL' : 'AKSES TERBATAS' }} <span style="font-size: 1.2rem;">→</span>
                </div>
            </a>

            {{-- Card Peserta --}}
            @php $isPeserta = auth()->user()->isPeserta(); @endphp
            <a href="{{ $isPeserta ? route('peserta.index') : '#' }}"
               style="text-decoration: none; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(189,209,211,0.5); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between; cursor: {{ $isPeserta ? 'pointer' : 'not-allowed' }};"
               @if($isPeserta) onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 30px 60px rgba(0,47,69,0.1)'; this.style.borderColor='#002f45'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='rgba(189,209,211,0.5)'" @endif>
                <div>
                    <div style="width: 48px; height: 48px; background: #e0decd; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 24px; height: 24px; color: #002f45;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 style="color: #002f45; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">Portal Peserta</h3>
                    <p style="color: #002f45; opacity: {{ $isPeserta ? '0.6' : '0.2' }}; font-size: 0.9rem; line-height: 1.6; margin: 0;">Akses materi, jadwal kegiatan, dan kumpulkan tugas harianmu di sini.</p>
                </div>
                <div style="margin-top: 2rem; color: #002f45; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; opacity: {{ $isPeserta ? '1' : '0.3' }};">
                    MULAI <span style="font-size: 1.2rem;">→</span>
                </div>
            </a>
        </div>

        {{-- Info Identitas (Footer Style Baru) --}}
        <div style="margin-top: 3rem; background: rgba(255,255,255,0.5); backdrop-filter: blur(8px); border-radius: 1.25rem; padding: 1.5rem 2rem; border: 1px solid rgba(255,255,255,0.4); display: flex; gap: 3rem; flex-wrap: wrap; align-items: center;">
            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                <span style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">NIM</span>
                <span style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->nim ?? '-' }}</span>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                <span style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">Angkatan</span>
                <span style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->angkatan ?? '-' }}</span>
            </div>

            @if(auth()->user()->kelompok)
            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                <span style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">Kelompok</span>
                <span style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->kelompok }}</span>
            </div>
            @endif

            @if(auth()->user()->divisi)
            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                <span style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">Divisi</span>
                <span style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->divisi }}</span>
            </div>
            @endif
        </div>

    </div>
</div>

<style>
    .glass-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 30px 60px rgba(210,194,150,0.3);
        background: rgba(210,194,150,0.5) !important;
    }
</style>
@endsection