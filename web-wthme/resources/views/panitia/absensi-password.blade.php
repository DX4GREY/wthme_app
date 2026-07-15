@extends('layouts.app')

@section('title', 'Password Verifikasi Absensi')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Plus Jakarta Sans', sans-serif;">
    <div style="max-width: 500px; margin: 0 auto;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <span style="display: inline-block; padding: 0.5rem 1.25rem; background: rgba(0,47,69,0.05); border-radius: 2rem; color: #002f45; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1rem;">
                Verifikasi Akses
            </span>
            <div style="display: flex; justify-content: center; margin-bottom: 1.5rem;">
                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 40px; height: 40px; color: #002f45;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; color: #002f45; font-size: 2rem; font-weight: 800; margin: 0;">
                Verifikasi Password Absensi
            </h2>
            <p style="color: #6b705c; margin-top: 0.75rem; font-size: 1rem;">
                Masukkan password harian untuk mengakses data absensi
            </p>
            
            <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(0,47,69,0.03); border: 1px solid rgba(0,47,69,0.05); border-radius: 1.5rem;">
                <p style="font-size: 0.85rem; color: #002f45; display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0;">
                    <svg style="width: 16px; height: 16px; color: #d2c296; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Password diupdate setiap hari oleh admin. Hubungi admin jika Anda memerlukan password hari ini.
                </p>
            </div>
        </div>

        @if(session('error'))
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-left: 4px solid #ef4444; border-radius: 0 1rem 1rem 0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg style="width: 20px; height: 20px; color: #ef4444; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p style="color: #991b1b; font-size: 0.9rem; margin: 0; font-weight: 500;">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(189, 209, 211, 0.2); border: 1px solid rgba(189, 209, 211, 0.4); border-left: 4px solid #bdd1d3; border-radius: 0 1rem 1rem 0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg style="width: 20px; height: 20px; color: #bdd1d3; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p style="color: #002f45; font-size: 0.9rem; margin: 0; font-weight: 500;">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Form -->
        <div style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 2rem; padding: 2.5rem;">
            <form action="{{ route('panitia.absensi.password.verify') }}" method="POST">
                @csrf
                
                <div style="margin-bottom: 2rem;">
                    <label for="password" style="display: block; font-size: 0.9rem; font-weight: 700; color: #002f45; margin-bottom: 0.75rem;">
                        Password Harian
                    </label>
                    <div style="position: relative;">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="glass-input"
                            placeholder="Masukkan password harian"
                            autofocus
                            minlength="6"
                            maxlength="50"
                        >
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility('password', this)" 
                            style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b705c; padding: 0.25rem; transition: color 0.2s;"
                            onmouseover="this.style.color='#002f45'"
                            onmouseout="this.style.color='#6b705c'"
                        >
                            <svg id="eye-icon" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p style="color: #6b705c; font-size: 0.8rem; margin-top: 0.5rem;">
                        Password minimal 6 karakter
                    </p>
                </div>

                <button 
                    type="submit" 
                    style="width: 100%; padding: 1rem 1.5rem; background: #002f45; color: white; border: none; border-radius: 1rem; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                    onmouseover="this.style.background='#003a55'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'"
                >
                    <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Verifikasi Password
                </button>
            </form>
        </div>

        <!-- Back Button -->
        <div style="margin-top: 2rem; text-align: center;">
            <a href="{{ route('panitia.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #d2c296; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: color 0.2s;" onmouseover="this.style.color='#002f45'" onmouseout="this.style.color='#d2c296'">
                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Security Note -->
        <div style="margin-top: 2.5rem; padding: 1.25rem; background: rgba(189, 209, 211, 0.15); border: 1px solid rgba(189, 209, 211, 0.3); border-radius: 1.5rem;">
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                <div style="flex-shrink: 0; width: 32px; height: 32px; background: rgba(210, 194, 150, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-top: 0.125rem;">
                    <svg style="width: 18px; height: 18px; color: #d2c296;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.953 11.953 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size: 0.85rem; font-weight: 700; color: #002f45; margin: 0 0 0.25rem 0;">Catatan Keamanan</p>
                    <p style="font-size: 0.8rem; color: #6b705c; margin: 0; line-height: 1.5;">
                        Password berubah setiap hari untuk melindungi data absensi. Jika Anda mengalami kesulitan, silakan hubungi administrator untuk mendapatkan password hari ini.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-input {
        width: 100%;
        padding: 1rem 3rem 1rem 1.25rem;
        border: 1px solid rgba(0, 47, 69, 0.1);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.5);
        color: #002f45;
        font-size: 1rem;
        transition: all 0.3s;
        outline: none;
    }
    
    .glass-input:focus {
        border-color: #d2c296;
        box-shadow: 0 0 0 3px rgba(210, 194, 150, 0.2);
    }
    
    .glass-input::placeholder {
        color: #6b705c;
        opacity: 0.5;
    }
</style>

<script>
    // Toggle password visibility
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const svg = button.querySelector('svg');
        
        if (input.type === 'password') {
            input.type = 'text';
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l-3.293-3.293m0 0a3 3 0 104.243-4.243l3.293 3.293m-4.243-4.243l4.243 4.243M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            button.setAttribute('title', 'Sembunyikan password');
        } else {
            input.type = 'password';
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            button.setAttribute('title', 'Tampilkan password');
        }
    }
</script>
@endsection