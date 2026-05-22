<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password — PKKMB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#e0decd; font-family:'Plus Jakarta Sans',sans-serif; padding:2rem;">

    <div style="width:100%; max-width:440px;">

        <div style="background:#002f45; border-radius:1.25rem; padding:2rem; margin-bottom:1.5rem; text-align:center;">
            <div style="font-size:2rem; margin-bottom:0.5rem;">🔐</div>
            <h1 style="font-family:'Playfair Display',serif; color:#d2c296; font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">
                Ganti Password
            </h1>
            <p style="color:#bdd1d3; font-size:0.8rem; opacity:0.8;">
                Halo, <strong>{{ auth()->user()->name }}</strong>!<br>
                Password kamu saat ini adalah password sementara.<br>
                Buat password baru untuk melanjutkan.
            </p>
        </div>

        <div style="background:white; border-radius:1.25rem; padding:2rem; box-shadow:0 4px 24px rgba(0,47,69,0.1);">

            @if ($errors->any())
            <div style="margin-bottom:1.5rem; padding:1rem; background:#fee2e2; border-radius:0.75rem; color:#991b1b; font-size:0.875rem;">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}" style="display:flex; flex-direction:column; gap:1.25rem;">
                @csrf
                @method('PUT')

                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                        Password Baru *
                    </label>
                    <input type="password" name="password" required
                        style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                        onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                        placeholder="Minimal 8 karakter">
                </div>

                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                        Konfirmasi Password Baru *
                    </label>
                    <input type="password" name="password_confirmation" required
                        style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                        onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                        placeholder="Ulangi password baru">
                </div>

                <button type="submit"
                    style="padding:0.875rem; background:#002f45; color:#d2c296; font-weight:700; border:none; border-radius:0.75rem; cursor:pointer; font-size:0.95rem;"
                    onmouseover="this.style.background='#00405e'" onmouseout="this.style.background='#002f45'">
                    Simpan Password Baru
                </button>
            </form>
        </div>

    </div>
</body>
</html>