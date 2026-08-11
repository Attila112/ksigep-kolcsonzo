<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            [
                'email' => 'admin@kisgep.test',
            ],
            [
                'name' => 'Development Admin',
                'password' => Hash::make('Admin123!'),
                'role' => 'ADMIN',
                'active' => true,
            ]
        );
    }
}