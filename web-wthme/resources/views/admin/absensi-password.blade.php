@extends('layouts.app')

@section('title', 'Manajemen Password Absensi Harian')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Password Absensi Harian
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Buat dan kelola password akses data absensi panitia
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('admin.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Buat Password -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            @if($todayPassword)
                                Update Password Hari Ini
                            @else
                                Buat Password Hari Ini
                            @endif
                        </h3>

                        @if($todayPassword)
                            <div class="mb-4 p-4 bg-gradient-to-br from-yellow-50 to-amber-50 border border-yellow-300 rounded-lg shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-yellow-800 mb-1">
                                            Password Saat Ini
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <code id="currentPassword" class="font-mono bg-white px-3 py-1.5 rounded border border-yellow-200 text-gray-900 font-bold tracking-wide">
                                                {{ $todayPassword->password_tampil ?? '***' }}
                                            </code>
                                            <button onclick="copyToClipboard('currentPassword', this)" class="inline-flex items-center gap-1 px-2 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded transition-colors text-xs font-medium" title="Salin password">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Salin</span>
                                            </button>
                                        </div>
                                        <p class="text-xs text-yellow-600 mt-2 flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Dibuat: {{ $todayPassword->dibuat_pada ? $todayPassword->dibuat_pada->format('d/m/Y H:i') : '-' }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-300 rounded-lg shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-blue-900">
                                            Belum ada password untuk hari ini
                                        </p>
                                        <p class="text-xs text-blue-700 mt-1">
                                            Buat password sekarang agar panitia dapat mengakses data absensi.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('admin.absensi.password.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    @if($todayPassword)
                                        Update Password Baru
                                    @else
                                        Buat Password Baru
                                    @endif
                                </label>
                                <div class="relative">
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="passwordInput" 
                                        required 
                                        class="appearance-none rounded-lg relative block w-full px-3 py-2.5 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition-all"
                                        placeholder="Masukkan password harian"
                                        autofocus
                                        minlength="6"
                                        maxlength="50"
                                    >
                                    <button type="button" onclick="togglePasswordVisibility('passwordInput', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-xs text-gray-500">
                                        Minimal 6 karakter, maksimal 50 karakter
                                    </p>
                                    <div id="passwordStrength" class="flex gap-1">
                                        <div class="h-1 w-6 rounded bg-gray-200 password-strength-bar"></div>
                                        <div class="h-1 w-6 rounded bg-gray-200 password-strength-bar"></div>
                                        <div class="h-1 w-6 rounded bg-gray-200 password-strength-bar"></div>
                                        <div class="h-1 w-6 rounded bg-gray-200 password-strength-bar"></div>
                                    </div>
                                </div>
                            </div>

                            <button 
                                type="submit" 
                                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-yellow-600 to-amber-600 hover:from-yellow-700 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all transform hover:scale-[1.02]"
                            >
                                @if($todayPassword)
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11-11v5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Update Password
                                @else
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Buat Password
                                @endif
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-4 bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-indigo-900 mb-2">Informasi Penting</h3>
                            <div class="text-sm text-indigo-700 space-y-1.5">
                                <div class="flex items-start gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Password berubah setiap hari untuk keamanan</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Hanya admin yang dapat membuat dan mengubah password</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                    </svg>
                                    <span>Panitia diminta password untuk mengakses data absensi</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Password disimpan dalam bentuk terenkripsi (hash)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Password -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            7 Password Terakhir
                        </h3>

                        @if($recentPasswords->count() > 0)
                            <div class="overflow-hidden shadow-lg ring-1 ring-gray-200 rounded-xl">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-sm font-bold text-gray-900">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Tanggal
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-left text-sm font-bold text-gray-900">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    Dibuat Oleh
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-left text-sm font-bold text-gray-900">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Waktu
                                                </div>
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-left text-sm font-bold text-gray-900">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Status
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach($recentPasswords as $password)
                                            <tr class="{{ $password->tanggal == date('Y-m-d') ? 'bg-gradient-to-r from-yellow-50 to-amber-50 hover:from-yellow-100 hover:to-amber-100' : 'hover:bg-gray-50' }} transition-colors">
                                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($password->tanggal)->format('d/m/Y') }}</span>
                                                        @if($password->tanggal == date('Y-m-d'))
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                                Hari Ini
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white font-semibold text-xs">
                                                            {{ substr($password->dibuatOleh->name ?? 'S', 0, 1) }}
                                                        </div>
                                                        <span class="font-medium">{{ $password->dibuatOleh->name ?? 'System' }}</span>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                    <div class="flex items-center gap-1.5">
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $password->dibuat_pada ? $password->dibuat_pada->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                    @if($password->tanggal == date('Y-m-d'))
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                            <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 text-gray-400">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Belum ada password yang dibuat.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
            button.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Tersalin!</span>';
            button.classList.add('bg-green-100', 'text-green-700');
            button.classList.remove('bg-yellow-100', 'text-yellow-700');
            
            setTimeout(function() {
                button.innerHTML = originalHTML;
                button.classList.remove('bg-green-100', 'text-green-700');
                button.classList.add('bg-yellow-100', 'text-yellow-700');
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            alert('Gagal menyalin password');
        });
    }

    // Password strength indicator
    document.getElementById('passwordInput')?.addEventListener('input', function(e) {
        const password = e.target.value;
        const bars = document.querySelectorAll('.password-strength-bar');
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 12) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9!@#$%^&*]/)) strength++;
        
        const colors = ['bg-gray-200', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
        
        bars.forEach((bar, index) => {
            bar.className = 'h-1 w-6 rounded password-strength-bar transition-all';
            if (index < strength) {
                bar.classList.add(colors[strength]);
            } else {
                bar.classList.add('bg-gray-200');
            }
        });
    });
</script>
