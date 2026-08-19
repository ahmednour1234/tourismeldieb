<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Admin\ResourceSchema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds one permission per resource/ability pair, plus the roles that group
 * them.
 *
 * Only `{resource}.view` existed before, so every write route would have been
 * denied the moment CRUD started persisting.
 */
final class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var list<string> */
    private const ABILITIES = ['view', 'create', 'update', 'delete'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];

        foreach (ResourceSchema::RESOURCES as $resource) {
            foreach (self::ABILITIES as $ability) {
                $permissions[] = Permission::findOrCreate($resource.'.'.$ability, 'web')->name;
            }
        }

        // Flush again now that the permissions exist: syncPermissions() resolves
        // each name through the registrar's cache, which was populated (empty or
        // stale) before this loop created the rows. Without this, syncing throws
        // "PermissionDoesNotExist" on a fresh database.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Full access to everything.
        Role::findOrCreate('admin', 'web')->syncPermissions($permissions);

        // Can read every resource but change nothing — useful for support staff
        // and for proving the write gates actually gate.
        Role::findOrCreate('viewer', 'web')->syncPermissions(
            collect(ResourceSchema::RESOURCES)
                ->map(fn (string $resource): string => $resource.'.view')
                ->all()
        );

        // Manages the catalogue but not users, roles, or settings.
        Role::findOrCreate('editor', 'web')->syncPermissions(
            collect(['languages', 'currencies', 'countries', 'destinations', 'categories', 'tours', 'options', 'prices'])
                ->crossJoin(self::ABILITIES)
                ->map(fn (array $pair): string => $pair[0].'.'.$pair[1])
                ->all()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
