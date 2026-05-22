@extends('layouts.app')

@section('content')
<div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem;">
<div style="max-width:500px; margin:0 auto;">

    <a href="{{ route('panitia.index') }}" style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
        ← Kembali ke Portal Panitia
    </a>

    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.75rem; font-weight:700; margin-bottom:0.5rem;">
        Generate QR Absensi
    </h1>
    <p style="color:#002f45; opacity:0.5; font-size:0.875rem; margin-bottom:2rem;">
        Buat QR Code untuk satu sesi kegiatan. QR bisa dimatikan kapan saja.
    </p>

    @if ($errors->any())
    <div style="padding:1rem; background:#fee2e2; border-radius:0.75rem; color:#991b1b; margin-bottom:1.5rem; font-size:0.875rem;">
        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <div style="background:white; border-radius:1rem; padding:2rem; border:2px solid #bdd1d3;">
        <form method="POST" action="{{ route('panitia.qr.store') }}" style="display:flex; flex-direction:column; gap:1.25rem;">
            @csrf

            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                    Nama Sesi *
                </label>
                <input type="text" name="nama_sesi" value="{{ old('nama_sesi') }}" required
                    style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'"
                    placeholder="Contoh: Hari 1 - Pembukaan PKKMB">
                <p style="color:#002f45; opacity:0.4; font-size:0.75rem; margin-top:0.35rem;">Nama ini muncul di rekap absensi.</p>
            </div>

            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.05em;">
                    QR Ini Untuk *
                </label>
                <div style="display:flex; gap:0.75rem;">
                    <label style="flex:1; display:flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.75rem;
                                  border:2px solid #bdd1d3; border-radius:0.6rem; cursor:pointer; transition:all 0.15s;"
                           id="label-peserta"
                           onclick="selectUntuk('peserta')">
                        <input type="radio" name="untuk" value="peserta" style="display:none;"
                               {{ old('untuk', 'peserta') === 'peserta' ? 'checked' : '' }}>
                        <span style="font-size:1.1rem;">👥</span>
                        <span style="color:#002f45; font-weight:600; font-size:0.9rem;">Peserta</span>
                    </label>
                    <label style="flex:1; display:flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.75rem;
                                  border:2px solid #bdd1d3; border-radius:0.6rem; cursor:pointer; transition:all 0.15s;"
                           id="label-panitia"
                           onclick="selectUntuk('panitia')">
                        <input type="radio" name="untuk" value="panitia" style="display:none;"
                               {{ old('untuk') === 'panitia' ? 'checked' : '' }}>
                        <span style="font-size:1.1rem;">🎗</span>
                        <span style="color:#002f45; font-weight:600; font-size:0.9rem;">Panitia</span>
                    </label>
                </div>
                {{-- Tambahkan ini setelah div field "Untuk" --}}
                <div id="rotating-section" style="display:none;">
                    <div style="background:#e0decd; border-radius:0.75rem; padding:1rem; border-left:3px solid #002f45;">
                        <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer; margin-bottom:0.75rem;">
                            <input type="checkbox" name="rotating" id="rotating-check" value="1"
                                {{ old('rotating') ? 'checked' : '' }}
                                style="width:18px; height:18px; accent-color:#002f45;">
                            <div>
                                <div style="color:#002f45; font-weight:700; font-size:0.875rem;">🔄 Aktifkan Rotating QR</div>
                                <div style="color:#002f45; opacity:0.5; font-size:0.75rem;">QR otomatis berubah tiap beberapa detik (disarankan untuk absensi di aula)</div>
                            </div>
                        </label>

                        <div id="interval-section" style="display:none;">
                            <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                                Interval Pergantian QR
                            </label>
                            <select name="rotate_interval"
                                style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; background:white; outline:none;">
                                <option value="30"  {{ old('rotate_interval') == 30  ? 'selected' : '' }}>30 detik (rekomendasi)</option>
                                <option value="45"  {{ old('rotate_interval') == 45  ? 'selected' : '' }}>45 detik</option>
                                <option value="60"  {{ old('rotate_interval') == 60  ? 'selected' : '' }}>60 detik</option>
                            </select>
                        </div>
                    </div>
                </div>

                <script>
                // Tampilkan opsi rotating hanya kalau pilih Peserta
                function updateRotatingSection() {
                    const val = document.querySelector('input[name="untuk"]:checked')?.value;
                    document.getElementById('rotating-section').style.display = val === 'peserta' ? 'block' : 'none';
                }

                document.querySelectorAll('input[name="untuk"]').forEach(r => {
                    r.addEventListener('change', updateRotatingSection);
                });

                document.getElementById('rotating-check').addEventListener('change', function() {
                    document.getElementById('interval-section').style.display = this.checked ? 'block' : 'none';
                });

                updateRotatingSection();
                </script>
            </div>

            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#002f45; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em;">
                    Batas Waktu
                    <span style="font-weight:400; text-transform:none; opacity:0.5; font-size:0.75rem;"> (opsional)</span>
                </label>
                <input type="datetime-local" name="berlaku_hingga" value="{{ old('berlaku_hingga') }}"
                    style="width:100%; padding:0.75rem 1rem; border:2px solid #bdd1d3; border-radius:0.6rem; font-size:0.9rem; color:#002f45; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#002f45'" onblur="this.style.borderColor='#bdd1d3'">
                <p style="color:#002f45; opacity:0.4; font-size:0.75rem; margin-top:0.35rem;">Kosongkan jika QR tidak perlu expired otomatis.</p>
            </div>

            <button type="submit"
                style="padding:0.875rem; background:#002f45; color:#d2c296; font-weight:700; border:none; border-radius:0.75rem; cursor:pointer; font-size:0.95rem;"
                onmouseover="this.style.background='#00405e'" onmouseout="this.style.background='#002f45'">
                🔲 Generate QR Code
            </button>
        </form>
    </div>

</div>
</div>

<script>
function selectUntuk(val) {
    ['peserta','panitia'].forEach(v => {
        const el = document.getElementById('label-' + v);
        if (v === val) {
            el.style.borderColor = '#002f45';
            el.style.background  = '#002f45';
            el.querySelector('span:last-child').style.color = 'white';
        } else {
            el.style.borderColor = '#bdd1d3';
            el.style.background  = 'white';
            el.querySelector('span:last-child').style.color = '#002f45';
        }
    });
    document.querySelector(`input[value="${val}"]`).checked = true;
}
// set default
selectUntuk('{{ old('untuk', 'peserta') }}');
</script>
@endsection
