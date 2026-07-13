@extends('layouts.app')

@section('content')
    @php
        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
        $selectedRecipientIds = $editingBroadcast?->recipients->pluck('user_id')->all() ?? [];
    @endphp

    <div
        style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Inter', sans-serif;">
        <div style="max-width: 900px; margin: 0 auto;">

            {{-- Header Section --}}
            <div style="margin-bottom: 2.5rem; animation: fadeInDown 0.8s ease-out;">
                <a href="{{ route('panitia.index') }}"
                    style="text-decoration: none; color: #002f45; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; opacity: 0.7; transition: 0.3s;"
                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    ⬅ Kembali ke Dashboard
                </a>
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Broadcast <span style="color:#6b705c; font-style:italic;">Peserta</span>
                </h1>
                <p style="color: #002f45; opacity: 0.6; font-size: 1.1rem;">Siarkan pengumuman atau tautan penting ke portal
                    peserta.</p>
            </div>

            {{-- Glassmorphism Form Card --}}
            <div
                style="background: rgba(255, 255, 255, 0.4); 
                    backdrop-filter: blur(15px); 
                    -webkit-backdrop-filter: blur(15px); 
                    padding: 2.5rem; 
                    border-radius: 2rem; 
                    border: 1px solid rgba(255, 255, 255, 0.6); 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
                    margin-bottom: 3.5rem;
                    animation: fadeInUp 0.8s ease-out;">

                <h4 style="margin-top: 0; color: #002f45; margin-bottom: 1.5rem; font-weight: 800; letter-spacing: -0.5px;">
                    Buat Broadcast Baru</h4>

                <form action="{{ route('panitia.info.peserta.store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div>
                            <label
                                style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">JUDUL
                                INFORMASI</label>
                            <input type="text" name="judul" placeholder="Contoh: Pengingat Atribut" required
                                style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                                onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                        </div>
                        <div>
                            <label
                                style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">KATEGORI</label>
                            <select name="kategori" required
                                style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                                onfocus="this.style.background='white';">
                                <option value="Pengumuman">📢 Pengumuman Tekstual</option>
                                <option value="Materi">📚 Materi/Modul</option>
                                <option value="Link Utama">🔗 Link Utama/Drive</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label
                            style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">ISI
                            PENGUMUMAN (TEKS)</label>
                        <textarea name="konten" rows="4" placeholder="Tulis pesan detail di sini..."
                            style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s; font-family: inherit; resize: vertical;"
                            onfocus="this.style.background='white';"></textarea>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label
                            style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">URL
                            LINK (OPSIONAL)</label>
                        <input type="url" name="url_link" placeholder="https://drive.google.com/..."
                            style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                            onfocus="this.style.background='white';">
                    </div>

                    <button type="submit"
                        style="width: 100%; background: #002f45; color: white; border: none; padding: 1rem; border-radius: 1rem; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,47,69,0.2);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,47,69,0.3)'"
                        onmouseout="this.style.transform='translateY(0)'">
                        Siarkan ke Seluruh Peserta 🚀
                    </button>
                </form>
            </div>

            @if ($isSuperAdmin)
                {{-- Personal Broadcast Section --}}
                <div
                    style="background: rgba(255, 255, 255, 0.4); 
                        backdrop-filter: blur(15px); 
                        -webkit-backdrop-filter: blur(15px); 
                        padding: 2.5rem; 
                        border-radius: 2rem; 
                        border: 1px solid rgba(255, 255, 255, 0.6); 
                        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
                        margin-bottom: 3.5rem;
                        animation: fadeInUp 0.8s ease-out;">

                    <h4 style="margin-top: 0; color: #002f45; margin-bottom: 0.5rem; font-weight: 800; letter-spacing: -0.5px;">
                        Broadcast Personal</h4>
                    <p style="margin: 0 0 1.5rem 0; color: #002f45; opacity: 0.7;">
                        Kirim pesan teks pribadi ke peserta yang dipilih saja, lalu pantau siapa yang sudah melihatnya.
                    </p>

                    <form action="{{ $editingBroadcast ? route('panitia.info.peserta.personal.update', $editingBroadcast->id) : route('panitia.info.peserta.personal.store') }}" method="POST">
                        @csrf
                        @if($editingBroadcast)
                            @method('PUT')
                        @endif

                        <div style="margin-bottom: 1.2rem;">
                            <label style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">JUDUL BROADCAST</label>
                            <input type="text" name="judul" value="{{ old('judul', $editingBroadcast?->judul ?? '') }}" placeholder="Judul Broadcast" required
                                style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                                onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                        </div>

                        <div style="margin-bottom: 1.2rem;">
                            <label style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">ISI PESAN (TEKS POPUP)</label>
                            <textarea name="konten" rows="4" placeholder="Tulis pesan singkat yang akan muncul sebagai popup saat peserta berhasil login..." required
                                style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s; font-family: inherit; resize: vertical;"
                                onfocus="this.style.background='white';">{{ old('konten', $editingBroadcast?->konten ?? '') }}</textarea>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">PILIH PESERTA</label>
                            <div style="border: 1px solid rgba(255,255,255,0.8); border-radius: 1rem; overflow: hidden; background: rgba(255,255,255,0.7); box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);">
                                <div style="padding: 0.8rem 1rem; border-bottom: 1px solid rgba(0,47,69,0.08); background: rgba(255,255,255,0.8);">
                                    <input type="text" id="searchPeserta" placeholder="Cari nama peserta atau kelompok..."
                                        style="width: 100%; padding: 0.7rem 0.9rem; border-radius: 0.9rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.6); outline: none; transition: 0.3s;"
                                        onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                                </div>
                                <div style="max-height: 320px; overflow: auto;">
                                    <table style="width: 100%; border-collapse: collapse;" id="tablePeserta">
                                        <thead>
                                            <tr style="background: rgba(0,47,69,0.04);">
                                                <th style="padding: 0.8rem 1rem; text-align: left; font-size: 0.75rem; color: #002f45; opacity: 0.85;">Nama Peserta</th>
                                                <th style="padding: 0.8rem 1rem; text-align: center; font-size: 0.75rem; color: #002f45; opacity: 0.85; width: 110px;">Kelompok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $groupedPesertas = $participants
                                                    ->groupBy('kelompok')
                                                    ->sortKeysUsing(function ($a, $b) {
                                                        if (is_null($a)) return 1;
                                                        if (is_null($b)) return -1;
                                                        if (is_numeric($a) && is_numeric($b)) {
                                                            return (int) $a <=> (int) $b;
                                                        }
                                                        return strcmp($a, $b);
                                                    });
                                            @endphp
                                            @forelse($groupedPesertas as $noKelompok => $daftarPeserta)
                                                <tr class="group-row" data-group="{{ $noKelompok ?? 'TANPA KELOMPOK' }}" style="background: rgba(0,47,69,0.03); cursor: pointer;">
                                                    <td colspan="2" style="padding: 0.6rem 1rem; font-size: 0.8rem; font-weight: 700; color: #002f45; opacity: 0.8; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                                        <span>🌿 KELOMPOK {{ $noKelompok ?? 'TANPA KELOMPOK' }}</span>
                                                        <button type="button" class="group-toggle-button" style="background: #002f45; color: white; border: none; border-radius: 0.75rem; padding: 0.3rem 0.8rem; cursor: pointer; font-size: 0.8rem;">Pilih semua</button>
                                                    </td>
                                                </tr>
                                                @foreach($daftarPeserta as $participant)
                                                    <tr class="peserta-row" data-id="{{ $participant->id }}" data-name="{{ $participant->name }}" data-kelompok="{{ $participant->kelompok ?? 'TANPA KELOMPOK' }}" style="cursor: pointer; transition: 0.2s;">
                                                        <td style="padding: 0.8rem 1rem; border-top: 1px solid rgba(0,47,69,0.05);">
                                                            <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-weight: 600; color: #002f45;">
                                                                <input type="checkbox" name="recipient_ids[]" value="{{ $participant->id }}" {{ in_array($participant->id, $selectedRecipientIds) ? 'checked' : '' }} style="accent-color: #002f45; width: 16px; height: 16px;">
                                                                {{ $participant->name }}
                                                            </label>
                                                        </td>
                                                        <td style="padding: 0.8rem 1rem; border-top: 1px solid rgba(0,47,69,0.05); text-align: center; color: #002f45; opacity: 0.75;">{{ $participant->kelompok ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="2" style="padding: 1rem; text-align: center; color: #002f45; opacity: 0.6;">Tidak ada data peserta ditemukan.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: #002f45; opacity: 0.7;">Pilih satu atau lebih peserta. Klik baris untuk memilih atau menonaktifkan.</p>
                        </div>

                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <button type="submit"
                                style="background: #002f45; color: white; border: none; padding: 1rem 1.2rem; border-radius: 1rem; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,47,69,0.2);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,47,69,0.3)'"
                                onmouseout="this.style.transform='translateY(0)'">
                                {{ $editingBroadcast ? 'Simpan Perubahan' : 'Kirim Broadcast Personal' }}
                            </button>
                            @if($editingBroadcast)
                                <a href="{{ route('panitia.info.peserta.index') }}"
                                    style="background: #e5e7eb; color: #002f45; padding: 1rem 1.2rem; border-radius: 1rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                                    Batal Edit
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <h4
                    style="color: #002f45; margin-bottom: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                    <span style="display: inline-block; width: 30px; height: 2px; background: #002f45;"></span>
                    Riwayat Broadcast Personal
                </h4>

                <div style="display: flex; flex-direction: column; gap: 1.2rem; margin-bottom: 3.5rem;">
                    @forelse($broadcasts as $broadcast)
                        <div style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 1.5rem; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; border: 1px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.02); transition: 0.3s;"
                            onmouseover="this.style.transform='scale(1.01)'; this.style.background='rgba(255, 255, 255, 0.9)'"
                            onmouseout="this.style.transform='scale(1)'; this.style.background='rgba(255, 255, 255, 0.7)'">

                            <div style="flex: 1;">
                                <span style="font-size: 0.65rem; font-weight: 900; background: #6b705c; color: white; padding: 0.3rem 0.8rem; border-radius: 2rem; letter-spacing: 0.5px;">
                                    PERSONAL
                                </span>
                                <h5 style="margin: 0.8rem 0 0.4rem 0; color: #002f45; font-size: 1.1rem; font-weight: 700;">
                                    {{ $broadcast->judul }}
                                </h5>
                                <p style="margin: 0; font-size: 0.85rem; color: #002f45; opacity: 0.7; line-height: 1.4;">
                                    {{ Str::limit($broadcast->konten, 140) }}
                                </p>
                                <p style="margin: 0.6rem 0 0.2rem 0; font-size: 0.8rem; color: #002f45; opacity: 0.75;">
                                    Target: {{ $broadcast->recipients->count() }} peserta • Sudah lihat: {{ $broadcast->recipients->whereNotNull('viewed_at')->count() }} • Belum lihat: {{ $broadcast->recipients->whereNull('viewed_at')->count() }}
                                </p>
                                <div style="margin-top: 0.7rem; display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                    @foreach($broadcast->recipients as $recipient)
                                        <span style="font-size: 0.72rem; padding: 0.25rem 0.6rem; border-radius: 999px; background: {{ $recipient->viewed_at ? '#dcfce7' : '#fef3c7' }}; color: #002f45;">
                                            {{ $recipient->user->name ?? 'Peserta' }} — {{ $recipient->viewed_at ? 'Sudah lihat' : 'Belum lihat' }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: flex-end;">
                                <a href="{{ route('panitia.info.peserta.index', ['edit' => $broadcast->id]) }}"
                                    style="background: #d2c296; color: #002f45; border: none; padding: 0.7rem 0.9rem; border-radius: 12px; cursor: pointer; transition: 0.3s; text-decoration: none; font-weight: 700;">
                                    ✏️
                                </a>
                                <form action="{{ route('panitia.info.peserta.personal.destroy', $broadcast->id) }}" method="POST" style="margin: 0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus broadcast personal ini?')"
                                        style="background: #ef4444; color: white; border: none; padding: 0.7rem 0.9rem; border-radius: 12px; cursor: pointer; transition: 0.3s;">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 3rem; background: rgba(255,255,255,0.3); border-radius: 2rem; border: 2px dashed rgba(0,47,69,0.1);">
                            <p style="color: #002f45; opacity: 0.5; font-weight: 600;">Belum ada broadcast personal yang dikirim.</p>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- List Section --}}
            <h4
                style="color: #002f45; margin-bottom: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 30px; height: 2px; background: #002f45;"></span>
                Informasi yang Sedang Tayang
            </h4>

            <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                @forelse($infos as $info)
                    <div style="background: rgba(255, 255, 255, 0.7); 
                            backdrop-filter: blur(10px); 
                            padding: 1.5rem; 
                            border-radius: 1.5rem; 
                            display: flex; 
                            justify-content: space-between; 
                            align-items: center; 
                            border: 1px solid white;
                            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
                            transition: 0.3s;"
                        onmouseover="this.style.transform='scale(1.01)'; this.style.background='rgba(255, 255, 255, 0.9)'"
                        onmouseout="this.style.transform='scale(1)'; this.style.background='rgba(255, 255, 255, 0.7)'">

                        <div style="flex: 1; padding-right: 1.5rem;">
                            <span
                                style="font-size: 0.65rem; font-weight: 900; background: #002f45; color: white; padding: 0.3rem 0.8rem; border-radius: 2rem; letter-spacing: 0.5px;">
                                {{ strtoupper($info->kategori) }}
                            </span>
                            <h5 style="margin: 0.8rem 0 0.4rem 0; color: #002f45; font-size: 1.1rem; font-weight: 700;">
                                {{ $info->judul }}</h5>

                            @if ($info->konten)
                                <p style="margin: 0; font-size: 0.85rem; color: #002f45; opacity: 0.7; line-height: 1.4;">
                                    {{ Str::limit($info->konten, 100) }}
                                </p>
                            @endif

                            @if ($info->url_link)
                                <p style="margin: 5px 0 0 0; font-size: 0.75rem; color: #d2c296; font-weight: 700;">🔗
                                    {{ Str::limit($info->url_link, 50) }}</p>
                            @endif
                        </div>

                        <form action="{{ route('panitia.info.peserta.destroy', $info->id) }}" method="POST"
                            style="margin: 0;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus informasi ini dari portal peserta?')"
                                style="background: #ef4444; color: white; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center;"
                                onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                                🗑️
                            </button>
                        </form>
                    </div>
                @empty
                    <div
                        style="text-align: center; padding: 4rem; background: rgba(255,255,255,0.3); border-radius: 2rem; border: 2px dashed rgba(0,47,69,0.1);">
                        <p style="color: #002f45; opacity: 0.5; font-weight: 600;">Belum ada informasi yang disiarkan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchPeserta');
            const table = document.getElementById('tablePeserta');

            if (!searchInput || !table) return;

            const filterRows = function () {
                const query = searchInput.value.toLowerCase().trim();
                const rows = table.querySelectorAll('tr.peserta-row');

                rows.forEach(function (row) {
                    const searchable = `${row.getAttribute('data-name') || ''} ${row.getAttribute('data-kelompok') || ''}`.toLowerCase();
                    const matches = !query || searchable.includes(query);
                    row.style.display = matches ? '' : 'none';
                });
            };

            searchInput.addEventListener('input', filterRows);

            table.querySelectorAll('tr.peserta-row').forEach(function (row) {
                row.addEventListener('click', function (event) {
                    if (event.target.tagName === 'INPUT' || event.target.closest('label')) {
                        return;
                    }

                    const checkbox = row.querySelector('input[name="recipient_ids[]"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                    }
                });
            });

            const getGroupRows = function (groupRow) {
                const rows = [];
                let next = groupRow.nextElementSibling;
                while (next && !next.classList.contains('group-row')) {
                    if (next.classList.contains('peserta-row')) {
                        rows.push(next);
                    }
                    next = next.nextElementSibling;
                }
                return rows;
            };

            const updateGroupButtonText = function (groupRow) {
                const button = groupRow.querySelector('.group-toggle-button');
                if (!button) return;
                const rows = getGroupRows(groupRow);
                const allChecked = rows.every(row => {
                    const checkbox = row.querySelector('input[name="recipient_ids[]"]');
                    return checkbox && checkbox.checked;
                });
                button.textContent = allChecked ? 'Batalkan semua' : 'Pilih semua';
            };

            table.querySelectorAll('tr.group-row').forEach(function (groupRow) {
                const button = groupRow.querySelector('.group-toggle-button');
                const toggleGroup = function () {
                    const rows = getGroupRows(groupRow);
                    const anyUnchecked = rows.some(row => {
                        const checkbox = row.querySelector('input[name="recipient_ids[]"]');
                        return checkbox && !checkbox.checked;
                    });

                    rows.forEach(row => {
                        const checkbox = row.querySelector('input[name="recipient_ids[]"]');
                        if (checkbox) {
                            checkbox.checked = anyUnchecked;
                        }
                    });

                    updateGroupButtonText(groupRow);
                };

                if (button) {
                    button.addEventListener('click', function (event) {
                        event.stopPropagation();
                        toggleGroup();
                    });
                }

                groupRow.addEventListener('click', function (event) {
                    if (event.target.closest('.group-toggle-button')) {
                        return;
                    }
                    toggleGroup();
                });

                updateGroupButtonText(groupRow);
            });
        });
    </script>

    {{-- <style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style> --}}
@endsection
