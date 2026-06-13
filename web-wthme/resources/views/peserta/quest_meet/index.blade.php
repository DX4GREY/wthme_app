@extends('layouts.app')

@section('content')
    {{-- Background Wrapper - Menggunakan gradien premium khas portal Anda --}}
    <div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%); font-family: system-ui, -apple-system, sans-serif;">
        <div style="max-width:700px; margin:0 auto;">

            {{-- Navigasi Kembali --}}
            <a href="{{ route('peserta.index') }}"
                style="color:#002f45; text-decoration:none; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; font-weight:600; opacity:0.7; transition:0.2s;"
                onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Portal
            </a>

            {{-- Header Section --}}
            <div style="margin-bottom:2rem;">
                <h1 style="font-family:'Playfair Display', serif; color:#002f45; font-size:2.25rem; font-weight:800; margin-bottom:0.5rem;">
                    ✨ Upload Bukti Quest
                </h1>
                <p style="color:#002f45; opacity:0.6; font-size:1rem; font-weight:500;">
                    Meet The KBM Elektro · Dokumentasikan pertemuan serumu di sini!
                </p>
            </div>

            {{-- Card Outer Container (Glassmorphism Style) --}}
            <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(15px); border-radius:2rem; border:1px solid rgba(255, 255, 255, 0.5); overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                
                {{-- Form Inner Box --}}
                <div style="padding: 2rem;">
                    <form action="{{ route('peserta.meet.store') }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1.75rem;">
                        @csrf
                        
                        {{-- 1. Pilih Kategori Target --}}
                        <div style="display:flex; flex-direction:column; gap:0.5rem;">
                            <label style="color:#002f45; font-weight:700; font-size:0.95rem;">1. Pilih Kategori Target</label>
                            <select name="kategori_angkatan" id="kategori_angkatan" required
                                style="width:100%; padding:0.9rem 1.25rem; background:rgba(255,255,255,0.6); border:1px solid rgba(0,47,69,0.1); border-radius:1.25rem; font-size:0.9rem; color:#002f45; outline:none; font-weight:600; box-sizing:border-box; transition:0.2s;">
                                <option value="" style="background:#bdd1d3; color:#002f45;">-- Pilih Angkatan --</option>
                                <option value="2024" style="background:#bdd1d3; color:#002f45;">Angkatan 2024</option>
                                <option value="2023" style="background:#bdd1d3; color:#002f45;">Angkatan 2023</option>
                                <option value="2022" style="background:#bdd1d3; color:#002f45;">Angkatan 2022</option>
                                <option value="2021" style="background:#bdd1d3; color:#002f45;">Angkatan 2021</option>
                                <option value="alumni" style="background:#bdd1d3; color:#002f45;">Alumni / Senior Tua</option>
                            </select>
                        </div>

                        {{-- 2. Tipe Pertemuan --}}
                        <div style="display:flex; flex-direction:column; gap:0.5rem;">
                            <label style="color:#002f45; font-weight:700; font-size:0.95rem;">2. Tipe Pertemuan</label>
                            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                                <label style="flex:1; min-width:240px; display:flex; align-items:center; gap:0.75rem; padding:1rem; background:rgba(255,255,255,0.4); border:1px solid rgba(0,47,69,0.1); border-radius:1.25rem; cursor:pointer; transition:0.2s;">
                                    <input type="radio" name="tipe_meet" id="type_individual" value="individu" checked style="accent-color:#002f45; width:1.1rem; height:1.1rem;">
                                    <span style="color:#002f45; font-size:0.85rem; font-weight:600;">Meet Individu (Selfie Personal)</span>
                                </label>
                                <label style="flex:1; min-width:240px; display:flex; align-items:center; gap:0.75rem; padding:1rem; background:rgba(255,255,255,0.4); border:1px solid rgba(0,47,69,0.1); border-radius:1.25rem; cursor:pointer; transition:0.2s;">
                                    <input type="radio" name="tipe_meet" id="type_group" value="group" style="accent-color:#002f45; width:1.1rem; height:1.1rem;">
                                    <span style="color:#002f45; font-size:0.85rem; font-weight:600;">Meet Group (Gathering Angkatan)</span>
                                </label>
                            </div>
                        </div>

                        {{-- 3. Wrapper Abang dari Database (Dinamis) --}}
                        <div id="wrapper_abang_db" style="display:none; flex-direction:column; gap:0.5rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                                <label style="color:#002f45; font-weight:700; font-size:0.95rem;">3. Pilih Target yang Ditemui</label>
                                <span style="font-size:0.75rem; font-weight:700; background:#fdf6e2; color:#002f45; padding:0.25rem 0.75rem; border-radius:99px; border:1px solid rgba(0,47,69,0.15);">
                                    ★ +50 Poin / Orang
                                </span>
                            </div>
                            <input type="text" id="search_abang" placeholder="🔍 Cari nama..."
                                style="width:100%; padding:0.8rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:1rem; font-size:0.9rem; color:#002f45; outline:none; font-weight:600; box-sizing:border-box;">
                            
                            <div id="list_checkbox_abang" style="display:flex; flex-direction:column; gap:0.4rem; max-height:220px; overflow-y:auto; background:rgba(255,255,255,0.3); padding:0.75rem; border-radius:1.25rem; border:1px solid rgba(0,47,69,0.08);">
                                {{-- Diisi via AJAX --}}
                            </div>
                        </div>

                        {{-- 3. Wrapper Alumni Manual (Dinamis) --}}
                        <div id="wrapper_alumni_manual" style="display:none; flex-direction:column; gap:0.5rem;">
                            <label style="color:#002f45; font-weight:700; font-size:0.95rem;">3. List Nama Alumni Senior</label>
                            <span style="font-size:0.75rem; color:#002f45; opacity:0.6; font-weight:500; margin-bottom:0.25rem;">Ketik manual, tekan tombol (+) untuk menambah nama baru</span>
                            
                            <div id="container_input_alumni" style="display:flex; flex-direction:column; gap:0.5rem;">
                                <div style="display:flex; gap:0.5rem;">
                                    <input type="text" name="alumni_names[]" placeholder="Masukkan nama lengkap senior"
                                        style="flex:1; padding:0.8rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:1rem; font-size:0.9rem; color:#002f45; outline:none; font-weight:600; box-sizing:border-box;">
                                    <button type="button" id="add_alumni_field" style="width:45px; height:43px; background:#002f45; color:#d2c296; border:none; border-radius:1rem; font-size:1.25rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">+</button>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Upload Foto Bukti --}}
                        <div style="display:flex; flex-direction:column; gap:0.5rem;">
                            <label style="color:#002f45; font-weight:700; font-size:0.95rem;">4. Upload Foto Bukti</label>
                            
                            <label for="foto_bukti" id="dropzone_label"
                                style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; background: rgba(255, 255, 255, 0.4); border:2px dashed rgba(0, 47, 69, 0.2); border-radius:1.5rem; cursor:pointer; text-align:center; transition:0.2s;">
                                <div style="font-size:2.5rem; margin-bottom:0.5rem;">📷</div>
                                <span id="file_status_text" style="color:#002f45; font-weight:700; font-size:0.9rem;">
                                    Klik atau seret foto bukti di sini
                                </span>
                                <span style="font-size:0.75rem; color:#002f45; opacity:0.5; font-weight:600; margin-top:0.25rem;">
                                    Format Gambar (Kompresi Otomatis 60%)
                                </span>
                                
                                <input type="file" id="foto_bukti" name="foto_bukti" accept="image/*" required style="display:none;"
                                    onchange="handleImageSelected(this)">
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <div style="margin-top:0.5rem;">
                            <button type="submit" style="width:100%; padding:1rem; background:#002f45; color:#d2c296; font-weight:800; border:none; border-radius:1.25rem; cursor:pointer; font-size:1rem; box-shadow: 0 4px 15px rgba(0, 47, 69, 0.15); transition: 0.2s;"
                                onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                Kirim Bukti Quest
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <p style="color:#002f45; opacity:0.4; font-size:0.8rem; margin-top:2rem; text-align:center; font-weight:600;">
                💡 Pastikan wajah target terlihat jelas pada foto untuk mempermudah validasi panitia.
            </p>
        </div>
    </div>

    <script>
        // Logika Interaktif Menampilkan Status Unggahan Gambar
        function handleImageSelected(input) {
            const labelText = document.getElementById('file_status_text');
            const box = document.getElementById('dropzone_label');
            
            if (input.files && input.files[0]) {
                box.style.borderColor = '#002f45';
                box.style.background = 'rgba(255, 255, 255, 0.7)';
                labelText.innerHTML = `✅ Terpilih: <span style="color:#002f45; text-decoration: underline;">${input.files[0].name}</span>`;
            } else {
                box.style.borderColor = 'rgba(0, 47, 69, 0.2)';
                box.style.background = 'rgba(255, 255, 255, 0.4)';
                labelText.textContent = 'Klik atau seret foto bukti di sini';
            }
        }

        // Event Handler saat Kategori Angkatan berubah
        document.getElementById('kategori_angkatan').addEventListener('change', function() {
            let angkatan = this.value;
            let wrapperDb = document.getElementById('wrapper_abang_db');
            let wrapperAlumni = document.getElementById('wrapper_alumni_manual');
            let listContainer = document.getElementById('list_checkbox_abang');

            if(!angkatan) {
                wrapperDb.style.display = 'none';
                wrapperAlumni.style.display = 'none';
                return;
            }

            if(angkatan === 'alumni') {
                wrapperDb.style.display = 'none';
                wrapperAlumni.style.display = 'flex';
            } else {
                wrapperAlumni.style.display = 'none';
                wrapperDb.style.display = 'flex';
                listContainer.innerHTML = '<p style="font-size:0.8rem; color:#002f45; opacity:0.5; padding:0.5rem; font-weight:600;">Memuat data nama...</p>';

                // Fetch data via AJAX JSON
                fetch(`/peserta/quest-meet/get-abang/${angkatan}`)
                    .then(res => res.json())
                    .then(data => {
                        listContainer.innerHTML = '';
                        if(data.length === 0) {
                            listContainer.innerHTML = '<span style="font-size:0.8rem; color:#002f45; font-weight:700; opacity:0.6; padding:0.5rem;">⚠ Belum ada data abang di angkatan ini.</span>';
                            return;
                        }
                        data.forEach(abang => {
                            listContainer.innerHTML += `
                                <label style="display:flex; align-items:center; gap:0.75rem; padding:0.6rem 0.8rem; background:rgba(255,255,255,0.5); border-radius:0.75rem; cursor:pointer; border: 1px solid rgba(0,47,69,0.03);" class="item-abang">
                                    <input type="checkbox" name="abang_ids[]" value="${abang.id}" id="abang_${abang.id}" style="accent-color:#002f45; width:1rem; height:1rem;">
                                    <span style="font-size:0.85rem; color:#002f45; font-weight:600;" class="label-nama-abang">${abang.name}</span>
                                </label>
                            `;
                        });
                    });
            }
        });

        // Fitur Realtime Live Search Nama Abang-Abang
        document.getElementById('search_abang').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.item-abang');
            items.forEach(item => {
                let text = item.querySelector('.label-nama-abang').innerText.toLowerCase();
                if(text.includes(filter)) {
                    item.style.setProperty('display', 'flex', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });
        });

        // Fitur Tambah Baris Input Text Dinamis untuk Kategori Alumni
        document.getElementById('add_alumni_field').addEventListener('click', function() {
            let container = document.getElementById('container_input_alumni');
            let div = document.createElement('div');
            div.style.display = 'flex';
            div.style.gap = '0.5rem';
            div.innerHTML = `
                <input type="text" name="alumni_names[]" placeholder="Masukkan nama lengkap senior"
                    style="flex:1; padding:0.8rem 1.25rem; background:rgba(255,255,255,0.5); border:1px solid rgba(0,47,69,0.1); border-radius:1rem; font-size:0.9rem; color:#002f45; outline:none; font-weight:600; box-sizing:border-box;">
                <button type="button" class="remove-alumni-field" style="width:45px; height:43px; background:rgba(0,47,69,0.1); color:#002f45; border:1px solid rgba(0,47,69,0.2); border-radius:1rem; font-size:1.25rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">-</button>
            `;
            container.appendChild(div);
        });

        document.addEventListener('click', function(e) {
            if(e.target && e.target.classList.contains('remove-alumni-field')) {
                e.target.parentElement.remove();
            }
        });
    </script>
@endsection