@extends('layouts.app')

@section('content')
    {{-- Background Wrapper dengan Gradient Lembut --}}
    <div style="min-height:calc(100vh - 64px); padding:3rem 1.5rem; background: linear-gradient(135deg, #f4f3ee 0%, #e0decd 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1400px; margin:0 auto;">

            {{-- Header Section (Glass Card) --}}
            <div style="background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 1.5rem; padding: 2rem; display:flex; justify-content:space-between; align-items:center; margin-bottom:3rem; box-shadow: 0 8px 32px 0 rgba(0, 47, 69, 0.1);">
                <div>
                    <a href="{{ route('panitia.mentoring.index') }}"
                        style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                        <span>←</span> Kembali
                    </a>
                    <h1 style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0;">
                        📋 Rekapitulasi Mentoring
                    </h1>
                    <p style="color:#002f45; opacity:0.7; margin-top:0.5rem; font-size:1.1rem; font-weight:500;">
                        Klasifikasi berdasarkan Kelompok & Jenis Kegiatan
                    </p>
                </div>
                <a href="{{ route('panitia.mentoring.export_seluruh') }}"
                    style="padding:1rem 2rem; background: rgba(112, 173, 71, 0.8); backdrop-filter: blur(5px); color:white; border-radius:1rem; text-decoration:none; font-weight:700; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(112, 173, 71, 0.3); transition: transform 0.2s;">
                    📥 Download Excel
                </a>
            </div>

            @php
                $currentKelompok = null;
                $currentMentoringId = null;
                $hasData = count($rekapDetail) > 0;
            @endphp

            @if(!$hasData)
                <div style="background: rgba(255, 255, 255, 0.4); border-radius: 1.5rem; padding: 3rem; text-align: center; color: #002f45; font-weight: 600;">
                    📭 Belum ada riwayat rekapitulasi data mentoring yang tersimpan.
                </div>
            @endif

            @foreach ($rekapDetail as $index => $rd)
                @php
                    $loopKelompok = $rd->peserta->kelompok ?? 'Tanpa Kelompok';
                    $loopMentoringId = $rd->mentoring_id;
                    $namaKegiatan = $rd->mentoring->nama_kegiatan ?? 'Kegiatan Tanpa Nama';
                    $tanggalKegiatan = $rd->mentoring->tanggal ?? null;
                    
                    // Hitung jumlah kehadiran statis per sesi mentoring saat ini untuk footer komponen card
                    $allDetailsInSession = $rekapDetail->where('mentoring_id', $loopMentoringId);
                    $hadirCount = $allDetailsInSession->where('kehadiran', 'Hadir')->count();
                    $tidakHadirCount = $allDetailsInSession->whereIn('kehadiran', ['Izin', 'Alpha'])->count();
                @endphp

                {{-- JIKA KELOMPOK BERUBAH: Buka container kelompok baru --}}
                @if($currentKelompok !== $loopKelompok)
                    @if($currentKelompok !== null)
                                        </tbody>
                                    </table>
                                </div>
                                <div style="padding:1rem 2rem; background: rgba(255, 255, 255, 0.2); border-top:1px solid rgba(255,255,255,0.1); display:flex; gap:2rem; font-size:0.85rem;">
                                    <span style="color:#166534; font-weight:600;">
                                        <span style="opacity:0.7;">✅ Hadir:</span> <strong>{{ $hadirCount }}</strong>
                                    </span>
                                    <span style="color:#991b1b; font-weight:600;">
                                        <span style="opacity:0.7;">❌ Tidak Hadir:</span> <strong>{{ $tidakHadirCount }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div> {{-- Penutup grid kegiatan --}}
                    </div> {{-- Penutup box kelompok --}}
                    @endif

                    @php $currentKelompok = $loopKelompok; $currentMentoringId = null; @endphp

                    <div style="margin-bottom:5rem;">
                        {{-- JUDUL KELOMPOK --}}
                        <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom:2rem;">
                            <div style="width:60px; height:60px; background: rgba(0, 47, 69, 0.8); backdrop-filter: blur(5px); color:#d2c296; display:flex; align-items:center; justify-content:center; border-radius:18px; font-weight:800; font-size:1.6rem; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                                {{ $loopKelompok }}
                            </div>
                            <h2 style="font-family:'Playfair Display',serif; color:#002f45; margin:0; font-size:2rem; letter-spacing:1px;">
                                KELOMPOK {{ $loopKelompok }}
                            </h2>
                            <div style="flex-grow:1; height:1px; background: linear-gradient(to right, rgba(0,47,69,0.3), transparent);"></div>
                        </div>

                        {{-- GRID KEGIATAN --}}
                        <div style="display:grid; grid-template-columns: 1fr; gap:2.5rem; padding-left:1.5rem; border-left:2px solid rgba(0,47,69,0.1);">
                @endif

                {{-- JIKA KEGIATAN/SESI BERUBAH: Ganti card tabel pembungkus baru --}}
                @if($currentMentoringId !== $loopMentoringId)
                    @if($currentMentoringId !== null)
                                        </tbody>
                                    </table>
                                </div>
                                <div style="padding:1rem 2rem; background: rgba(255, 255, 255, 0.2); border-top:1px solid rgba(255,255,255,0.1); display:flex; gap:2rem; font-size:0.85rem;">
                                    <span style="color:#166534; font-weight:600;">
                                        <span style="opacity:0.7;">✅ Hadir:</span> <strong>{{ $hadirCount }}</strong>
                                    </span>
                                    <span style="color:#991b1b; font-weight:600;">
                                        <span style="opacity:0.7;">❌ Tidak Hadir:</span> <strong>{{ $tidakHadirCount }}</strong>
                                    </span>
                                </div>
                            </div>
                    @endif

                    @php $currentMentoringId = $loopMentoringId; @endphp

                    <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 1.5rem; overflow:hidden; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);">
                        
                        {{-- Sub-Header (Glass Top) --}}
                        <div style="padding:1.2rem 2rem; background: rgba(255, 255, 255, 0.3); border-bottom:1px solid rgba(255,255,255,0.2); display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:800; color:#002f45; font-size:1.1rem; letter-spacing:0.5px;">
                                📌 {{ strtoupper($namaKegiatan) }}
                            </span>
                            <span style="font-size:0.85rem; font-weight:700; color:#002f45; background: rgba(255, 255, 255, 0.5); padding:0.4rem 1rem; border-radius:12px; border: 1px solid rgba(255,255,255,0.3);">
                                📅 {{ $tanggalKegiatan ? date('d M Y', strtotime($tanggalKegiatan)) : '-' }}
                            </span>
                        </div>

                        {{-- Menampilkan Catatan/Poin Pembahasan di Rekapitulasi (Jika Ada) --}}
                        @if(!empty($rd->mentoring->catatan_pertemuan))
                            <div class="meeting-notes-rekap">
                                <div class="notes-title">📝 Rangkuman & Poin Pembahasan Mentoring:</div>
                                <div class="notes-content">{!! nl2br(e($rd->mentoring->catatan_pertemuan)) !!}</div>
                            </div>
                        @endif

                        {{-- Table Area --}}
                        <div style="overflow-x: auto; padding: 0.5rem 1.5rem;">
                            <table style="width:100%; border-collapse:separate; border-spacing: 0 0.5rem;">
                                <thead>
                                    <tr>
                                        <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">Nama Peserta</th>
                                        <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">NIM</th>
                                        <th style="padding:1rem 1.5rem; text-align:center; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">Status</th>
                                        <th style="padding:1rem 1.5rem; text-align:left; color:#002f45; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">Aksi / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                @endif

                {{-- ISI DATA BARIS PESERTA --}}
                <tr style="background: rgba(255, 255, 255, 0.15); transition: background 0.3s;">
                    <td style="padding:1rem 1.5rem; font-weight:700; color:#002f45; border-radius: 12px 0 0 12px;">{{ $rd->peserta->name ?? 'User Terhapus' }}</td>
                    <td style="padding:1rem 1.5rem; color:#002f45; font-size:0.9rem; opacity:0.8;">{{ $rd->peserta->nim ?? '-' }}</td>
                    <td style="padding:1rem 1.5rem; text-align:center;">
                        <span style="display:inline-block; padding:0.4rem 0.8rem; border-radius:10px; font-size:0.7rem; font-weight:800; letter-spacing:0.5px;
                            {{ $rd->kehadiran === 'Hadir' ? 'background:rgba(34, 197, 94, 0.2); color:#166534; border: 1px solid rgba(34, 197, 94, 0.3);' : 
                               ($rd->kehadiran === 'Izin' ? 'background:rgba(249, 115, 22, 0.2); color:#9a3412; border: 1px solid rgba(249, 115, 22, 0.3);' : 
                               'background:rgba(239, 68, 68, 0.2); color:#991b1b; border: 1px solid rgba(239, 68, 68, 0.3);') }}">
                            {{ strtoupper($rd->kehadiran ?? 'ALPHA') }}
                        </span>
                    </td>
                    <td style="padding:1rem 1.5rem; color:#002f45; font-size:0.85rem; border-radius: 0 12px 12px 0;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            @if(!empty($rd->file_path))
                                @php
                                    $decodedFiles = json_decode($rd->file_path, true);
                                    $isMultiple = is_array($decodedFiles);
                                @endphp

                                @if($isMultiple && count($decodedFiles) > 1)
                                    <button type="button" 
                                            onclick="bukaModalFile('{{ e(json_encode($decodedFiles)) }}', '{{ $rd->peserta->name ?? 'Peserta' }}')"
                                            style="padding: 0.4rem 0.8rem; background: #002f45; color: #fff; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                                        👁️ Lihat File ({{ count($decodedFiles) }})
                                    </button>
                                @else
                                    @php
                                        $singlePath = $isMultiple ? ($decodedFiles[0]['path'] ?? '') : $rd->file_path;
                                    @endphp
                                    <a href="{{ asset('storage/' . $singlePath) }}" target="_blank"
                                       style="padding: 0.4rem 0.8rem; background: #6b705c; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                        👁️ Lihat File
                                    </a>
                                @endif
                            @endif
                            
                            <span style="font-style:italic; opacity:0.6;">
                                {{ $rd->keterangan ?? '—' }}
                            </span>
                        </div>
                    </td>
                </tr>

                {{-- Kunci Penutup Iterasi Terakhir --}}
                @if($loop->last)
                                    </tbody>
                                </table>
                            </div>
                            <div style="padding:1rem 2rem; background: rgba(255, 255, 255, 0.2); border-top:1px solid rgba(255,255,255,0.1); display:flex; gap:2rem; font-size:0.85rem;">
                                <span style="color:#166534; font-weight:600;">
                                    <span style="opacity:0.7;">✅ Hadir:</span> <strong>{{ $hadirCount }}</strong>
                                </span>
                                <span style="color:#991b1b; font-weight:600;">
                                    <span style="opacity:0.7;">❌ Tidak Hadir:</span> <strong>{{ $tidakHadirCount }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            @endforeach

        </div>
    </div>

    {{-- MODAL INTERAKTIF UNTUK MULTIPLE FILES --}}
    <div id="modalDaftarFile" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3 style="margin:0; font-family:'Playfair Display',serif; color:#002f45;" id="modalNamaPeserta">Daftar File</h3>
                <span class="custom-close" onclick="tutupModalFile()">&times;</span>
            </div>
            <div class="custom-modal-body">
                <p style="font-size:0.9rem; color:#555; margin-bottom:1.5rem;">Silakan klik nama file di bawah ini untuk melihat isi dokumen di tab baru:</p>
                <div id="containerListFile" style="display:flex; flex-direction:column; gap:0.75rem;">
                    {{-- Diisi dinamis via JavaScript --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Styling Khusus --}}
    <style>
        .meeting-notes-rekap { 
            background: rgba(0, 47, 69, 0.04); 
            border-left: 4px solid #6b705c; 
            border-radius: 1rem; 
            padding: 1.25rem 1.5rem; 
            margin: 1.5rem 1.5rem 0.5rem 1.5rem; 
        }
        .notes-title { 
            font-size: 0.75rem; 
            font-weight: 800; 
            color: #002f45; 
            text-transform: uppercase; 
            margin-bottom: 0.6rem; 
            letter-spacing: 0.05em; 
        }
        .notes-content { 
            font-size: 0.9rem; 
            color: #002f45; 
            line-height: 1.4; 
            opacity: 0.9; 
            white-space: pre-wrap;
        }

        .custom-modal {
            display: none; 
            position: fixed; 
            z-index: 9999; 
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto; 
            background-color: rgba(0,47,69,0.4);
            backdrop-filter: blur(4px);
        }
        .custom-modal-content {
            background-color: #fff;
            margin: 15% auto; 
            padding: 2rem;
            border-radius: 1.25rem;
            width: 80%;
            max-width: 550px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease-out;
        }
        .custom-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        .custom-close {
            color: #aaa;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
        }
        .custom-close:hover { color: #002f45; }
        .custom-modal-body { padding-top: 1.5rem; }
        
        .file-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #002f45;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .file-list-item:hover {
            background: #e0decd;
            border-color: #6b705c;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>

    {{-- Script Logika Modal --}}
    <script>
        function bukaModalFile(filesJson, namaPeserta) {
            const files = JSON.parse(filesJson);
            const container = document.getElementById('containerListFile');
            const modalTitle = document.getElementById('modalNamaPeserta');
            
            modalTitle.innerText = "File Dokumen: " + namaPeserta;
            container.innerHTML = ''; 

            files.forEach((file, index) => {
                const urlFile = "{{ asset('storage') }}/" + file.path;
                const namaAsli = file.nama_asli || ("Dokumen " + (index + 1));
                
                const a = document.createElement('a');
                a.href = urlFile;
                a.target = "_blank";
                a.className = "file-list-item";
                a.innerHTML = `<span>📄 ${namaAsli}</span> <span style="font-size:0.8rem; color:#666;">Buka Tab Baru →</span>`;
                
                container.appendChild(a);
            });

            document.getElementById('modalDaftarFile').style.display = 'block';
        }

        function tutupModalFile() {
            document.getElementById('modalDaftarFile').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modalDaftarFile');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
@endsection