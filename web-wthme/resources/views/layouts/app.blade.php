<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WTHME 2025</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('images/logo-logo.png') }}" type="image/png">
</head>

<body class="min-h-screen bg-cream">

    {{-- Navbar --}}
    <nav class="bg-navy shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    {{-- <img src="{{ asset('images/logo-logo.png') }}" alt="Logo" class="h-20 w-20 object-contain"> --}}
                    <h1 class="hidden sm:block"
                        style="font-nunito:'Playfair Display', serif; color:#ffffff; font-size:2rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        WTHME <span style="color:#d2c296; font-style:italic;">2025</span>
                    </h1>
                </div>
                <div class="flex items-center gap-4">
                    {{-- Bagian Teks Pengguna: Tetap Sama Persis --}}
                    <span class="text-teal text-sm hidden md:block">
                        {{ auth()->user()->name }}
                        <span class="text-sand capitalize ml-1">({{ auth()->user()->role }})</span>
                    </span>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Ganti Password: Disesuaikan dengan style tombol 'Keluar' asli kamu --}}
                        <a href="{{ route('password.change') }}"
                            class="bg-sand/20 hover:bg-sand/40 text-sand border border-sand/30 
                               text-sm px-4 py-2 rounded-lg transition-all duration-200">
                            Ganti Password
                        </a>

                        {{-- Form Logout dengan Konfirmasi --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar?')"
                                class="bg-sand/20 hover:bg-sand/40 text-sand border border-sand/30 
                                   text-sm px-4 py-2 rounded-lg transition-all duration-200">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 pt-4">
            <div
                class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 pt-4">
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

</body>

</html>
