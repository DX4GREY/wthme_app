<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — WTHME</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Nunito:wght@800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen flex" style="background-color: #e0decd; font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- Panel Kiri: Branding (Hanya muncul di Desktop) --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden" style="background-color: #e0decd;">
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#bdd1d3" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>

        <div class="relative z-10 flex flex-col items-center justify-center w-full p-12 text-center">
            {{-- Logo Branding: Ukuran besar tapi tinggi dikunci agar tidak mendorong teks --}}
            <div class="w-full flex justify-center mb-6">
                <img src="{{ asset('images/logo-kegiatan.png') }}" 
                     style="width: auto; max-width: 450px; max-height: 180px; object-fit: contain;">
            </div>

            <h1 style="font-family:'Nunito', sans-serif; color:#002f45; font-size:2.5rem; font-weight:700; line-height:1.2; margin-bottom:1rem;">
                WTHME 2025
            </h1>
            <h2 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.25rem; font-style:italic; margin-bottom:1.5rem;">
                Welcome To Himpunan Mahasiswa Elektro 2025
            </h2>
            <p style="color:#002f45; font-size:0.875rem; max-width:320px; line-height:1.7; opacity: 0.8;">
                Kegiatan orientasi mahasiswa baru Teknik Elektro yang membentuk karakter beretika, loyal, dan kekeluargaan.
            </p>
        </div>
    </div>

    {{-- Panel Kanan: Form Login (Glassmorphism Style) --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 relative"
        style="
        background-image: linear-gradient(rgba(224, 222, 205, 0.6), rgba(224, 222, 205, 0.5)), url('{{ asset('images/background-login.jpg') }}');
        background-size: cover;
        background-position: center;
    ">
        
        <div class="w-full max-w-md z-10 p-8 lg:p-10" 
             style="background: rgba(255, 255, 255, 0.2); 
                    backdrop-filter: blur(15px); 
                    -webkit-backdrop-filter: blur(15px); 
                    border: 1px solid rgba(255, 255, 255, 0.3); 
                    border-radius: 2rem; 
                    box-shadow: 0 8px 32px 0 rgba(0, 47, 69, 0.1);">

            {{-- Mobile Logo: Tetap proporsional tanpa box --}}
            <div class="lg:hidden flex justify-center mb-6">
                <img src="{{ asset('images/logo-kegiatan.png') }}" 
                     style="width: auto; max-width: 280px; max-height: 100px; object-fit: contain;">
            </div>

            <h2 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:700; margin-bottom:0.5rem; text-align: center;">
                Selamat Datang
            </h2>
            <p style="color:#002f45; opacity:0.8; margin-bottom:2rem; font-size:0.9rem; font-weight: 500; text-align: center;">
                Masuk ke akun WTHME kamu
            </p>

            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl" style="background: rgba(254, 226, 226, 0.6); border: 1px solid rgba(153, 27, 27, 0.2); color:#991b1b;">
                    <p class="text-xs font-bold">× {{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl" style="background: rgba(254, 226, 226, 0.6); border: 1px solid rgba(153, 27, 27, 0.2); color:#991b1b;">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs font-bold">× {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform: uppercase;">
                        Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        style="width:100%; padding:0.875rem 1rem; border:1px solid rgba(0, 47, 69, 0.1); border-radius:1rem; 
                               background:rgba(255, 255, 255, 0.4); color:#002f45; font-size:0.9rem; outline:none; transition: all 0.3s;"
                        onfocus="this.style.background='white'; this.style.borderColor='#002f45'" 
                        onblur="this.style.background='rgba(255, 255, 255, 0.4)'; this.style.borderColor='rgba(0, 47, 69, 0.1)'"
                        placeholder="email@example.com">
                </div>

                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform: uppercase;">
                        Password
                    </label>
                    <input type="password" name="password" id="password_input" required
                        style="width:100%; padding:0.875rem 1rem; border:1px solid rgba(0, 47, 69, 0.1); border-radius:1rem; 
                               background:rgba(255, 255, 255, 0.4); color:#002f45; font-size:0.9rem; outline:none; transition: all 0.3s;"
                        onfocus="this.style.background='white'; this.style.borderColor='#002f45'" 
                        onblur="this.style.background='rgba(255, 255, 255, 0.4)'; this.style.borderColor='rgba(0, 47, 69, 0.1)'"
                        placeholder="••••••••">

                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="toggle_password" style="cursor: pointer;">
                        <label for="toggle_password" style="font-size: 0.75rem; color: #002f45; cursor: pointer; font-weight: 600;">
                            Lihat Password
                        </label>
                    </div>
                </div>

                <button type="submit"
                    style="width:100%; padding:1rem; background:#002f45; color:#d2c296; font-weight:700; 
                           font-size:1rem; border:none; border-radius:1rem; cursor:pointer; 
                           transition:transform 0.2s, background 0.2s; box-shadow: 0 4px 15px rgba(0, 47, 69, 0.2);"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.background='#003d59'" 
                    onmouseout="this.style.transform='translateY(0)'; this.style.background='#002f45'">
                    Masuk
                </button>
            </form>

            <div style="text-align:center; margin-top:2rem; color:#002f45; font-weight:600; font-size:0.85rem;">
                Belum punya akun?
                <a href="{{ route('register') }}"
                    style="color:#002f45; font-weight:800; text-decoration:none; border-bottom: 2px solid #d2c296; padding-bottom: 2px;">
                    Daftar sekarang
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password_input');
            const toggleCheckbox = document.getElementById('toggle_password');
            toggleCheckbox.addEventListener('change', function() {
                passwordInput.type = this.checked ? 'text' : 'password';
            });
        });
    </script>

</body>
</html>
