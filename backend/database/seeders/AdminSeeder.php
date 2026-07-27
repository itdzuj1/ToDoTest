<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LogicException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('admin.seed.name');
        $email = config('admin.seed.email');
        $password = config('admin.seed.password');

        if (! is_string($name) || ! is_string($email) || ! is_string($password)) {
            throw new LogicException('ADMIN_NAME, ADMIN_EMAIL and ADMIN_PASSWORD must be configured before seeding.');
        }

        Admin::query()->firstOrCreate(
            ['email' => Str::lower($email)],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ],
        );
    }
}
