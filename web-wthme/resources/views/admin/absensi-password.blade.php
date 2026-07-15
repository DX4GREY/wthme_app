@extends('layouts.app')

@section('title', 'Manajemen Password Absensi Harian')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Plus Jakarta Sans', sans-serif;">
    <div style="max-width: 1100px; margin: 0 auto;">
        
        <!-- Header -->
        <div style="margin-bottom: 3rem;">
            <a href="{{ route('admin.index') }}" style="color: #6b705c; opacity: 0.7; text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem; width: fit-content;">
                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>
            <h1 style="font-family: 'Playfair Display', serif; color: #002f45; font-size: 2rem; font-weight: 700; margin: 1rem 0 0.5rem;">
                Password Absensi Harian
            </h1>
            <p style="color: #6b705c; font-size: 1rem; margin: 0;">
                Buat dan kelola password akses data absensi panitia
            </p>
        </div>

        @if(session('success'))
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(189, 209, 211, 0.2); border: 1px solid rgba(189, 209, 211, 0.4); border-left: 4px solid #bdd1d3; border-radius: 0 0.75rem 0.75rem 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg style="width: 20px; height: 20px; color: #bdd1d3; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p style="color: #002f45; font-size: 0.9rem; margin: 0; font-weight: 500;">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-left: 4px solid #ef4444; border-radius: 0 0.75rem 0.75rem 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg style="width: 20px; height: 20px; color: #ef4444; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p style="color: #991b1b; font-size: 0.9rem; margin: 0; font-weight: 500;">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Form Buat/Update Password -->
            <div>
                <div style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 2rem; padding: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #002f45; margin: 0 0 1.5rem 0;">
                        @if($todayPassword)
                            Update Password Hari Ini
                        @else
                            Buat Password Hari Ini
                        @endif
                    </h3>

                    @if($todayPassword)
                        <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: rgba(0, 47, 69, 0.02); border: 1px solid rgba(0, 47, 69, 0.05); border-radius: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div style="flex: 1;">
                                    <p style="font-size: 0.85rem; font-weight: 700; color: #002f45; margin: 0 0 0.5rem 0;">
                                        Password Saat Ini
                                    </p>
                                </div>
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(189, 209, 211, 0.2); color: #002f45; border: 1px solid rgba(189, 209, 211, 0.3);">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #bdd1d3; animation: pulse 2s infinite;"></span>
                                    Aktif
                                </span>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <code id="currentPassword" style="font-family: monospace; background: white; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(0, 47, 69, 0.1); color: #002f45; font-weight: 700; letter-spacing: 0.05em; flex: 1;">
                                    {{ $todayPassword->password_tampil ?? '******' }}
                                </code>
                                <button onclick="copyToClipboard('currentPassword', this)" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 0.875rem; background: rgba(210, 194, 150, 0.2); color: #002f45; border: none; border-radius: 0.5rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='rgba(210, 194, 150, 0.4)'" onmouseout="this.style.background='rgba(210, 194, 150, 0.2)'">
                                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Salin
                                </button>
                            </div>
                            
                            <p style="font-size: 0.8rem; color: #6b705c; display: flex; align-items: center; gap: 0.25rem; margin: 0;">
                                <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Dibuat: {{ $todayPassword->dibuat_pada ? $todayPassword->dibuat_pada->format('d/m/Y H:i') : '-' }}
                            </p>
                        </div>
                    @else
                        <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: rgba(189, 209, 211, 0.1); border: 1px solid rgba(189, 209, 211, 0.2); border-radius: 1.25rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <div style="flex-shrink: 0; width: 32px; height: 32px; background: rgba(210, 194, 150, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg style="width: 18px; height: 18px; color: #d2c296;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p style="font-size: 0.85rem; font-weight: 600; color: #002f45; margin: 0 0 0.25rem 0;">
                                        Belum ada password untuk hari ini
                                    </p>
                                    <p style="font-size: 0.8rem; color: #6b705c; margin: 0;">
                                        Buat password sekarang agar panitia dapat mengakses data absensi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                     <form action="{{ route('admin.absensi.password.store') }}" method="POST" id="passwordForm">
                         @csrf
                         <div style="margin-bottom: 1.5rem;">
                             <label for="password" style="display: block; font-size: 0.9rem; font-weight: 700; color: #002f45; margin-bottom: 0.5rem;">
                                 @if($todayPassword)
                                     Update Password Baru
                                 @else
                                     Buat Password Baru
                                 @endif
                             </label>
                             
                             <!-- Generate Random Password Button -->
                             <div style="margin-bottom: 0.75rem;">
                                 <button 
                                     type="button" 
                                     onclick="generateRandomPassword()"
                                     style="width: 100%; padding: 0.625rem 1rem; background: rgba(210, 194, 150, 0.2); color: #002f45; border: 1px solid rgba(210, 194, 150, 0.4); border-radius: 0.625rem; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                                     onmouseover="this.style.background='rgba(210, 194, 150, 0.4)'"
                                     onmouseout="this.style.background='rgba(210, 194, 150, 0.2)'"
                                 >
                                     <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 014.582 9m0 0H9m11-11v5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                     </svg>
                                     Generate Password Random
                                 </button>
                             </div>
                            
                             <div style="position: relative;">
                                 <input 
                                     type="password" 
                                     name="password" 
                                     id="passwordInput" 
                                     required 
                                     class="glass-input"
                                     placeholder="Masukkan password harian"
                                     minlength="6"
                                     maxlength="50"
                                 >
                                 <button 
                                     type="button" 
                                     onclick="togglePasswordVisibility('passwordInput', this)" 
                                     style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6b705c; padding: 0.25rem; transition: color 0.2s;"
                                     onmouseover="this.style.color='#002f45'"
                                     onmouseout="this.style.color='#6b705c'"
                                 >
                                     <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                     </svg>
                                 </button>
                             </div>
                             <p style="color: #6b705c; font-size: 0.8rem; margin: 0.5rem 0 0 0;">
                                 Minimal 6 karakter, maksimal 50 karakter
                             </p>
                         </div>

                         <button 
                             type="submit" 
                             style="width: 100%; padding: 0.875rem 1.25rem; background: #002f45; color: white; border: none; border-radius: 0.875rem; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                             onmouseover="this.style.background='#003a55'; this.style.transform='translateY(-2px)'"
                             onmouseout="this.style.background='#002f45'; this.style.transform='translateY(0)'"
                         >
                             @if($todayPassword)
                                 <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 014.582 9m0 0H9m11-11v5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                 </svg>
                                 Update Password
                             @else
                                 <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                 </svg>
                                 Buat Password
                             @endif
                         </button>
                     </form>
                </div>

                <!-- Info Box -->
                <div style="margin-top: 1.5rem; padding: 1.25rem; background: rgba(189, 209, 211, 0.1); border: 1px solid rgba(189, 209, 211, 0.2); border-radius: 1.25rem;">
                    <h3 style="font-size: 0.85rem; font-weight: 700; color: #002f45; margin: 0 0 0.75rem 0;">Informasi Penting</h3>
                    <ul style="margin: 0; padding-left: 1.25rem; color: #6b705c; font-size: 0.85rem; list-style: none;">
                        <li style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <svg style="width: 16px; height: 16px; color: #d2c296; flex-shrink: 0; margin-top: 0.125rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Password berubah setiap hari untuk keamanan</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <svg style="width: 16px; height: 16px; color: #d2c296; flex-shrink: 0; margin-top: 0.125rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Hanya admin yang dapat membuat dan mengubah password</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <svg style="width: 16px; height: 16px; color: #d2c296; flex-shrink: 0; margin-top: 0.125rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            <span>Panitia diminta password untuk mengakses data absensi</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <svg style="width: 16px; height: 16px; color: #d2c296; flex-shrink: 0; margin-top: 0.125rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Password disimpan dalam bentuk terenkripsi (hash)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Riwayat Password -->
            <div>
                <div style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 2rem; padding: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #002f45; margin: 0 0 1.5rem 0;">
                        7 Password Terakhir
                    </h3>

                    @if($recentPasswords->count() > 0)
                        <div style="overflow: hidden; border: 1px solid rgba(0, 47, 69, 0.05); border-radius: 1.25rem;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #002f45;">
                                        <th style="padding: 1rem; text-align: left; color: #d2c296; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Tanggal
                                            </div>
                                        </th>
                                        <th style="padding: 1rem; text-align: left; color: #d2c296; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Dibuat Oleh
                                            </div>
                                        </th>
                                        <th style="padding: 1rem; text-align: left; color: #d2c296; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Waktu
                                            </div>
                                        </th>
                                        <th style="padding: 1rem; text-align: left; color: #d2c296; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Status
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPasswords as $password)
                                        <tr style="border-bottom: 1px solid rgba(0, 47, 69, 0.05); {{ $password->tanggal == date('Y-m-d') ? 'background: rgba(210, 194, 150, 0.1);' : '' }}">
                                            <td style="padding: 1rem;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="font-weight: 600; color: #002f45;">{{ \Carbon\Carbon::parse($password->tanggal)->format('d/m/Y') }}</span>
                                                    @if($password->tanggal == date('Y-m-d'))
                                                        <span style="display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; background: rgba(189, 209, 211, 0.2); color: #002f45; border: 1px solid rgba(189, 209, 211, 0.3);">
                                                            Hari Ini
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding: 1rem; color: #6b705c;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    @if($password->dibuatOleh)
                                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #d2c296, #e0decd); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #002f45; font-size: 0.75rem;">
                                                            {{ substr($password->dibuatOleh->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <span style="font-weight: 500; color: #002f45; font-size: 0.85rem;">{{ $password->dibuatOleh->name }}</span>
                                                            <span style="display: block; font-size: 0.7rem; color: #6b705c;">Admin</span>
                                                        </div>
                                                    @else
                                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(189, 209, 211, 0.2); display: flex; align-items: center; justify-content: center;">
                                                            <svg style="width: 18px; height: 18px; color: #bdd1d3;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <span style="font-weight: 500; color: #002f45; font-size: 0.85rem;">System</span>
                                                            <span style="display: block; font-size: 0.7rem; color: #6b705c;">Auto-Generated</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding: 1rem; color: #6b705c;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <svg style="width: 16px; height: 16px; color: #d2c296;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $password->dibuat_pada ? $password->dibuat_pada->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            </td>
                                            <td style="padding: 1rem;">
                                                @if($password->tanggal == date('Y-m-d'))
                                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: rgba(189, 209, 211, 0.2); color: #002f45; border: 1px solid rgba(189, 209, 211, 0.3);">
                                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #bdd1d3; animation: pulse 2s infinite;"></span>
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; color: #9ca3af; font-size: 0.85rem;">
                                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Expired
                                                    </span>
                                                     @endif
                                                     </td>
                                                 </tr>
                                             @endforeach
                                         </tbody>
                                     </table>
                                 </div>
                             @else
                                 <div style="text-align: center; padding: 3rem 1rem;">
                                     <svg style="width: 48px; height: 48px; margin: 0 auto; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                     </svg>
                                     <p style="margin-top: 1rem; color: #6b705c; font-size: 0.9rem;">Belum ada password yang dibuat.</p>
                                 </div>
                             @endif
                         </div>
                         
                         <!-- Password History Log -->
                         @if($passwordHistory && $passwordHistory->count() > 0)
                             <div style="margin-top: 2rem;">
                                 <h4 style="font-size: 1rem; font-weight: 700; color: #002f45; margin: 0 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(0, 47, 69, 0.1);">
                                     Riwayat Perubahan Password
                                 </h4>
                                 <div style="max-height: 300px; overflow-y: auto;">
                                     <table style="width: 100%; border-collapse: collapse;">
                                         <thead>
                                             <tr style="background: rgba(0, 47, 69, 0.02);">
                                                 <th style="padding: 0.75rem; text-align: left; color: #6b705c; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Waktu</th>
                                                 <th style="padding: 0.75rem; text-align: left; color: #6b705c; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Password</th>
                                                 <th style="padding: 0.75rem; text-align: left; color: #6b705c; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Oleh</th>
                                             </tr>
                                         </thead>
                                         <tbody>
                                             @foreach($passwordHistory as $history)
                                                 <tr style="border-bottom: 1px solid rgba(0, 47, 69, 0.03);">
                                                     <td style="padding: 0.5rem 0.75rem; font-size: 0.8rem; color: #6b705c;">
                                                         {{ $history->created_at ? $history->created_at->format('d/m/Y H:i') : '-' }}
                                                     </td>
                                                     <td style="padding: 0.5rem 0.75rem; font-family: monospace; font-size: 0.85rem; color: #002f45;">
                                                         {{ $history->password_tampil }}
                                                     </td>
                                                     <td style="padding: 0.5rem 0.75rem; font-size: 0.8rem; color: #002f45;">
                                                         @if($history->createdBy)
                                                             {{ $history->createdBy->name }} <span style="color: #6b705c; font-size: 0.7rem;">(Admin)</span>
                                                         @else
                                                             <span style="color: #6b705c;">System</span>
                                                         @endif
                                                     </td>
                                                 </tr>
                                             @endforeach
                                         </tbody>
                                     </table>
                                 </div>
                             </div>
                         @endif
                     </div>
                 </div>
             </div>
         </div>
     </div>

<style>
    .glass-input {
        width: 100%;
        padding: 0.875rem 3rem 0.875rem 1rem;
        border: 1px solid rgba(0, 47, 69, 0.1);
        border-radius: 0.875rem;
        background: rgba(255, 255, 255, 0.5);
        color: #002f45;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.3s;
    }
    
    .glass-input:focus {
        border-color: #d2c296;
        box-shadow: 0 0 0 3px rgba(210, 194, 150, 0.2);
    }
    
    .glass-input::placeholder {
        color: #6b705c;
        opacity: 0.4;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
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

    // Copy to clipboard
    function copyToClipboard(elementId, button) {
        const element = document.getElementById(elementId);
        const text = element.textContent;
        
        navigator.clipboard.writeText(text).then(function() {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span style="font-size: 0.75rem;">Tersalin!</span>';
            button.style.background = 'rgba(189, 209, 211, 0.3)';
            
            setTimeout(function() {
                button.innerHTML = originalHTML;
                button.style.background = 'rgba(210, 194, 150, 0.2)';
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            alert('Gagal menyalin password');
        });
    }

    // Generate Random Password - Direct without confirmation
    function generateRandomPassword() {
        // Create temp form and submit directly
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = '{{ route("admin.absensi.password.generate") }}';
        tempForm.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
        document.body.appendChild(tempForm);
        tempForm.submit();
    }
    </script>
@endsection
