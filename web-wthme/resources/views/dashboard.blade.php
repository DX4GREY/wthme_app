@extends('layouts.app')

@section('content')
    {{-- Background Ambient --}}
    <div style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem;">
        <div style="max-width: 1000px; margin: 0 auto;">

            {{-- Header Section --}}
            <div style="text-align: center; margin-bottom: 4rem;">
                <span
                    style="display: inline-block; padding: 0.5rem 1.25rem; background: rgba(0,47,69,0.05); border-radius: 2rem; color: #002f45; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1rem;">
                    {{ date('l, d F Y') }}
                </span>
                <h1
                    style="font-family: 'Playfair Display', serif; color: #002f45; font-size: 3rem; font-weight: 700; margin: 0; letter-spacing: -0.02em;">
                    Halo, {{ explode(' ', auth()->user()->name)[0] }}
                </h1>
                <p style="color: #002f45; opacity: 0.5; font-size: 1.1rem; margin-top: 0.75rem; font-weight: 400;">
                    Pilih portal yang sesuai dengan peranmu hari ini.
                </p>
            </div>

            {{-- Main Grid --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">

                {{-- Card Admin --}}
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.index') }}" class="glass-card"
                        style="text-decoration: none; background: rgba(210,194,150,0.4); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.4); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="font-size: 2rem; margin-bottom: 1.5rem;">⚙️</div>
                            <h3
                                style="color: #002f45; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">
                                Panel Admin</h3>
                            <p style="color: #002f45; opacity: 0.7; font-size: 0.9rem; line-height: 1.6; margin: 0;">Kelola
                                infrastruktur sistem, kontrol penuh data, dan manajemen otoritas.</p>
                        </div>
                        <div
                            style="margin-top: 2rem; color: #002f45; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                            AKSES SISTEM <span style="font-size: 1.2rem;">→</span>
                        </div>
                    </a>
                @endif

                {{-- Card Panitia --}}
                @php $isPanitia = auth()->user()->isPanitia(); @endphp
                <a href="{{ $isPanitia ? route('panitia.index') : '#' }}"
                    style="text-decoration: none; background: {{ $isPanitia ? 'rgba(0,47,69,0.95)' : 'rgba(0,47,69,0.1)' }}; backdrop-filter: blur(10px); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between; border: 1px solid rgba(255,255,255,0.1); cursor: {{ $isPanitia ? 'pointer' : 'not-allowed' }};"
                    @if ($isPanitia) onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 30px 60px rgba(0,47,69,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'" @endif>
                    <div>
                        <div
                            style="width: 48px; height: 48px; background: rgba(210,194,150,0.2); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <svg style="width: 24px; height: 24px; color: #d2c296;" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3
                            style="color: {{ $isPanitia ? '#d2c296' : 'rgba(0,47,69,0.4)' }}; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">
                            Portal Panitia</h3>
                        <p
                            style="color: #bdd1d3; opacity: {{ $isPanitia ? '0.8' : '0.3' }}; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                            Kelola operasional, absensi real-time, dan manajemen logistik event.</p>
                    </div>
                    <div
                        style="margin-top: 2rem; color: #d2c296; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; opacity: {{ $isPanitia ? '1' : '0.3' }};">
                        {{ $isPanitia ? 'MASUK PORTAL' : 'AKSES TERBATAS' }} <span style="font-size: 1.2rem;">→</span>
                    </div>
                </a>

                {{-- Card Peserta --}}
                @php $isPeserta = auth()->user()->isPeserta(); $isAdmin = auth()->user()->isAdmin(); @endphp
                @if ($isAdmin)
                    <a href="javascript:void(0)" onclick="bukaModalPeserta()"
                        style="text-decoration: none; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(189,209,211,0.5); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between; cursor: pointer;"
                        onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 30px 60px rgba(0,47,69,0.1)'; this.style.borderColor='#002f45'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='rgba(189,209,211,0.5)'">
                        <div>
                            <div
                                style="width: 48px; height: 48px; background: #e0decd; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <svg style="width: 24px; height: 24px; color: #002f45;" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3
                                style="color: #002f45; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">
                                Portal Peserta</h3>
                            <p
                                style="color: #002f45; opacity: 0.6; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                                Akses sebagai peserta tertentu. Pilih nama peserta untuk melihat portalnya.</p>
                        </div>
                        <div
                            style="margin-top: 2rem; color: #002f45; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                            PILIH PESERTA <span style="font-size: 1.2rem;">→</span>
                        </div>
                    </a>
                @else
                    <a href="{{ $isPeserta ? route('peserta.index') : '#' }}"
                        style="text-decoration: none; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(189,209,211,0.5); border-radius: 2rem; padding: 2.5rem; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; flex-direction: column; justify-content: space-between; cursor: {{ $isPeserta ? 'pointer' : 'not-allowed' }};"
                        @if ($isPeserta) onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 30px 60px rgba(0,47,69,0.1)'; this.style.borderColor='#002f45'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='rgba(189,209,211,0.5)'" @endif>
                        <div>
                            <div
                                style="width: 48px; height: 48px; background: #e0decd; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <svg style="width: 24px; height: 24px; color: #002f45;" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3
                                style="color: #002f45; font-size: 1.5rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 0.75rem;">
                                Portal Peserta</h3>
                            <p
                                style="color: #002f45; opacity: {{ $isPeserta ? '0.6' : '0.2' }}; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                                Akses materi, jadwal kegiatan, dan kumpulkan tugas harianmu di sini.</p>
                        </div>
                        <div
                            style="margin-top: 2rem; color: #002f45; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; opacity: {{ $isPeserta ? '1' : '0.3' }};">
                            MULAI <span style="font-size: 1.2rem;">→</span>
                        </div>
                    </a>
                @endif
            </div>

            {{-- Tombol Akses Papan Leaderboard Utama --}}
            <div style="margin-top: 2rem;">
                <a href="{{ route('leaderboard.index') }}"
                    style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 1.25rem; background: rgba(210,194,150,0.25); border: 1px solid rgba(210,194,150,0.4); backdrop-filter: blur(5px); border-radius: 1.5rem; text-decoration: none; color: #002f45; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.15rem; transition: all 0.3s ease;"
                    onmouseover="this.style.background='rgba(210,194,150,0.45)'; this.style.transform='translateY(-3px)'"
                    onmouseout="this.style.background='rgba(210,194,150,0.25)'; this.style.transform='translateY(0)'">
                    <span>🏆</span> Lihat Papan Peringkat Akumulasi Leaderboard <span
                        style="font-family: 'Segoe UI', sans-serif; font-size: 1rem; margin-left: 0.25rem;">→</span>
                </a>
            </div>

            {{-- Info Identitas (Footer Style Baru) --}}
            <div
                style="margin-top: 3rem; background: rgba(255,255,255,0.5); backdrop-filter: blur(8px); border-radius: 1.25rem; padding: 1.5rem 2rem; border: 1px solid rgba(255,255,255,0.4); display: flex; gap: 3rem; flex-wrap: wrap; align-items: center;">
                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <span
                        style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">NIM</span>
                    <span
                        style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->nim ?? '-' }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <span
                        style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">Angkatan</span>
                    <span
                        style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->angkatan ?? '-' }}</span>
                </div>

                @if (auth()->user()->kelompok)
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span
                            style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">Kelompok</span>
                        <span
                            style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->kelompok }}</span>
                    </div>
                @endif

                @if (auth()->user()->divisi)
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span
                            style="font-size: 0.7rem; color: #002f45; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em;">Divisi</span>
                        <span
                            style="color: #002f45; font-weight: 700; font-size: 1rem;">{{ auth()->user()->divisi }}</span>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <style>
        .glass-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(210, 194, 150, 0.3);
            background: rgba(210, 194, 150, 0.5) !important;
        }
    </style>

    @php
        $personalBroadcastsJson = $personalBroadcasts->map(function ($broadcast) {
            return [
                'id' => $broadcast->personal_broadcast_id,
                'judul' => optional($broadcast->broadcast)->judul ?? '',
                'konten' => optional($broadcast->broadcast)->konten ?? '',
            ];
        })->values();
    @endphp

    @if ($personalBroadcastsJson->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const broadcasts = @json($personalBroadcastsJson);

                broadcasts.forEach((broadcast, index) => {
                    setTimeout(() => {
                        Swal.fire({
                            title: broadcast.judul,
                            text: broadcast.konten,
                            icon: 'info',
                            confirmButtonText: 'Mengerti',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        }).then(() => {
                            fetch('{{ route('peserta.personal.broadcast.viewed', ['id' => ':id']) }}'.replace(':id', broadcast.id), {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            });
                        });
                    }, index * 350);
                });
            });
        </script>
    @endif

    {{-- MODAL PILIH PESERTA UNTUK ADMIN --}}
    @if (auth()->user()->isAdmin())
    <div id="modalPilihPeserta" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px);">
        <div style="background:white; border-radius:1.5rem; width:100%; max-width:550px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 50px rgba(0,0,0,0.2);">
            {{-- Header Modal --}}
            <div style="padding:1.5rem 1.75rem; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; color:#002f45; font-family:'Playfair Display',serif; font-size:1.35rem; font-weight:700;">Pilih Peserta</h3>
                    <p style="margin:4px 0 0 0; color:#002f45; opacity:0.5; font-size:0.8rem;">Klik nama peserta untuk masuk ke portal sebagai peserta tersebut</p>
                </div>
                <button onclick="tutupModalPeserta()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#002f45; opacity:0.4; padding:0.25rem;">&times;</button>
            </div>

            {{-- Search --}}
            <div style="padding:1rem 1.75rem; border-bottom:1px solid #f3f4f6;">
                <input type="text" id="searchPesertaModal" placeholder="🔍 Cari nama, NIM, atau kelompok..." 
                    style="width:100%; padding:0.7rem 1rem; border:1px solid #e5e7eb; border-radius:0.75rem; outline:none; font-size:0.9rem; box-sizing:border-box;"
                    oninput="filterPesertaModal()">
            </div>

            {{-- Daftar Peserta --}}
            <div id="daftarPesertaModal" style="flex:1; overflow-y:auto; padding:0.5rem 0;">
                <div style="text-align:center; padding:2rem; color:#002f45; opacity:0.4;">
                    <div style="font-size:2rem; margin-bottom:0.5rem;">⏳</div>
                    Memuat data peserta...
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding:1rem 1.75rem; border-top:1px solid #e5e7eb; text-align:center;">
                <span style="color:#002f45; opacity:0.4; font-size:0.75rem;">Total <span id="totalPesertaCount">0</span> peserta</span>
            </div>
        </div>
    </div>

    <script>
        let daftarPeserta = [];

        function bukaModalPeserta() {
            const modal = document.getElementById('modalPilihPeserta');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Load data jika belum
            if (daftarPeserta.length === 0) {
                fetch('{{ route('impersonasi.peserta.list') }}')
                    .then(res => res.json())
                    .then(data => {
                        daftarPeserta = data;
                        renderPeserta(data);
                    })
                    .catch(err => {
                        document.getElementById('daftarPesertaModal').innerHTML = 
                            '<div style="text-align:center; padding:2rem; color:#ef4444;">Gagal memuat data peserta.</div>';
                    });
            }
        }

        function tutupModalPeserta() {
            document.getElementById('modalPilihPeserta').style.display = 'none';
            document.body.style.overflow = '';
        }

        function renderPeserta(data) {
            const container = document.getElementById('daftarPesertaModal');
            document.getElementById('totalPesertaCount').textContent = data.length;

            if (data.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:2rem; color:#002f45; opacity:0.4;">Tidak ada peserta ditemukan.</div>';
                return;
            }

            let html = '';
            data.forEach(p => {
                const genderLabel = p.gender === 'L' ? 'L' : (p.gender === 'P' ? 'P' : '-');
                const genderColor = p.gender === 'L' ? '#3b82f6' : (p.gender === 'P' ? '#ec4899' : '#9ca3af');
                html += `
                    <div onclick="pilihPeserta(${p.id}, '${p.name.replace(/'/g, "\\'")}')" 
                        style="display:flex; align-items:center; gap:1rem; padding:0.85rem 1.75rem; cursor:pointer; transition:0.2s; border-bottom:1px solid #f9fafb;"
                        onmouseover="this.style.background='#f3f4f6'" 
                        onmouseout="this.style.background='transparent'">
                        <div style="width:40px; height:40px; border-radius:50%; background:#e0decd; display:flex; align-items:center; justify-content:center; font-weight:700; color:#002f45; font-size:0.85rem; flex-shrink:0;">
                            ${p.name.charAt(0).toUpperCase()}
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="color:#002f45; font-weight:600; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.name}</div>
                            <div style="display:flex; gap:0.75rem; align-items:center; margin-top:2px;">
                                <span style="color:#002f45; opacity:0.5; font-size:0.75rem;">${p.nim}</span>
                                <span style="color:#002f45; opacity:0.5; font-size:0.75rem;">Kel. ${p.kelompok}</span>
                            </div>
                        </div>
                        <div style="width:28px; height:28px; border-radius:50%; background:${genderColor}20; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:${genderColor}; flex-shrink:0;">
                            ${genderLabel}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function filterPesertaModal() {
            const q = document.getElementById('searchPesertaModal').value.toLowerCase();
            const filtered = daftarPeserta.filter(p => 
                p.name.toLowerCase().includes(q) || 
                p.nim.toLowerCase().includes(q) || 
                p.kelompok.toString().includes(q)
            );
            renderPeserta(filtered);
        }

        function pilihPeserta(id, name) {
            if (!confirm('Masuk ke portal sebagai "' + name + '"?')) return;

            // Submit form impersonasi
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('impersonasi.login', ['id' => ':id']) }}'.replace(':id', id);
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
            document.body.appendChild(form);
            form.submit();
        }

        // Tutup modal jika klik di luar
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalPilihPeserta');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) tutupModalPeserta();
                });
            }
        });
    </script>
    @endif
@endsection
