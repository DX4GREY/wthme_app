@extends('layouts.app')

@section('content')
    {{-- Panggil Google Fonts Premium langsung via CDN biar font auto-bagus & halus --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2 family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <div
        style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%); font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
        <div style="max-width:720px; margin:0 auto;">

            {{-- Navigasi Kembali --}}
            <a href="{{ route('peserta.index') }}"
                style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7; transition:0.2s; font-family: 'Plus Jakarta Sans', sans-serif;"
                onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>

            {{-- Header Section --}}
            <div style="margin-bottom:2.5rem;">
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Riwayat Medis <span style="color:#6b705c; font-style:italic;">Peserta</span>
                </h1>
                <p
                    style="color:#002f45; opacity:0.75; font-size:0.95rem; font-weight:500; line-height:1.6; font-family: 'Plus Jakarta Sans', sans-serif; margin-bottom: 1rem;">
                    Silakan isi data medis di bawah ini dengan sebenar-benarnya. Seluruh kolom formulir
                    bersifat
                    <strong>wajib diisi</strong> demi keselamatan Anda selama kegiatan berlangsung.
                </p>

                {{-- Box Contact Person Tim Medis --}}
                <div
                    style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(5px); border-radius: 1rem; border: 1px solid rgba(0, 47, 69, 0.1); padding: 1rem 1.25rem; font-size: 0.85rem; color: #002f45; line-height: 1.5;">
                    <div
                        style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                            </path>
                        </svg>
                        Hubungi Tim Medis / P3K (Jika Ada Kendala):
                    </div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-weight: 600; opacity: 0.9;">
                        <li>Anis Dwi Yanti: <a href="https://wa.me/6281808295796" target="_blank"
                                style="color: #002f45; text-decoration: none; font-weight: 700;">+62 818-0829-5796</a></li>
                        <li>Muhammad Nelvin Junior: <a href="https://wa.me/6287871588815" target="_blank"
                                style="color: #002f45; text-decoration: none; font-weight: 700;">+62 878-7158-8815</a></li>
                        <li>Selvy Irawan: <a href="https://wa.me/6281288227546" target="_blank"
                                style="color: #002f45; text-decoration: none; font-weight: 700;">+62 812-8822-7546</a></li>
                    </ul>
                </div>

            </div>

            {{-- Notifikasi Error Validasi Global --}}
            @if ($errors->any())
                <div
                    style="padding:1rem 1.5rem; background: rgba(239, 68, 68, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(239, 68, 68, 0.3); border-radius:1.25rem; color:#991b1b; margin-bottom:1.5rem; font-size:0.85rem; font-weight:600; font-family: 'Plus Jakarta Sans', sans-serif;">
                    ⚠️ Terjadi kesalahan! Silakan periksa kembali isian formulir yang ditandai tanda bintang merah.
                </div>
            @endif

            {{-- Main Glass Card Form --}}
            <div
                style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); border-radius:2rem; border:1px solid rgba(255, 255, 255, 0.6); padding:2.5rem 2.25rem; box-shadow: 0 20px 50px rgba(0,47,69,0.06);">

                <form method="POST" action="{{ route('peserta.riwayat.store') }}" enctype="multipart/form-data"
                    style="display:flex; flex-direction:column; gap:2.25rem;">
                    @csrf

                    {{-- Identitas Statis --}}
                    <div
                        style="background: rgba(0, 47, 69, 0.07); border-radius:1.25rem; padding:1.25rem 1.5rem; display:flex; gap:1.5rem; flex-wrap:wrap; border: 1px solid rgba(0, 47, 69, 0.05); font-family: 'Plus Jakarta Sans', sans-serif;">
                        <div>
                            <div
                                style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem; font-weight: 700;">
                                Nama Lengkap</div>
                            <div style="color:#002f45; font-weight:700; font-size:0.95rem;">{{ auth()->user()->name }}</div>
                        </div>
                        <div>
                            <div
                                style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem; font-weight: 700;">
                                NIM</div>
                            <div style="color:#002f45; font-weight:700; font-size:0.95rem;">{{ auth()->user()->nim }}</div>
                        </div>
                        <div style="margin-left:auto;">
                            <div
                                style="font-size:0.65rem; color:#002f45; opacity:0.5; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.25rem; font-weight: 700;">
                                Kelompok</div>
                            <div
                                style="color:#d2c296; background:#002f45; padding:0.2rem 0.75rem; border-radius:0.5rem; font-weight:700; font-size:0.85rem; text-align:center;">
                                {{ auth()->user()->kelompok }}</div>
                        </div>
                    </div>

                    {{-- Input Nomor Telepon Pribadi --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Nomor Telephone <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="no_telp" value="{{ old('no_telp', $data->no_telp ?? '') }}"
                            placeholder="Contoh: 081234567xxx" required
                            style="width:100%; padding:0.95rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:0.85rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; box-sizing:border-box; transition:0.2s; font-family: 'Plus Jakarta Sans', sans-serif;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">
                    </div>

                    {{-- Input Nomor Telepon Orang Tua/Wali --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Nomor Telepon Orang Tua/Wali <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="no_telp_ortu"
                            value="{{ old('no_telp_ortu', $data->no_telp_ortu ?? '') }}" placeholder="Contoh: 089876543xxx"
                            required
                            style="width:100%; padding:0.95rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid {{ $errors->has('no_telp_ortu') ? '#ef4444' : 'rgba(0,47,69,0.15)' }}; border-radius:0.85rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; box-sizing:border-box; transition:0.2s; font-family: 'Plus Jakarta Sans', sans-serif;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">
                    </div>

                    {{-- Alamat Rumah --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Alamat Rumah <span style="color:#ef4444;">*</span>
                        </label>
                        <textarea name="alamat_rumah" rows="2" placeholder="Tuliskan alamat lengkap tempat tinggal atau kost saat ini..."
                            required
                            style="width:100%; padding:1rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; resize:vertical; font-family:'Plus Jakarta Sans', sans-serif; box-sizing:border-box; transition:0.2s;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">{{ old('alamat_rumah', $data->alamat_rumah ?? '') }}</textarea>
                    </div>

                    <hr style="border: none; border-top: 1px dashed rgba(0,47,69,0.2); margin: 0.5rem 0;">

                    {{-- Q1: Riwayat Penyakit --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.04em; line-height:1.5; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Apakah kamu memiliki riwayat alergi atau penyakit tertentu? Jika ya, silakan berikan rincian
                            <span style="color:#ef4444;">*</span>
                        </label>
                        <div
                            style="background: rgba(0, 47, 69, 0.05); padding: 1rem 1.25rem; border-left: 4px solid #6b705c; border-radius: 0.5rem 1rem 1rem 0.5rem; font-size:0.825rem; color:#002f45; line-height:1.6; margin-bottom:0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            <span style="opacity: 0.8;">Seperti penyakit kronis, kondisi kesehatan yang sedang diobati, atau
                                riwayat cedera yang relevan. Jika ada riwayat medis yang signifikan, penting untuk
                                memberikan informasi rinci kepada panitia P3K agar dapat merespons dengan tepat jika ada
                                kejadian terkait.</span>
                            <div style="margin-top: 0.5rem;"><strong>Contoh:</strong> "Ya, saya memiliki riwayat asma. Saya
                                memiliki inhaler yang harus saya gunakan jika saya mengalami sesak napas atau gejala asma
                                lainnya. Saya akan memastikan inhaler tersebut selalu tersedia dan mudah diakses jika
                                dibutuhkan."</div>
                        </div>
                        <textarea name="riwayat_penyakit" rows="3" required
                            placeholder='Contoh: "Ya, saya memiliki riwayat asma..." atau isi "-" jika tidak ada'
                            style="width:100%; padding:1rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; resize:vertical; font-family:'Plus Jakarta Sans', sans-serif; box-sizing:border-box; transition:0.2s;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">{{ old('riwayat_penyakit', $data->riwayat_penyakit ?? '') }}</textarea>
                    </div>

                    {{-- Q2: Konsumsi Obat Rutin --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.04em; line-height:1.5; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Apakah kamu sedang mengonsumsi obat-obatan tertentu? Jika ya, silakan berikan rincian <span
                                style="color:#ef4444;">*</span>
                        </label>
                        <div
                            style="background: rgba(0, 47, 69, 0.05); padding: 1rem 1.25rem; border-left: 4px solid #6b705c; border-radius: 0.5rem 1rem 1rem 0.5rem; font-size:0.825rem; color:#002f45; line-height:1.6; margin-bottom:0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            <span style="opacity: 0.8;">Jika sedang mengonsumsi obat-obatan tertentu, baik untuk kondisi
                                jangka pendek maupun jangka panjang, penting untuk memberikan rincian tentang nama obat,
                                dosis, dan frekuensi penggunaan.</span>
                            <div style="margin-top: 0.5rem; white-space: pre-line;"><strong>Contoh:</strong> "Ya, saat ini
                                saya sedang mengonsumsi obat-obatan tertentu. Berikut adalah rincian mengenai obat yang saya
                                konsumsi:
                                1. Nama obat: Paracetamol
                                - Dosis: 500 mg
                                - Frekuensi: Dikonsumsi saat perlu untuk mengurangi demam atau nyeri.
                                2. Nama obat: Cetirizine
                                - Dosis: 10 mg
                                - Frekuensi: Dikonsumsi setiap hari sebelum tidur untuk mengatasi alergi."</div>
                        </div>
                        <textarea name="obat_rutin" rows="5" required
                            placeholder='Contoh: "Ya, saat ini saya sedang mengonsumsi..." atau isi "-" jika tidak ada'
                            style="width:100%; padding:1rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; resize:vertical; font-family:'Plus Jakarta Sans', sans-serif; box-sizing:border-box; transition:0.2s;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">{{ old('obat_rutin', $data->obat_rutin ?? '') }}</textarea>
                    </div>

                    {{-- Q3: Riwayat Cedera --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.04em; line-height:1.5; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Apakah kamu memiliki riwayat cedera atau kondisi kesehatan yang pernah memerlukan pertolongan
                            pertama atau penanganan medis sebelumnya? Jika ya, mohon berikan rincian. <span
                                style="color:#ef4444;">*</span>
                        </label>
                        <div
                            style="background: rgba(0, 47, 69, 0.05); padding: 1rem 1.25rem; border-left: 4px solid #6b705c; border-radius: 0.5rem 1rem 1rem 0.5rem; font-size:0.825rem; color:#002f45; line-height:1.6; margin-bottom:0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            <span style="opacity: 0.8;">Dapat berupa cedera olahraga sebelumnya, patah tulang, luka yang
                                memerlukan jahitan, kondisi medis yang memerlukan perawatan khusus seperti asma, epilepsi,
                                atau diabetes.</span>
                            <div style="margin-top: 0.5rem;"><strong>Contoh:</strong> "Ya, saya pernah mengalami cedera
                                olahraga pada tahun lalu di mana saya mengalami patah tulang pergelangan tangan kanan. Saya
                                menjalani perawatan di rumah sakit dan dipasang gips."</div>
                        </div>
                        <textarea name="riwayat_cedera" rows="3" required
                            placeholder='Contoh: "Ya, saya pernah mengalami cedera olahraga..." atau isi "-" jika tidak ada'
                            style="width:100%; padding:1rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; resize:vertical; font-family:'Plus Jakarta Sans', sans-serif; box-sizing:border-box; transition:0.2s;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">{{ old('riwayat_cedera', $data->riwayat_cedera ?? '') }}</textarea>
                    </div>

                    {{-- Q4: Alergi Makanan --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.04em; line-height:1.5; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Alergi Makanan <span style="color:#ef4444;">*</span>
                        </label>
                        <div
                            style="background: rgba(0, 47, 69, 0.05); padding: 1rem 1.25rem; border-left: 4px solid #6b705c; border-radius: 0.5rem 1rem 1rem 0.5rem; font-size:0.825rem; color:#002f45; line-height:1.6; margin-bottom:0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            <span style="opacity: 0.8;">Berikan informasi tentang alergi makanan tertentu seperti gluten,
                                kacang-kacangan, makanan laut, telur, susu, atau bahan makanan lainnya.</span>
                            <div style="margin-top: 0.5rem;"><strong>Contoh:</strong> "Ya, saya memiliki riwayat alergi
                                terhadap makanan. Saya alergi terhadap kacang-kacangan. Jika saya mengonsumsi makanan yang
                                mengandung kacang, kulit saya gatal dan ruam."</div>
                        </div>
                        <textarea name="alergi_makanan" rows="3" required
                            placeholder='Contoh: "Ya, saya memiliki riwayat alergi terhadap makanan..." atau isi "-" jika tidak ada'
                            style="width:100%; padding:1rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; resize:vertical; font-family:'Plus Jakarta Sans', sans-serif; box-sizing:border-box; transition:0.2s;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">{{ old('alergi_makanan', $data->alergi_makanan ?? '') }}</textarea>
                    </div>

                    {{-- Q5: Keterangan Tambahan --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.04em; line-height:1.5; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Informasi Tambahan <span style="color:#ef4444;">*</span>
                        </label>
                        <div
                            style="background: rgba(0, 47, 69, 0.05); padding: 1rem 1.25rem; border-left: 4px solid #6b705c; border-radius: 0.5rem 1rem 1rem 0.5rem; font-size:0.825rem; color:#002f45; line-height:1.6; margin-bottom:0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            <span style="opacity: 0.8;">Apakah ada hal lain yang perlu kami ketahui terkait kebutuhan P3K
                                anda atau informasi kesehatan lainnya yang relevan?</span>
                            <div style="margin-top: 0.5rem; white-space: pre-line;"><strong>Contoh:</strong> "Ya, ada
                                beberapa hal lain yang perlu diketahui:
                                1. Riwayat alergi obat: Alergi terhadap antibiotik golongan penisilin.
                                2. Kontak darurat: Nomor telepon Kakak (085712345xxx) jika terjadi keadaan darurat."</div>
                        </div>
                        <textarea name="keterangan_tambahan" rows="6" required
                            placeholder='Contoh rincian poin seperti di atas atau isi "-" jika tidak ada'
                            style="width:100%; padding:1rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:1rem; font-size:0.95rem; color:#002f45; font-weight:600; outline:none; resize:vertical; font-family:'Plus Jakarta Sans', sans-serif; box-sizing:border-box; transition:0.2s;"
                            onfocus="this.style.borderColor='#002f45'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(0,47,69,0.05)';">{{ old('keterangan_tambahan', $data->keterangan_tambahan ?? '') }}</textarea>
                    </div>

                    {{-- Q6: Upload Bukti Medis (File) --}}
                    <div>
                        <label
                            style="display:block; font-size:0.85rem; font-weight:800; color:#002f45; margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.04em; line-height:1.5; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Bukti Riwayat Penyakit <span
                                style="color:#6b705c; font-size:0.75rem; font-weight:600; text-transform:lowercase;">(Opsional)</span>
                        </label>
                        <div
                            style="background: rgba(0, 47, 69, 0.05); padding: 1rem 1.25rem; border-left: 4px solid #6b705c; border-radius: 0.5rem 1rem 1rem 0.5rem; font-size:0.825rem; color:#002f45; line-height:1.6; margin-bottom:0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            <span style="opacity: 0.8;">Jika ada, silakan sertakan bukti riwayat penyakit (Surat
                                Dokter/Foto Obat) dengan format Gambar atau PDF. Jika tidak memiliki riwayat penyakit atau
                                tidak ada berkas, kolom ini <strong>boleh dikosongkan</strong>. <strong
                                    style="color: #991b1b;">(Maksimal ukuran file: 5 MB)</strong></span>
                        </div>

                        {{-- 🔴 PERBAIKAN: Menghapus atribut 'required' agar bisa dilewati --}}
                        <input type="file" name="bukti_kesehatan" accept="image/*,application/pdf"
                            style="width:100%; padding:0.75rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.15); border-radius:0.85rem; font-size:0.9rem; color:#002f45; font-weight:600; font-family: 'Plus Jakarta Sans', sans-serif;">

                        @if ($data && $data->bukti_kesehatan)
                            <div
                                style="margin-top:0.6rem; font-size:0.85rem; color:#002f45; font-weight:600; font-family: 'Plus Jakarta Sans', sans-serif;">
                                📂 Bukti saat ini: <a href="{{ asset('storage/' . $data->bukti_kesehatan) }}"
                                    target="_blank" style="color:#002f45; text-decoration:underline;">Lihat Dokumen
                                    Terunggah</a>
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Submit --}}
                    <button type="submit"
                        style="margin-top:1rem; padding:1.2rem; background:#002f45; color:#d2c296; font-weight:800; border:none; border-radius:1.25rem; cursor:pointer; font-size:1rem; box-shadow: 0 10px 20px rgba(0,47,69,0.2); transition:0.3s; font-family: 'Plus Jakarta Sans', sans-serif;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,47,69,0.3)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px rgba(0,47,69,0.2)';">
                        {{ $data ? '🔄 Perbarui Formulir Medis' : '💾 Simpan Formulir Medis' }}
                    </button>

                </form>
            </div>

            <p
                style="text-align:center; color:#002f45; opacity:0.4; font-size:0.8rem; margin-top:2.5rem; font-weight:600; font-family: 'Plus Jakarta Sans', sans-serif;">
                Seluruh informasi data medis tersimpan dengan enkripsi aman sistem WTHME 2025.
            </p>

        </div>
    </div>
@endsection
