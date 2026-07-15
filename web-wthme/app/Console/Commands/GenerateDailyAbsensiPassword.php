<?php

namespace App\Console\Commands;

use App\Models\DailyAbsensiPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateDailyAbsensiPassword extends Command
{
    protected $signature = 'absensi:generate-password {--show : Show the generated password}';
    protected $description = 'Generate or update daily attendance password automatically';

    public function handle()
    {
        $today = date('Y-m-d');
        $password = strtoupper(Str::random(8));

        $existingPassword = DailyAbsensiPassword::where('tanggal', $today)->first();

        if ($existingPassword) {
            $existingPassword->password_tampil = $password;
            $existingPassword->password = $password;
            $existingPassword->dibuat_oleh = null;
            $existingPassword->dibuat_pada = now();
            $existingPassword->save();
            
            $this->info("Password absensi hari ini berhasil diperbarui.");
        } else {
            DailyAbsensiPassword::create([
                'tanggal' => $today,
                'password' => $password,
                'password_tampil' => $password,
                'dibuat_oleh' => null,
                'dibuat_pada' => now(),
            ]);
            
            $this->info("Password absensi hari ini berhasil dibuat.");
        }

        if ($this->option('show')) {
            $this->line("Password hari ini: <comment>{$password}</comment>");
        }

        $this->info("Tanggal: {$today}");
    }
}