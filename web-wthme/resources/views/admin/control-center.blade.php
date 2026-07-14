@extends('layouts.app')

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:2rem 1.25rem;color:#002f45;">
    <a href="{{ route('admin.index') }}" style="color:#002f45;opacity:.6;text-decoration:none;font-size:.875rem;">← Kembali ke manajemen pengguna</a>

    <div style="display:flex;justify-content:space-between;align-items:end;gap:1rem;flex-wrap:wrap;margin:1.25rem 0 2rem;">
        <div>
            <p style="margin:0 0 .35rem;color:#b8860b;font-size:.75rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Administrator only</p>
            <h1 style="font-family:'Playfair Display',serif;font-size:2rem;margin:0;">Control Center</h1>
            <p style="margin:.45rem 0 0;opacity:.65;">Kelola otoritas, data akun, dan kondisi operasional sistem dari satu tempat.</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="{{ route('admin.export.peserta') }}" style="padding:.65rem .9rem;background:#bdd1d3;color:#002f45;border-radius:.55rem;text-decoration:none;font-size:.8rem;font-weight:700;">Export peserta</a>
            <a href="{{ route('admin.export.panitia') }}" style="padding:.65rem .9rem;background:#002f45;color:#fff;border-radius:.55rem;text-decoration:none;font-size:.8rem;font-weight:700;">Export panitia</a>
        </div>
    </div>

    @if(session('success'))<div style="padding:1rem;border-radius:.7rem;background:#dcfce7;color:#166534;margin-bottom:1rem;">{{ session('success') }}</div>@endif
    @if(session('error'))<div style="padding:1rem;border-radius:.7rem;background:#fee2e2;color:#991b1b;margin-bottom:1rem;">{{ session('error') }}</div>@endif
    @if($errors->any())<div style="padding:1rem;border-radius:.7rem;background:#fee2e2;color:#991b1b;margin-bottom:1rem;">{{ $errors->first() }}</div>@endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;">
        @foreach([['Total akun',$stats['total_users']],['Akun aktif',$stats['active_users']],['Administrator',$stats['admin_users']],['Nonaktif',$stats['inactive_users']]] as [$label, $value])
            <div style="background:#fff;border:1px solid #bdd1d3;border-radius:.85rem;padding:1.15rem;">
                <div style="font-size:.78rem;opacity:.6;">{{ $label }}</div><div style="font-size:1.8rem;font-weight:800;margin-top:.25rem;">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr);gap:1.25rem;margin-bottom:2rem;">
        <section style="background:#fff;border:1px solid #bdd1d3;border-radius:1rem;padding:1.25rem;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin:0 0 1rem;">Kondisi infrastruktur</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div style="background:#f4f7f8;padding:.8rem;border-radius:.6rem;"><small style="opacity:.6;">Environment</small><br><strong>{{ $health['environment'] }}</strong></div>
                <div style="background:#f4f7f8;padding:.8rem;border-radius:.6rem;"><small style="opacity:.6;">Penyimpanan</small><br><strong style="color:{{ $health['storage_ready'] ? '#15803d' : '#b91c1c' }}">{{ $health['storage_ready'] ? 'Siap ditulis' : 'Tidak dapat ditulis' }}</strong></div>
                <div style="background:#f4f7f8;padding:.8rem;border-radius:.6rem;"><small style="opacity:.6;">Cache driver</small><br><strong>{{ $health['cache_driver'] }}</strong></div>
                <div style="background:#f4f7f8;padding:.8rem;border-radius:.6rem;"><small style="opacity:.6;">Antrean gagal</small><br><strong>{{ $health['failed_jobs'] ?? 'Tidak tersedia' }}</strong></div>
            </div>
            <p style="font-size:.78rem;opacity:.55;margin:1rem 0 0;word-break:break-all;">URL aplikasi: {{ $health['app_url'] }} · Queue: {{ $health['queue_driver'] }}</p>
        </section>
        <section style="background:#002f45;color:#fff;border-radius:1rem;padding:1.25rem;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin:0 0:.4rem;color:#d2c296;">Tindakan sistem</h2>
            <p style="font-size:.8rem;opacity:.75;margin:0 0:1rem;">Aman untuk operasional; seluruh tindakan dicatat pada audit log.</p>
            <div style="display:grid;gap:.65rem;">
                <a href="{{ url('/wthme-pma') }}" target="_blank" rel="noopener noreferrer" style="width:100%;box-sizing:border-box;padding:.7rem;border:1px solid #bdd1d3;background:transparent;color:#fff;border-radius:.5rem;text-align:left;text-decoration:none;cursor:pointer;">Buka PhpMyAdmin ↗</a>
                <form method="POST" action="{{ route('admin.system.action', 'clear-cache') }}">@csrf<button onclick="return confirm('Bersihkan cache aplikasi?')" style="width:100%;padding:.7rem;border:1px solid #bdd1d3;background:transparent;color:#fff;border-radius:.5rem;text-align:left;cursor:pointer;">Bersihkan cache aplikasi</button></form>
                <form method="POST" action="{{ route('admin.system.action', 'clear-optimized') }}">@csrf<button onclick="return confirm('Bersihkan seluruh cache optimasi?')" style="width:100%;padding:.7rem;border:0;background:#d2c296;color:#002f45;border-radius:.5rem;text-align:left;cursor:pointer;font-weight:700;">Reset cache optimasi</button></form>
            </div>
        </section>
    </div>

    <section style="background:#fff;border:1px solid #bdd1d3;border-radius:1rem;overflow:hidden;margin-bottom:2rem;">
        <div style="padding:1.25rem;display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;">
            <div><h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin:0;">Otoritas & akses akun</h2><p style="font-size:.8rem;opacity:.6;margin:.35rem 0 0;">Perubahan peran dan status langsung berlaku pada permintaan berikutnya.</p></div>
            <form method="GET"><input name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, atau email" style="padding:.6rem .8rem;border:1px solid #bdd1d3;border-radius:.5rem;"><button style="padding:.6rem .8rem;background:#002f45;color:#fff;border:0;border-radius:.5rem;">Cari</button></form>
        </div>
        <div style="overflow:auto;"><table style="width:100%;border-collapse:collapse;min-width:1000px;">
            <thead><tr style="background:#f4f7f8;text-align:left;font-size:.72rem;text-transform:uppercase;"><th style="padding:.8rem 1rem;">Akun</th><th style="padding:.8rem 1rem;">Peran & divisi</th><th style="padding:.8rem 1rem;">Status</th><th style="padding:.8rem 1rem;">Kelola otoritas</th></tr></thead>
            <tbody>@forelse($users as $user)
                <tr style="border-top:1px solid #e5e7eb;vertical-align:top;"><td style="padding:1rem;"><strong>{{ $user->name }}</strong><br><small style="opacity:.6;">{{ $user->nim ?? '—' }} · {{ $user->email }}</small></td>
                <td style="padding:1rem;"><span style="font-weight:700;text-transform:uppercase;">{{ $user->role }}</span><br><small style="opacity:.6;">{{ $user->divisi ?: 'Tanpa divisi' }}</small></td>
                <td style="padding:1rem;"><span style="color:{{ $user->is_active ? '#15803d' : '#b91c1c' }};font-weight:700;">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>@if ($user->is_active)<button type="button" data-deactivate-user data-user-name="{{ $user->name }}" data-action="{{ route('admin.users.status', $user) }}" style="display:block;margin-top:.5rem;font-size:.72rem;border:0;background:none;color:#002f45;text-decoration:underline;cursor:pointer;">Nonaktifkan</button>@else @if ($user->deactivation_message)<small style="display:block;margin:.45rem 0;max-width:190px;opacity:.65;">{{ $user->deactivation_message }}</small>@endif<form method="POST" action="{{ route('admin.users.status', $user) }}" style="margin-top:.5rem;">@csrf @method('PATCH')<input type="hidden" name="is_active" value="1"><button style="font-size:.72rem;border:0;background:none;color:#002f45;text-decoration:underline;cursor:pointer;">Aktifkan</button></form>@endif</td>
                <td style="padding:1rem;"><form method="POST" action="{{ route('admin.users.authority', $user) }}" style="display:flex;gap:.4rem;align-items:center;">@csrf @method('PUT')<select name="role" style="padding:.45rem;border:1px solid #bdd1d3;border-radius:.4rem;">@foreach(['peserta','panitia','mentor','bendahara','korlap','admin'] as $role)<option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>@endforeach</select><input name="divisi" value="{{ $user->divisi }}" placeholder="Divisi" style="width:95px;padding:.45rem;border:1px solid #bdd1d3;border-radius:.4rem;"><button style="padding:.45rem .65rem;border:0;border-radius:.4rem;background:#002f45;color:#fff;cursor:pointer;">Simpan</button></form></td></tr>
            @empty<tr><td colspan="4" style="padding:2rem;text-align:center;opacity:.6;">Akun tidak ditemukan.</td></tr>@endforelse</tbody>
        </table></div>
    </section>

    <dialog id="deactivation-dialog" style="width:min(460px,calc(100% - 2rem));border:0;border-radius:1rem;padding:0;box-shadow:0 20px 50px rgba(0,0,0,.28);">
        <form id="deactivation-form" method="POST" style="padding:1.5rem;color:#002f45;">
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_active" value="0">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.35rem;margin:0 0:.5rem;">Nonaktifkan akun</h2>
            <p style="font-size:.85rem;line-height:1.5;margin:0 0:1rem;opacity:.7;">Pesan ini akan ditampilkan kepada <strong id="deactivation-user-name"></strong> saat mencoba masuk.</p>
            <label for="deactivation-message" style="display:block;font-size:.8rem;font-weight:700;margin-bottom:.4rem;">Pesan untuk pengguna</label>
            <textarea id="deactivation-message" name="deactivation_message" required maxlength="1000" rows="5" placeholder="Contoh: Akun Anda dinonaktifkan karena ..." style="box-sizing:border-box;width:100%;padding:.7rem;border:1px solid #bdd1d3;border-radius:.5rem;resize:vertical;font:inherit;"></textarea>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;margin-top:1.25rem;"><button type="button" id="cancel-deactivation" style="padding:.6rem .85rem;border:1px solid #bdd1d3;background:#fff;color:#002f45;border-radius:.5rem;cursor:pointer;">Batal</button><button style="padding:.6rem .85rem;border:0;background:#b91c1c;color:#fff;border-radius:.5rem;cursor:pointer;font-weight:700;">Nonaktifkan akun</button></div>
        </form>
    </dialog>

    <section style="background:#fff;border:1px solid #bdd1d3;border-radius:1rem;overflow:hidden;">
        <div style="padding:1.25rem;"><h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin:0;">Audit log terbaru</h2><p style="font-size:.8rem;opacity:.6;margin:.35rem 0 0;">Catatan perubahan otoritas dan tindakan infrastruktur.</p></div>
        @forelse($auditLogs as $log)<div style="border-top:1px solid #e5e7eb;padding:.8rem 1.25rem;display:flex;justify-content:space-between;gap:1rem;font-size:.85rem;"><span><strong>{{ str_replace('.', ' · ', $log->event) }}</strong> <span style="opacity:.65;">oleh {{ $log->actor?->name ?? 'Sistem' }}</span></span><span style="opacity:.55;white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</span></div>@empty<div style="padding:1.25rem;opacity:.6;">Belum ada tindakan yang tercatat.</div>@endforelse
    </section>
</div>
<script>
    const deactivationDialog = document.getElementById('deactivation-dialog');
    const deactivationForm = document.getElementById('deactivation-form');
    const deactivationUserName = document.getElementById('deactivation-user-name');
    const deactivationMessage = document.getElementById('deactivation-message');

    document.querySelectorAll('[data-deactivate-user]').forEach((button) => {
        button.addEventListener('click', () => {
            deactivationForm.action = button.dataset.action;
            deactivationUserName.textContent = button.dataset.userName;
            deactivationMessage.value = '';
            deactivationDialog.showModal();
            deactivationMessage.focus();
        });
    });

    document.getElementById('cancel-deactivation').addEventListener('click', () => deactivationDialog.close());
</script>
@endsection
