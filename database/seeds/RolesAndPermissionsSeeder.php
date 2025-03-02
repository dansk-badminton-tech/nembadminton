<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role as SystemRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'view teamrounds']);
        Permission::create(['name' => 'create teamrounds']);
        Permission::create(['name' => 'edit teamrounds']);
        Permission::create(['name' => 'delete teamrounds']);
        Permission::create(['name' => 'view cancellations collectors']);
        Permission::create(['name' => 'create cancellations collectors']);
        Permission::create(['name' => 'edit cancellations collectors']);
        Permission::create(['name' => 'delete cancellations collectors']);
        Permission::create(['name' => 'view clubhouse']);
        Permission::create(['name' => 'edit clubhouse']); // This gives permissions to edit permissions
        Permission::create(['name' => 'view members']);
        Permission::create(['name' => 'edit members']);
        Permission::create(['name' => 'create members']);
        Permission::create(['name' => 'delete members']);
        Permission::create(['name' => 'view teams']);

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // or may be done by chaining
        Role::create(['name' => SystemRole::COACH->value])->givePermissionTo([
            Permission::VIEW_TEAMROUNDS->value,
            Permission::CREATE_TEAMROUNDS->value,
            Permission::EDIT_TEAMROUNDS->value,
            Permission::DELETE_TEAMROUNDS->value,
            Permission::VIEW_CANCELLATIONS_COLLECTORS->value,
            Permission::CREATE_CANCELLATIONS_COLLECTORS->value,
            Permission::EDIT_CANCELLATIONS_COLLECTORS->value,
            Permission::DELETE_CANCELLATIONS_COLLECTORS->value,
            Permission::VIEW_CLUBHOUSE->value,
            Permission::EDIT_CLUBHOUSE->value,
            Permission::VIEW_MEMBERS->value,
            Permission::EDIT_MEMBERS->value,
            Permission::CREATE_MEMBERS->value,
            Permission::DELETE_MEMBERS->value,
        ]);

        Role::create(['name' => SystemRole::PLAYER->value])->givePermissionTo([
            Permission::VIEW_TEAMS->value,
        ]);

        $role = Role::create(['name' => SystemRole::SUPERADMIN->value]);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => SystemRole::CLUB_ADMIN->value]);
        $role->givePermissionTo(Permission::all());
    }
}
