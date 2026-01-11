<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jika sudah ada user, jangan buat lagi
        if (User::count() == 0) {
            User::create([
                'name' => 'Admin Filament',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'), // default password
            ]);
        }
    }
}
