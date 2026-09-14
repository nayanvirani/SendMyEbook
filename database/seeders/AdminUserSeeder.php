<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds the default platform-owner super admin. The password is never
 * hardcoded here: pass it via ADMIN_DEFAULT_PASSWORD (e.g.
 * `ADMIN_DEFAULT_PASSWORD=... php artisan db:seed --class=AdminUserSeeder`)
 * or let one be generated and printed once.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_DEFAULT_EMAIL', 'virani.nayan@gmail.com');
        $password = env('ADMIN_DEFAULT_PASSWORD');
        $generated = $password === null;
        $password ??= Str::random(20);

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Nayan',
                'password' => Hash::make($password),
                'is_super_admin' => true,
            ]
        );

        if ($generated) {
            $this->command?->warn("Generated super admin password for {$email}: {$password}");
            $this->command?->warn('Save this now — it is not stored anywhere else and will not be shown again.');
        }
    }
}
