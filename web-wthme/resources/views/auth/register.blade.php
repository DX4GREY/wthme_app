<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — WTHME</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet">
</head>

<body
    style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; background:#e0decd; font-family:'Plus Jakarta Sans',sans-serif;">

    <div style="width:100%; max-width:480px;">
        <div style="text-align:center; margin-bottom:2rem;">
            <h1
                style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:700; margin-bottom:0.25rem;">
                Pendaftaran Peserta
            </h1>
            <p style="color:#002f45; opacity:0.6; font-size:0.875rem;">WTHME 2025</p>
        </div>

        <div style="background:white; border-radius:1.25rem; padding:2rem; box-shadow:0 4px 24px rgba(0,47,69,0.1);">

            @if ($errors->any())
                <div
                    style="margin-bottom:1.5rem; padding:1rem; background:#fee2e2; border-radius:0.75rem; border:1px solid #fca5a5;">
                    @foreach ($errors->all() as $error)
                        <p style="font-size:0.875rem; color:#991b1b; margin:0.25rem 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}"
                style="display:flex; flex-direction:column; gap:1.25rem;">
                @csrf

                <div style="display:grid; gap:1.25rem;">

                    <div>
                        <label
                            style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                            Nama Lengkap *
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                            onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                            placeholder="Nama sesuai KTP">
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <label
                                style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                                NIM *
                            </label>
                            <input type="text" name="nim" value="{{ old('nim') }}" required
                                style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                                onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                                placeholder="Nomor Induk">
                        </div>
                        <div>
                            <label
                                style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                                Angkatan *
                            </label>
                            <input type="number" name="angkatan" value="{{ old('angkatan') }}" required min="2000"
                                max="2099" oninput="if (this.value.length > 4) this.value = this.value.slice(0, 4)"
                                style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                                onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                                placeholder="2025">
                        </div>
                        <div>
                            <label
                                style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                                Jenis Kelamin *
                            </label>
                            <select name="gender" required
                                style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; background:white; box-sizing:border-box; outline:none;"
                                onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'">
                                <option value="">-- Pilih Gender --</option>
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label
                            style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                            Kelompok *
                        </label>
                        <select name="kelompok" required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; background:white; box-sizing:border-box; outline:none;"
                            onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'">
                            <option value="">-- Pilih Kelompok --</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ old('kelompok') == $i ? 'selected' : '' }}>
                                    Kelompok {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label
                            style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                            Email *
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                            onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                            placeholder="email@example.com">
                    </div>

                    <div>
                        <label
                            style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                            Password *
                        </label>
                        <input type="password" name="password" required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                            onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                            placeholder="Minimal 8 karakter">
                    </div>

                    <div>
                        <label
                            style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                            Konfirmasi Password *
                        </label>
                        <input type="password" name="password_confirmation" required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                            onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                            placeholder="Ulangi password">
                    </div>
                    <div>
                        <label
                            style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase;">
                            Kode Registrasi *
                        </label>
                        <input type="text" name="registration_code" required
                            style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; outline:none;"
                            placeholder="Masukkan kode dari panitia">
                    </div>

                </div>

                <button type="submit"
                    style="padding:0.875rem; background:#002f45; color:#d2c296; font-weight:700; border:none; border-radius:0.75rem; cursor:pointer; font-size:0.95rem; letter-spacing:0.025em; transition:background 0.2s;"
                    onmouseover="this.style.background='#00405e'" onmouseout="this.style.background='#002f45'">
                    Daftar Sekarang
                </button>

            </form>
        </div>

        <div style="text-align:center; margin-top:1.5rem; color:#002f45; opacity:0.6; font-size:0.875rem;">
            Sudah punya akun?
            <a href="{{ route('login') }}" style="color:#002f45; font-weight:700; opacity:1; text-decoration:none;">
                Masuk di sini
            </a>
        </div>
    </div>

</body>

</html>
