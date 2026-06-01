<?php

namespace Database\Seeders;

use App\Enums\Permission as SystemPermission;
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
        Permission::findOrCreate(SystemPermission::VIEW_TEAMROUNDS->value);
        Permission::findOrCreate(SystemPermission::CREATE_TEAMROUNDS->value);
        Permission::findOrCreate(SystemPermission::EDIT_TEAMROUNDS->value);
        Permission::findOrCreate(SystemPermission::DELETE_TEAMROUNDS->value);
        Permission::findOrCreate(SystemPermission::VIEW_CANCELLATIONS_COLLECTORS->value);
        Permission::findOrCreate(SystemPermission::CREATE_CANCELLATIONS_COLLECTORS->value);
        Permission::findOrCreate(SystemPermission::EDIT_CANCELLATIONS_COLLECTORS->value);
        Permission::findOrCreate(SystemPermission::DELETE_CANCELLATIONS_COLLECTORS->value);
        Permission::findOrCreate(SystemPermission::VIEW_CLUBHOUSE->value);
        Permission::findOrCreate(SystemPermission::EDIT_CLUBHOUSE->value); // This gives permissions to edit permissions
        Permission::findOrCreate(SystemPermission::VIEW_MEMBERS->value);
        Permission::findOrCreate(SystemPermission::EDIT_MEMBERS->value);
        Permission::findOrCreate(SystemPermission::CREATE_MEMBERS->value);
        Permission::findOrCreate(SystemPermission::DELETE_MEMBERS->value);
        Permission::findOrCreate(SystemPermission::VIEW_TEAMS->value);
        Permission::findOrCreate(SystemPermission::CREATE_TEAMS->value);
        Permission::findOrCreate(SystemPermission::EDIT_TEAMS->value);
        Permission::findOrCreate(SystemPermission::DELETE_TEAMS->value);

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // or may be done by chaining
        Role::updateOrCreate(['name' => SystemRole::COACH->value, 'clubhouse_id' => null])->givePermissionTo([
            SystemPermission::VIEW_TEAMROUNDS->value,
            SystemPermission::CREATE_TEAMROUNDS->value,
            SystemPermission::EDIT_TEAMROUNDS->value,
            SystemPermission::DELETE_TEAMROUNDS->value,
            SystemPermission::VIEW_CANCELLATIONS_COLLECTORS->value,
            SystemPermission::CREATE_CANCELLATIONS_COLLECTORS->value,
            SystemPermission::EDIT_CANCELLATIONS_COLLECTORS->value,
            SystemPermission::DELETE_CANCELLATIONS_COLLECTORS->value,
            SystemPermission::VIEW_CLUBHOUSE->value,
            SystemPermission::EDIT_CLUBHOUSE->value,
            SystemPermission::VIEW_MEMBERS->value,
            SystemPermission::EDIT_MEMBERS->value,
            SystemPermission::CREATE_MEMBERS->value,
            SystemPermission::DELETE_MEMBERS->value,
            SystemPermission::VIEW_TEAMS->value,
            SystemPermission::CREATE_TEAMS->value,
            SystemPermission::EDIT_TEAMS->value,
            SystemPermission::DELETE_TEAMS->value,
        ]);

        Role::updateOrCreate(['name' => SystemRole::PLAYER->value, 'clubhouse_id' => null])->givePermissionTo([
            SystemPermission::VIEW_TEAMS->value,
        ]);

        $role = Role::updateOrCreate(['name' => SystemRole::SUPERADMIN->value, 'clubhouse_id' => null]);
        $role->givePermissionTo(Permission::all());

        $role = Role::updateOrCreate(['name' => SystemRole::CLUB_ADMIN->value, 'clubhouse_id' => null]);
        $role->givePermissionTo(Permission::all());
    }
}
