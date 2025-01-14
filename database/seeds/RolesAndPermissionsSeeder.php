<?php

namespace Database\Seeders;

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
        Role::create(['name' => 'coach'])->givePermissionTo([
            'view teamrounds',
            'create teamrounds',
            'edit teamrounds',
            'delete teamrounds',
            'view cancellations collectors',
            'create cancellations collectors',
            'edit cancellations collectors',
            'delete cancellations collectors',
            'view clubhouse',
            'edit clubhouse',
            'view members',
            'edit members',
            'create members',
            'delete members'
        ]);

        Role::create(['name' => 'member'])->givePermissionTo([
            'view teams'
        ]);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
