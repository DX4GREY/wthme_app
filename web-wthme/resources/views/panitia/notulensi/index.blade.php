@extends('layouts.app')

@section('content')
    <div style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background-color: #e0decd;">
        <div style="max-width:1000px; margin:0 auto;">

            {{-- Header Section --}}
            <div
                style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1.5rem;">
                <div>
                    <a href="{{ route('panitia.index') }}"
                        style="color:#002f45; opacity:0.5; text-decoration:none; font-size:0.875rem; display:block; margin-bottom:1.5rem;">
                        ← Kembali
                    </a>
                    <h1
                        style="font-family:'Playfair Display',serif; color:#002f45; font-size:2rem; font-weight:700; margin:0;">
                        Notulensi Rapat
                    </h1>
                </div>

                {{-- Tombol Tambah Notulensi (Hanya Sekre/Admin/BPH yang biasanya buat) --}}
                <button onclick="toggleModal()"
                    style="padding:0.6rem 1.25rem; background:#002f45; color:#d2c296; border-radius:0.6rem; border:none; cursor:pointer; font-size:0.875rem; font-weight:700; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    + Buat Notulensi Baru
                </button>
            </div>

            {{-- List Notulensi --}}
            @if ($notulensi->isEmpty())
                <div
                    style="background:white; border-radius:1rem; padding:4rem 2rem; text-align:center; border:2px solid #bdd1d3;">
                    <div style="font-size:4rem; margin-bottom:1rem;">📝</div>
                    <h3 style="color:#002f45; margin-bottom:0.5rem;">Belum Ada Notulensi</h3>
                    <p style="color:#002f45; opacity:0.5;">Klik tombol di atas untuk mencatat rapat pertama.</p>
                </div>
            @else
                @foreach ($notulensi as $n)
                    <div
                        style="background:white; border-radius:1rem; border:1px solid #bdd1d3; margin-bottom:2rem; overflow:hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        {{-- Card Header --}}
                        <div
                            style="background:#002f45; padding:1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
                            <div>
                                <span
                                    style="color:#d2c296; font-size:0.75rem; font-weight:700; text-transform:uppercase; display:block; margin-bottom:0.25rem;">
                                    {{ \Carbon\Carbon::parse($n->tanggal)->translatedFormat('d F Y') }}
                                </span>
                                <h2 style="color:white; margin:0; font-size:1.25rem;">{{ $n->topik }}</h2>
                            </div>
                            <div style="display:flex; gap:0.5rem;">
                                {{-- <a href="{{ route('panitia.notulensi.export', $n->id) }}" style="background:#2f855a; color:white; padding:0.5rem 1rem; border-radius:0.5rem; text-decoration:none; font-size:0.75rem; font-weight:700;">
                            ⬇ Export Excel
                        </a> --}}
                                <a href="{{ route('panitia.notulensi.download', $n->id) }}"
                                    style="background:#2b5797; color:white; padding:0.5rem 1rem; border-radius:0.5rem; text-decoration:none; font-size:0.8rem; font-weight:bold; display:inline-block;">
                                    Download DOCX 📄
                                </a>
                            </div>
                        </div>

                        {{-- Info Rapat --}}
                        <div
                            style="padding:1rem 1.25rem; background:#f9f8f6; border-bottom:1px solid #e0decd; display:flex; gap:2rem; font-size:0.875rem; color:#002f45;">
                            <span><strong>📍 Tempat:</strong> {{ $n->tempat ?? '-' }}</span>
                            <span><strong>👤 Pemimpin:</strong> {{ $n->pemimpin_rapat ?? '-' }}</span>
                        </div>

                        {{-- Poin-Poin Per Divisi --}}
                        <div style="padding:1.25rem;">
                            @foreach ($n->poin as $p)
                                <div style="margin-bottom:1.5rem; last-child { margin-bottom:0; }">
                                    <h4
                                        style="color:#002f45; border-left:4px solid #d2c296; padding-left:0.75rem; margin-bottom:0.75rem; font-size:1rem;">
                                        Divisi {{ $p->divisi }}
                                    </h4>
                                    <div style="color:#4a5568; font-size:0.9rem; line-height:1.6; padding-left:1.25rem;">
                                        {!! nl2br(e($p->isi_poin)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- MODAL FORM (Hidden by default) --}}
    <div id="modalNotulensi"
        style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background:rgba(0,47,69,0.8); backdrop-filter:blur(4px); overflow-y:auto;">
        <div style="background:white; margin:2rem auto; width:90%; max-width:800px; border-radius:1rem; overflow:hidden;">
            <div
                style="padding:1.5rem; background:#002f45; color:white; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Form Notulensi Rapat</h3>
                <button onclick="toggleModal()"
                    style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>

            <form action="{{ route('panitia.notulensi.store') }}" method="POST" id="formNotulensi" style="padding:1.5rem;">
                @csrf
                {{-- Data Utama Rapat --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                    <div>
                        <label style="display:block; font-weight:700; font-size:0.875rem;">Topik Rapat</label>
                        <input type="text" name="topik" required
                            style="width:100%; padding:0.6rem; border:1px solid #bdd1d3; border-radius:0.5rem;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:700; font-size:0.875rem;">Tanggal</label>
                        <input type="date" name="tanggal" required
                            style="width:100%; padding:0.6rem; border:1px solid #bdd1d3; border-radius:0.5rem;"
                            value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <hr style="border:0; border-top:1px solid #eee; margin-bottom:1.5rem;">

                {{-- Input Poin Per Divisi --}}
                <div
                    style="background:#f8fafc; padding:1rem; border-radius:0.75rem; border:1px dashed #bdd1d3; margin-bottom:1.5rem;">
                    <div style="margin-bottom:1rem;">
                        <label style="font-weight:bold; color:#002f45;">Pilih Divisi:</label>
                        <select id="selectDivisi"
                            style="width:100%; padding:0.6rem; border:1px solid #bdd1d3; border-radius:0.5rem;">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div }}">{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="font-weight:bold; color:#002f45;">Isi Poin Pembahasan:</label>
                        <textarea id="tempPoin" class="auto-point"
                            style="width:100%; height:100px; border:1px solid #bdd1d3; border-radius:0.5rem; padding:0.75rem;"
                            placeholder="Ketik poin di sini... (Enter untuk poin baru)">• </textarea>
                    </div>

                    <button type="button" onclick="addPoinToList()"
                        style="width:100%; padding:0.6rem; background:#d2c296; color:#002f45; border:none; border-radius:0.5rem; font-weight:700; cursor:pointer;">
                        + Tambahkan ke Daftar
                    </button>
                </div>

                {{-- Preview Daftar yang Akan Disimpan --}}
                <div id="daftarPoinPreview" style="margin-top:1.5rem;">
                    <p style="font-weight:700; color:#002f45; margin-bottom:0.5rem;">Antrean Poin Notulensi:</p>
                    <div id="containerPreview">
                        <p style="color:#64748b; font-style:italic; font-size:0.8rem;">Belum ada poin yang ditambahkan.</p>
                    </div>
                </div>

                <div
                    style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem; border-top:1px solid #eee; padding-top:1.5rem;">
                    <button type="button" onclick="toggleModal()"
                        style="padding:0.75rem 1.5rem; background:#f1f5f9; color:#64748b; border:none; border-radius:0.5rem; font-weight:700; cursor:pointer;">Batal</button>
                    <button type="submit"
                        style="padding:0.75rem 1.5rem; background:#002f45; color:#d2c296; border:none; border-radius:0.5rem; font-weight:700; cursor:pointer;">Simpan
                        Permanen</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi buka tutup modal
        function addPoinToList() {
            const divisi = document.getElementById('selectDivisi').value;
            const isi = document.getElementById('tempPoin').value;
            const container = document.getElementById('containerPreview');

            if (!divisi || isi.trim() === '•' || isi.trim() === '') {
                alert('Pilih divisi dan isi poinnya dulu ya!');
                return;
            }

            // Hapus tulisan "Belum ada poin" jika ini item pertama
            if (container.querySelector('p')) {
                container.innerHTML = '';
            }

            // Buat element preview
            const itemDiv = document.createElement('div');
            itemDiv.style =
                "background:white; border:1px solid #bdd1d3; padding:0.75rem; border-radius:0.5rem; margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:flex-start;";

            // HTML untuk ditampilkan dan Input Hidden untuk dikirim ke Laravel
            itemDiv.innerHTML = `
            <div>
                <strong style="color:#002f45; font-size:0.8rem;">[${divisi}]</strong>
                <div style="font-size:0.85rem; white-space:pre-wrap;">${isi}</div>
                <input type="hidden" name="divisi_input[]" value="${divisi}">
                <input type="hidden" name="isi_poin_input[]" value="${isi}">
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="color:red; border:none; background:none; cursor:pointer;">&times;</button>
        `;

            container.appendChild(itemDiv);

            // Reset input setelah tambah
            document.getElementById('selectDivisi').value = '';
            document.getElementById('tempPoin').value = '• ';
        }

        function toggleModal() {
            const modal = document.getElementById('modalNotulensi');
            modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'block' : 'none';
        }

        // Fungsi Auto Bullet (Setiap Enter ganti poin)
        document.querySelectorAll('.auto-point').forEach(textarea => {
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const value = this.value;

                    // Masukkan line break + bullet baru
                    this.value = value.substring(0, start) + "\n• " + value.substring(end);

                    // Set posisi kursor ke setelah bullet baru
                    this.selectionStart = this.selectionEnd = start + 3;
                }
            });

            // Pastikan saat fokus, jika kosong langsung ada bullet
            textarea.addEventListener('focus', function() {
                if (this.value.trim() === '') {
                    this.value = '• ';
                }
            });
        });
    </script>
@endsection
