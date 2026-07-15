<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\DailyAbsensiPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Imports\PanitiaImport;
use App\Imports\PesertaImport;
use App\Exports\TemplatePesertaExport; // Pastikan kelas export ini sudah dibuat
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    private const MANAGEABLE_ROLES = ['peserta', 'panitia', 'mentor', 'bendahara', 'korlap', 'admin'];

    /**
     * Halaman Utama Panel Admin
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        // 1. Query Panitia dengan Filter
        $panitiaQuery = User::where('role', 'panitia');
        if ($search) {
            $panitiaQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('nim', 'LIKE', "%{$search}%");
            });
        }
        $panitiaList = $panitiaQuery->orderBy('divisi')->orderBy('name')->get();

        // 2. Query Peserta dengan Filter
        $pesertaQuery = User::where('role', 'peserta');
        if ($search) {
            $pesertaQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('nim', 'LIKE', "%{$search}%");
            });
        }
        $pesertaList = $pesertaQuery->orderBy('kelompok')->orderBy('name')->get();

        // 3. Statistik (Tetap hitung total asli tanpa filter search)
        $totalPeserta = User::where('role', 'peserta')->count();
        $totalPanitia = User::where('role', 'panitia')->count();

        return view('admin.index', compact('totalPeserta', 'totalPanitia', 'panitiaList', 'pesertaList'));
    }

    /**
     * Tampilkan Form Import Panitia
     */
    public function importForm()
    {
        return view('admin.import-panitia');
    }

    /**
     * Proses Upload & Import Panitia
     */
    public function importStore(Request $request)
    {
        set_time_limit(0);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new PanitiaImport();
        Excel::import($import, $request->file('file'));

        $imported = $import->getImported();
        $skipped  = $import->getSkipped();

        $message  = count($imported) . ' akun panitia berhasil dibuat.';
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' data dilewati (sudah ada).';
        }

        return redirect()->route('admin.panitia')
            ->with('success', $message)
            ->with('imported', $imported)
            ->with('skipped', $skipped);
    }

    /**
     * Hapus Akun Panitia
     */
    public function deletePanitia($id)
    {
        $user = User::where('id', $id)->where('role', 'panitia')->firstOrFail();
        $name = $user->name;
        $this->audit(request(), 'user.deleted', $user, ['name' => $name, 'role' => $user->role]);
        $user->delete();

        return back()->with('success', 'Akun panitia ' . $name . ' berhasil dihapus.');
    }

    /**
     * Reset Password Panitia ke NIM
     */
    public function resetPassword($id)
    {
        $user = User::where('id', $id)->where('role', 'panitia')->firstOrFail();
        $user->update([
            'password'             => bcrypt($user->nim),
            'must_change_password' => true,
        ]);
        $this->audit(request(), 'password.reset', $user, ['target_role' => 'panitia']);

        return back()->with('success', 'Password ' . $user->name . ' berhasil direset ke NIM.');
    }

    /**
     * Edit Data Panitia
     */
    public function editPanitia($id)
    {
        $panitia = User::where('id', $id)->where('role', 'panitia')->firstOrFail();
        return view('admin.edit-panitia', compact('panitia'));
    }

    public function updatePanitia(Request $request, $id)
    {
        $panitia = User::where('id', $id)->where('role', 'panitia')->firstOrFail();

        $request->validate([
            'name'     => 'required|string|max:255',
            'nim'      => 'required|string|unique:users,nim,' . $id,
            'angkatan' => 'required|string',
            'divisi'   => 'required|string',
            'email'    => 'required|email|unique:users,email,' . $id,
        ]);

        $panitia->update($request->only('name', 'nim', 'angkatan', 'divisi', 'email'));

        return redirect()->route('admin.panitia')
            ->with('success', 'Data panitia ' . $panitia->name . ' berhasil diperbarui.');
    }

    /**
     * Download Template Excel Panitia (Statis via Folder Public)
     */
    public function downloadTemplate()
    {
        $path = public_path('templates/template-import-panitia.xlsx');
        
        if (!file_exists($path)) {
            return back()->with('error', 'File template panitia belum tersedia di server.');
        }

        return response()->download($path, 'template-import-panitia.xlsx');
    }

    /**
     * Tampilkan Form Import Peserta
     */
    public function importPesertaForm()
    {
        return view('admin.import-peserta');
    }

    /**
     * Proses Upload & Import Peserta
     */
    public function importPesertaStore(Request $request)
    {
        set_time_limit(0);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel peserta wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new PesertaImport();
        Excel::import($import, $request->file('file'));

        $message = count($import->getImported()) . ' data peserta berhasil diimport.';
        if (count($import->getSkipped()) > 0) {
            $message .= ' ' . count($import->getSkipped()) . ' data dilewati.';
        }

        return redirect()->route('admin.index')
            ->with('success', $message)
            ->with('imported', $import->getImported())
            ->with('skipped', $import->getSkipped());
    }

    /**
     * Download Template Excel Peserta (On-The-Fly Tanpa File Fisik / Anti-Error)
     */
    public function downloadTemplatePeserta()
    {
        // Mengunduh instan lewat class Export agar terhindar dari FileNotFoundException
        return Excel::download(new TemplatePesertaExport, 'template-import-peserta.xlsx');
    }

    /**
     * Reset Password Peserta ke NIM
     */
    public function resetPasswordPeserta($id)
    {
        $user = User::where('id', $id)->where('role', 'peserta')->firstOrFail();

        $user->update([
            'password'             => bcrypt($user->nim),
            'must_change_password' => true,
        ]);
        $this->audit(request(), 'password.reset', $user, ['target_role' => 'peserta']);

        return back()->with('success', 'Password peserta ' . $user->name . ' berhasil direset ke NIM.');
    }

    /** Pusat kendali aplikasi yang hanya tersedia untuk admin. */
    public function controlCenter(Request $request)
    {
        if (! Schema::hasColumn('users', 'is_active') || ! Schema::hasTable('audit_logs')) {
            return redirect()->route('admin.index')->with(
                'error',
                'Control Center memerlukan migrasi database. Jalankan php artisan migrate terlebih dahulu.'
            );
        }

        $users = User::query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('role')->orderBy('name')->get();

        $health = [
            'environment' => app()->environment(),
            'app_url' => config('app.url'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
            'storage_ready' => is_writable(storage_path()),
        ];

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        $auditLogs = AuditLog::with('actor:id,name')->latest()->take(20)->get();

        return view('admin.control-center', compact('users', 'health', 'stats', 'auditLogs'));
    }

    public function exportPeserta()
    {
        return Excel::download(
            new UsersExport('peserta'),
            'data-peserta-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function exportPanitia()
    {
        return Excel::download(
            new UsersExport('panitia'),
            'data-panitia-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function updateAuthority(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'role' => ['required', 'in:' . implode(',', self::MANAGEABLE_ROLES)],
            'divisi' => ['nullable', 'string', 'max:100'],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak dapat mencabut otoritas admin dari akun sendiri.');
        }
        if ($user->role === 'admin' && $user->is_active && $data['role'] !== 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Sistem wajib memiliki minimal satu administrator aktif.');
        }

        $before = $user->only(['role', 'divisi']);
        $user->update($data);
        $this->audit($request, 'authority.updated', $user, ['before' => $before, 'after' => $user->only(['role', 'divisi'])]);

        return back()->with('success', "Otoritas {$user->name} berhasil diperbarui.");
    }

    public function updateUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
            'deactivation_message' => ['nullable', 'string', 'max:1000'],
            'ban_reason_id' => ['nullable', 'integer', 'min:1', 'max:999999'],
        ]);
        $active = (bool) $data['is_active'];

        if (! $active && empty($data['deactivation_message']) && empty($data['ban_reason_id'])) {
            return back()->withErrors([
                'deactivation_message' => 'Pesan penonaktifan atau reason ID ban wajib diisi.',
            ])->withInput();
        }

        if ($user->id === $request->user()->id && ! $active) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        if ($user->role === 'admin' && ! $active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Sistem wajib memiliki minimal satu administrator aktif.');
        }

        $isBanned = ! $active && ! empty($data['ban_reason_id']);
        $nim = $user->nim ?? '-';
        $message = $active
            ? null
            : ($isBanned
                ? "Your account has been BANNED, nim: {$nim}, reason_id: {$data['ban_reason_id']}."
                : $data['deactivation_message']);

        $user->update([
            'is_active' => $active,
            'deactivation_message' => $message,
        ]);
        $this->audit($request, $active ? 'user.activated' : ($isBanned ? 'user.banned' : 'user.deactivated'), $user, [
            'deactivation_message' => $message,
            'reason_id' => $data['ban_reason_id'] ?? null,
        ]);

        return back()->with('success', "Akun {$user->name} " . ($active ? 'diaktifkan.' : ($isBanned ? 'dibanned.' : 'dinonaktifkan.')));
    }

    public function runSystemAction(Request $request, string $action)
    {
        $commands = ['clear-cache' => 'cache:clear', 'clear-optimized' => 'optimize:clear'];
        abort_unless(array_key_exists($action, $commands), 404);

        Artisan::call($commands[$action]);
        $this->audit($request, 'system.' . $action, null, ['command' => $commands[$action]]);

        return back()->with('success', 'Tindakan infrastruktur berhasil dijalankan.');
    }

    private function audit(Request $request, string $event, ?User $subject = null, array $properties = []): void
    {
        AuditLog::create([
            'actor_id' => $request->user()->id,
            'event' => $event,
            'subject_type' => $subject ? User::class : null,
            'subject_id' => $subject?->id,
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    /**
     * Manajemen Password Absensi Harian
     */
    public function absensiPasswordIndex()
    {
        $todayPassword = DailyAbsensiPassword::getTodayPassword();
        $recentPasswords = DailyAbsensiPassword::orderBy('tanggal', 'desc')->take(7)->get();
        
        return view('admin.absensi-password', compact('todayPassword', 'recentPasswords'));
    }

    public function absensiPasswordStore(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:50'],
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.max' => 'Password must be at most 50 characters.',
        ]);

        $today = date('Y-m-d');

        // Check if password for today already exists
        $existingPassword = DailyAbsensiPassword::where('tanggal', $today)->first();

        if ($existingPassword) {
            // Update password - store plain for display and hash the original
            $existingPassword->password_tampil = $request->password;
            $existingPassword->password = bcrypt($request->password);
            $existingPassword->dibuat_oleh = $request->user()->id;
            $existingPassword->dibuat_pada = now();
            $existingPassword->save();
            
            $message = 'Attendance password for today has been updated successfully.';
            $this->audit($request, 'absensi_password.updated', null, ['password_date' => $today]);
        } else {
            // Create new password
            DailyAbsensiPassword::create([
                'tanggal' => $today,
                'password' => bcrypt($request->password),
                'password_tampil' => $request->password,
                'dibuat_oleh' => $request->user()->id,
            ]);
            
            $message = 'Attendance password for today has been created successfully.';
            $this->audit($request, 'absensi_password.created', null, ['password_date' => $today]);
        }

        return redirect()->route('admin.absensi.password.index')
            ->with('success', $message);
    }
}
