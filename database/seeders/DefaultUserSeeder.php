<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        // Đảm bảo role đã tồn tại
        $user = User::firstOrCreate(
            ['name' => 'admin'],
            ['password' => Hash::make('admin')]
        );

        // Gán role (nếu dùng spatie/permission)
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        echo "✅ Đã tạo tài khoản admin mặc định\n";
    }
}
