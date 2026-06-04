@extends('layouts.app')

@section('content')
    {{-- Background Ambient --}}
    <div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 3rem 1.5rem;">
        <div style="max-width:900px; margin:0 auto;">

            {{-- Header --}}
            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; font-weight:600;">
                        <span style="font-size:1.1rem;">←</span> Kembali</a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Kelola <span style="color:#6b705c; font-style:italic;">Tugas</span>
                    </h1>
                    <p style="color:#002f45; opacity:0.6; font-size:0.95rem; margin-top:0.5rem;">Buat dan kelola jenis tugas yang harus dikumpulkan peserta</p>
                </div>
                <div style="display:flex; gap:0.75rem; align-items: center;">
                    <a href="{{ route('panitia.tugas.rekap') }}"
                        style="padding:0.75rem 1.5rem; background:rgba(255,255,255,0.5); backdrop-filter:blur(10px); color:#002f45; border-radius:0.75rem; text-decoration:none; font-size:0.875rem; font-weight:700; border:1px solid rgba(189,209,211,0.6); transition:all 0.3s ease;">
                        📊 Lihat Rekap
                    </a>
                    @if (auth()->user()->isAcara())
                        <button onclick="toggleForm()"
                            style="padding:0.75rem 1.5rem; background:#002f45; color:#d2c296; border-radius:0.75rem; border:none; cursor:pointer; font-size:0.875rem; font-weight:700; box-shadow:0 10px 20px rgba(0,47,69,0.2); transition:all 0.3s ease;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,47,69,0.3)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px rgba(0,47,69,0.2)';">
                            + Buat Tugas Baru
                        </button>
                    @endif
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div id="flash-message-success"
                    style="padding:1rem; background:rgba(220,252,231,0.7); backdrop-filter:blur(10px); border:1px solid #86efac; border-radius:1rem; color:#166534; margin-bottom:1.5rem; font-size:0.875rem; display:flex; align-items:center; gap:0.5rem;">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div id="flash-message-error"
                    style="padding:1rem; background:rgba(254,226,226,0.7); backdrop-filter:blur(10px); border:1px solid #fca5a5; border-radius:1rem; color:#991b1b; margin-bottom:1.5rem; font-size:0.875rem;">
                    @foreach ($errors->all() as $e)
                        <p style="margin:0.25rem 0;">⚠️ {{ $e }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Form Buat Tugas (Glass Style) --}}
            <div id="form-tugas" style="display:none; margin-bottom:2.5rem;">
                <div style="background:rgba(255,255,255,0.7); backdrop-filter:blur(15px); border-radius:1.5rem; padding:2rem; border:1px solid rgba(255,255,255,0.5); box-shadow:0 20px 40px rgba(0,0,0,0.05);">
                    <h3 style="color:#002f45; font-weight:700; font-size:1.15rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem; font-family:'Playfair Display',serif;">
                        ✏️ Buat Jenis Tugas Baru
                        <button onclick="toggleForm()"
                            style="margin-left:auto; background:rgba(0,47,69,0.05); border:none; color:#002f45; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:1.25rem; display:flex; align-items:center; justify-content:center;">&times;</button>
                    </h3>

                    <form method="POST" action="{{ route('panitia.tugas.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">
                            <div style="grid-column:1/-1;">
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Nama Tugas *</label>
                                <input type="text" name="nama_tugas" value="{{ old('nama_tugas') }}" required
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.95rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none; transition:border-color 0.3s;"
                                    onfocus="this.style.borderColor='#002f45'; this.style.background='white';"
                                    onblur="this.style.borderColor='rgba(189,209,211,0.8)'; this.style.background='rgba(255,255,255,0.5)';"
                                    placeholder="Misal: Essay Motivasi WTHME">
                            </div>

                            <div style="grid-column:1/-1;">
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Deskripsi / Petunjuk Ringkas</label>
                                <textarea name="deskripsi" rows="3"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none; resize:vertical; font-family:inherit;"
                                    onfocus="this.style.borderColor='#002f45'; this.style.background='white';"
                                    onblur="this.style.borderColor='rgba(189,209,211,0.8)'; this.style.background='rgba(255,255,255,0.5)';"
                                    placeholder="Jelaskan ketentuan tugas secara singkat...">{{ old('deskripsi') }}</textarea>
                            </div>

                            <div style="grid-column:1/-1;">
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Lampiran File Petunjuk (PDF/Gambar)</label>
                                <input type="file" name="file_petunjuk"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none;">
                                <p style="font-size: 0.7rem; color: #6b705c; margin-top: 0.25rem;">* Opsional. Muncul sebagai tombol download di sisi peserta.</p>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Deadline</label>
                                <input type="datetime-local" name="deadline" value="{{ old('deadline') }}"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none;">
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Tipe File *</label>
                                <select name="tipe_file" style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none;">
                                    <option value="semua" {{ old('tipe_file') === 'semua' ? 'selected' : '' }}>Semua Format</option>
                                    <option value="pdf" {{ old('tipe_file') === 'pdf' ? 'selected' : '' }}>PDF Saja</option>
                                    <option value="gambar" {{ old('tipe_file') === 'gambar' ? 'selected' : '' }}>Gambar (JPG/PNG)</option>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Maks. Ukuran</label>
                                <select name="maks_ukuran" style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none;">
                                    <option value="2048" {{ old('maks_ukuran') == 2048 ? 'selected' : '' }}>2 MB</option>
                                    <option value="5120" {{ old('maks_ukuran') == 5120 ? 'selected' : '' }} selected>5 MB</option>
                                    <option value="10240" {{ old('maks_ukuran') == 10240 ? 'selected' : '' }}>10 MB</option>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Urutan Tampil</label>
                                <input type="number" name="urutan" value="{{ old('urutan', 0) }}"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:rgba(255,255,255,0.5); outline:none;">
                            </div>
                        </div>

                        <div style="display:flex; gap:1rem;">
                            <button type="submit" style="flex:1; padding:1rem; background:#002f45; color:#d2c296; font-weight:700; border:none; border-radius:1rem; cursor:pointer; font-size:0.95rem; box-shadow:0 10px 20px rgba(0,47,69,0.15);">
                                🗂 Simpan Tugas
                            </button>
                            <button type="button" onclick="toggleForm()" style="padding:1rem 2rem; background:transparent; color:#002f45; border:1px solid rgba(0,47,69,0.2); border-radius:1rem; cursor:pointer; font-size:0.875rem; font-weight:600;">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Edit Tugas --}}
            <div id="form-edit-tugas" style="display:none; margin-bottom:2.5rem;">
                <div style="background:rgba(255,255,255,0.85); backdrop-filter:blur(15px); border-radius:1.5rem; padding:2rem; border:1px solid rgba(217,119,6,0.3); box-shadow:0 20px 40px rgba(0,0,0,0.08);">
                    <h3 style="color:#002f45; font-weight:700; font-size:1.15rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem; font-family:'Playfair Display',serif;">
                        ✏️ Edit Kategori Tugas: <span id="edit-title-nama" style="color:#d97706; font-style:italic;"></span>
                        <button onclick="closeEditForm()"
                            style="margin-left:auto; background:rgba(0,47,69,0.05); border:none; color:#002f45; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:1.25rem; display:flex; align-items:center; justify-content:center;">&times;</button>
                    </h3>

                    <form id="form-action-edit" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">
                            <div style="grid-column:1/-1;">
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Nama Tugas *</label>
                                <input type="text" name="nama_tugas" id="edit_nama_tugas" required
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.95rem; color:#002f45; background:white; outline:none;">
                            </div>

                            <div style="grid-column:1/-1;">
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Deskripsi / Petunjuk Ringkas</label>
                                <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:white; outline:none; resize:vertical; font-family:inherit;"></textarea>
                            </div>

                            <div style="grid-column:1/-1;">
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Ganti File Petunjuk (Biarkan Kosong Jika Tidak Diubah)</label>
                                <input type="file" name="file_petunjuk" style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:white; outline:none;">
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Deadline</label>
                                <input type="datetime-local" name="deadline" id="edit_deadline"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:white; outline:none;">
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Tipe File *</label>
                                <select name="tipe_file" id="edit_tipe_file" style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:white; outline:none;">
                                    <option value="semua">Semua Format</option>
                                    <option value="pdf">PDF Saja</option>
                                    <option value="gambar">Gambar (JPG/PNG)</option>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Maks. Ukuran</label>
                                <select name="maks_ukuran" id="edit_maks_ukuran" style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:white; outline:none;">
                                    <option value="2048">2 MB</option>
                                    <option value="5120">5 MB</option>
                                    <option value="10240">10 MB</option>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.7rem; font-weight:700; color:#002f45; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.1em; opacity:0.6;">Urutan Tampil</label>
                                <input type="number" name="urutan" id="edit_urutan"
                                    style="width:100%; padding:0.85rem 1.25rem; border:1px solid rgba(189,209,211,0.8); border-radius:0.8rem; font-size:0.9rem; color:#002f45; background:white; outline:none;">
                            </div>
                        </div>

                        <div style="display:flex; gap:1rem;">
                            <button type="submit" style="flex:1; padding:1rem; background:#d97706; color:white; font-weight:700; border:none; border-radius:1rem; cursor:pointer; font-size:0.95rem; box-shadow:0 10px 20px rgba(217,119,6,0.2);">
                                💾 Perbarui Data Tugas
                            </button>
                            <button type="button" onclick="closeEditForm()" style="padding:1rem 2rem; background:transparent; color:#002f45; border:1px solid rgba(0,47,69,0.2); border-radius:1rem; cursor:pointer; font-size:0.875rem; font-weight:600;">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Daftar Tugas --}}
            @if ($tugasList->isEmpty())
                <div style="background:rgba(255,255,255,0.5); backdrop-filter:blur(10px); border-radius:1.5rem; padding:4rem 2rem; text-align:center; border:1px solid rgba(255,255,255,0.4);">
                    <div style="font-size:3.5rem; margin-bottom:1rem; opacity:0.8;">📂</div>
                    <p style="color:#002f45; opacity:0.5; font-size:1.1rem; margin-bottom:1.5rem;">Belum ada jenis tugas yang dibuat.</p>
                    @if (auth()->user()->isAcara())
                        <button onclick="toggleForm()" style="padding:0.85rem 2.5rem; background:#002f45; color:#d2c296; border:none; border-radius:0.8rem; cursor:pointer; font-weight:700;">
                            + Mulai Buat Tugas
                        </button>
                    @endif
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:1.25rem;">
                    @foreach ($tugasList as $tugas)
                        <div style="background:rgba(255,255,255,0.6); backdrop-filter:blur(10px); border-radius:1.5rem; padding:1.5rem; border:1px solid {{ $tugas->aktif ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.05)' }}; transition:all 0.3s ease; opacity:{{ $tugas->aktif ? '1' : '0.7' }}; box-shadow:0 10px 30px rgba(0,0,0,0.02);">
                            <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">

                                {{-- Info utama --}}
                                <div style="flex:1; min-width:250px;">
                                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem; flex-wrap:wrap;">
                                        <h3 style="color:#002f45; font-weight:700; font-size:1.1rem; margin:0; font-family:'Playfair Display',serif;">
                                            {{ $tugas->nama_tugas }}</h3>
                                        <span style="padding:0.25rem 0.75rem; border-radius:2rem; font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; {{ $tugas->aktif ? 'background:#dcfce7; color:#166534;' : 'background:#f3f4f6; color:#6b7280;' }}">
                                            {{ $tugas->aktif ? '● Aktif' : '● Nonaktif' }}
                                        </span>
                                        @if ($tugas->isTerlambat())
                                            <span style="padding:0.25rem 0.75rem; border-radius:2rem; font-size:0.65rem; font-weight:800; background:#fee2e2; color:#991b1b; text-transform:uppercase;">
                                                ⏰ Tutup
                                            </span>
                                        @endif
                                    </div>
                                    @if ($tugas->deskripsi)
                                        <p style="color:#002f45; opacity:0.6; font-size:0.85rem; margin:0 0 0.75rem 0; line-height:1.6;">
                                            {{ $tugas->deskripsi }}</p>
                                    @endif

                                    {{-- Info Lampiran jika ada --}}
                                    @if ($tugas->file_petunjuk)
                                        <div style="margin-bottom:0.75rem;">
                                            <span style="font-size:0.75rem; color:#6b705c; display:flex; align-items:center; gap:0.3rem;">
                                                📄 Ada file petunjuk
                                            </span>
                                        </div>
                                    @endif

                                    <div style="display:flex; gap:1.25rem; flex-wrap:wrap; align-items:center;">
                                        @if ($tugas->deadline)
                                            <span style="color:#d97706; font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:0.3rem;">
                                                <span style="font-size:0.9rem;">⏳</span>
                                                {{ $tugas->deadline->format('d M, H:i') }}
                                            </span>
                                        @endif
                                        <span style="color:#002f45; opacity:0.4; font-size:0.75rem; font-weight:500;">
                                            📎 {{ strtoupper($tugas->tipe_file) }} · {{ round($tugas->maks_ukuran / 1024, 0) }}MB
                                        </span>
                                        <span style="color:#002f45; opacity:0.4; font-size:0.75rem; font-weight:500;">
                                            #{{ $tugas->urutan }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div style="text-align:center; padding:0 1.5rem; border-left:1px solid rgba(0,47,69,0.1); border-right:1px solid rgba(0,47,69,0.1);">
                                    <div style="color:#002f45; font-size:1.75rem; font-weight:800; font-family:'Playfair Display',serif; line-height:1;">
                                        {{ $tugas->pengumpulan_count }}
                                    </div>
                                    <div style="color:#002f45; opacity:0.4; font-size:0.6rem; text-transform:uppercase; letter-spacing:0.1em; margin-top:0.3rem; font-weight:700;">
                                        Pengumpulan</div>
                                    @if ($totalPeserta > 0)
                                        <div style="margin-top:0.6rem; background:rgba(0,47,69,0.1); border-radius:999px; height:4px; width:70px; margin-left:auto; margin-right:auto;">
                                            <div style="background:#002f45; height:4px; border-radius:999px; width:{{ min(100, round(($tugas->pengumpulan_count / $totalPeserta) * 100)) }}%;">
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Aksi --}}
                                <div style="display:flex; flex-direction:column; gap:0.5rem; min-width:140px;">

                                    {{-- Form Toggle Status --}}
                                    <form method="POST" action="{{ route('panitia.tugas.toggle', $tugas->id) }}" style="margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            style="width:100%; padding:0.5rem; background:{{ $tugas->aktif ? 'rgba(254,249,195,0.8)' : 'rgba(220,252,231,0.8)' }}; color:{{ $tugas->aktif ? '#854d0e' : '#166534' }}; border:1px solid rgba(0,0,0,0.05); border-radius:0.6rem; cursor:pointer; font-size:0.75rem; font-weight:700; transition:all 0.2s;">
                                            {{ $tugas->aktif ? '⏸ Nonaktifkan' : '▶ Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Button Edit Data Tugas --}}
                                    @if (auth()->user()->isAcara())
                                        <button type="button" class="btn-trigger-edit"
                                            data-id="{{ $tugas->id }}"
                                            data-nama="{{ $tugas->nama_tugas }}"
                                            data-deskripsi="{{ $tugas->deskripsi }}"
                                            data-deadline="{{ $tugas->deadline ? $tugas->deadline->format('Y-m-d\TH:i') : '' }}"
                                            data-tipe="{{ $tugas->tipe_file }}"
                                            data-ukuran="{{ $tugas->maks_ukuran }}"
                                            data-urutan="{{ $tugas->urutan }}"
                                            data-url="{{ route('panitia.tugas.update', $tugas->id) }}"
                                            style="width:100%; padding:0.5rem; background:rgba(217,119,6,0.15); color:#b45309; border:1px solid rgba(217,119,6,0.2); border-radius:0.6rem; cursor:pointer; font-size:0.75rem; font-weight:700; transition:all 0.2s;"
                                            onmouseover="this.style.background='rgba(217,119,6,0.25)'"
                                            onmouseout="this.style.background='rgba(217,119,6,0.15)'">
                                            ✏️ Edit Tugas
                                        </button>
                                    @endif

                                    {{-- Form Hapus --}}
                                    @if (auth()->user()->isAcara())
                                        <form method="POST" action="{{ route('panitia.tugas.destroy', $tugas->id) }}" style="margin:0;" onsubmit="return confirm('Hapus tugas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="width:100%; padding:0.5rem; background:rgba(254,226,226,0.6); color:#991b1b; border:1px solid rgba(0,0,0,0.05); border-radius:0.6rem; cursor:pointer; font-size:0.75rem; font-weight:700;">
                                                🗑 Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        // Fungsi Toggle untuk Form Buat Tugas Baru
        function toggleForm() {
            const f = document.getElementById('form-tugas');
            document.getElementById('form-edit-tugas').style.display = 'none';
            
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
            if (f.style.display === 'block') f.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Fungsi Membuka Form Edit dan Inject Data secara Dinamis
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.btn-trigger-edit');
            const editFormContainer = document.getElementById('form-edit-tugas');
            const formAction = document.getElementById('form-action-edit');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('form-tugas').style.display = 'none';

                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const deskripsi = this.getAttribute('data-deskripsi');
                    const deadline = this.getAttribute('data-deadline');
                    const tipe = this.getAttribute('data-tipe');
                    const ukuran = this.getAttribute('data-ukuran');
                    const urutan = this.getAttribute('data-urutan');
                    const url = this.getAttribute('data-url');

                    formAction.action = url;
                    document.getElementById('edit-title-nama').innerText = nama;
                    document.getElementById('edit_nama_tugas').value = nama;
                    document.getElementById('edit_deskripsi').value = deskripsi ? deskripsi : '';
                    document.getElementById('edit_deadline').value = deadline;
                    document.getElementById('edit_tipe_file').value = tipe;
                    document.getElementById('edit_maks_ukuran').value = ukuran;
                    document.getElementById('edit_urutan').value = urutan;

                    editFormContainer.style.display = 'block';
                    editFormContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        });

        // Fungsi Menutup Form Edit Tugas
        function closeEditForm() {
            document.getElementById('form-edit-tugas').style.display = 'none';
        }

        // Auto Close Flash Message Logic
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[id^="flash-message"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 4000);
            });
        });
    </script>
@endsection