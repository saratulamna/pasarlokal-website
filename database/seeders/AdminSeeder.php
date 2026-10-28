<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) env(
            'PASARLOKAL_ADMIN_NAME',
            'Admin PasarLokal'
        ));

        $email = strtolower(trim((string) env(
            'PASARLOKAL_ADMIN_EMAIL',
            'admin@pasarlokal.test'
        )));

        $password = (string) env(
            'PASARLOKAL_ADMIN_PASSWORD',
            ''
        );

        // Validasi email admin.
        if (
            $email === ''
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new RuntimeException(
                'Email admin tidak valid. Periksa konfigurasi .env.'
            );
        }

        // Password wajib diisi.
        if ($password === '') {
            throw new RuntimeException(
                'PASARLOKAL_ADMIN_PASSWORD belum diisi di file .env.'
            );
        }

        // Password kuat diwajibkan jika aplikasi production.
        if (
            app()->environment('production')
            && (
                strlen($password) < 12
                || in_array(
                    strtolower($password),
                    ['admin123', 'admid123'],
                    true
                )
            )
        ) {
            throw new RuntimeException(
                'Gunakan password admin yang kuat di production.'
            );
        }

        // Membuat atau memperbarui akun admin.
        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );

        $this->command?->info(
            'Akun admin PasarLokal berhasil disiapkan.'
        );

        $this->command?->line(
            'Email: '.$email
        );
    }
}
