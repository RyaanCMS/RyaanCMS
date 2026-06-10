<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MarketplaceItem;
use App\Services\Menu\DefaultSidebarMenuImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name'     => 'RyaanCMS Admin',
            'email'    => 'admin@ryaancms.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'username' => 'admin',
            'is_active'=> true,
            'email_verified_at' => now(),
        ]);

        // Seed default global settings
        $this->call([
            SettingsSeeder::class,
            MarketplaceSeeder::class,
        ]);

        // Seed default sidebar menus for the admin user
        app(DefaultSidebarMenuImporter::class)->ensureForUser($admin);
    }
}
