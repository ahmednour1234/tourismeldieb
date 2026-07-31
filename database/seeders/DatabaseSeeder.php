<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear Spatie's permission cache before seeding. After migrate:fresh
        // the permission tables are empty, but the cached set survives the
        // migration — so the first givePermissionTo/syncRoles throws
        // "PermissionDoesNotExist" against a stale cache. This is why a fresh
        // seed failed on the server.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::query()->firstOrNew(['email' => 'test@example.com']);
        $user->forceFill([
            'name' => 'Test User',
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => Hash::make('password'),
        ])->save();

        $this->call([
            PermissionSeeder::class,
            SettingSeeder::class,
            TourismCatalogSeeder::class,
            PhaseTwoTourOperationsSeeder::class,
            ContentSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // The demo admin holds the `admin` role rather than a hand-listed set
        // of permissions: the previous list granted only `.view`, so every
        // create/update/delete route would 403 once persistence was real.
        $admin = User::query()->firstOrNew(['email' => 'admin@hurgadaguide.example']);
        $admin->forceFill([
            'name' => 'Demo Admin',
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => Hash::make('password'),
        ])->save();

        $admin->syncRoles(['admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
