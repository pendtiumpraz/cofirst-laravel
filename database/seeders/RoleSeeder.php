<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Course management
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
            
            // Class management
            'view classes',
            'create classes',
            'edit classes',
            'delete classes',
            
            // Schedule management
            'view schedules',
            'create schedules',
            'edit schedules',
            'delete schedules',
            
            // Report management
            'view reports',
            'create reports',
            'edit reports',
            'delete reports',
            
            // Financial management
            'view finances',
            'create transactions',
            'edit transactions',
            'delete transactions',
            
            // Parent specific
            'view child reports',
            'view child progress',
            
            // Student specific
            'view own schedule',
            'request schedule change',
            
            // Teacher specific
            'manage attendance',
            'create student reports',
            'handover students',
            'view curriculum',
            
            // Admin specific
            'manage all schedules',
            'activate deactivate users',
            'post monthly reports',
            'view financial reports',
            
            // Super admin specific
            'system administration',
            'manage curriculum',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'parent' => [
                'view courses',
                'view child reports',
                'view child progress',
            ],
            'student' => [
                'view courses',
                'view own schedule',
                'request schedule change',
            ],
            'teacher' => [
                'view schedules',
                'manage attendance',
                'create reports',
                'edit reports',
                'view reports',
                'create student reports',
                'handover students',
                'view curriculum',
            ],
            'admin' => [
                'view users',
                'create users',
                'edit users',
                'view courses',
                'create courses',
                'edit courses',
                'view classes',
                'create classes',
                'edit classes',
                'view schedules',
                'create schedules',
                'edit schedules',
                'delete schedules',
                'manage all schedules',
                'activate deactivate users',
                'post monthly reports',
                'view financial reports',
                'view reports',
            ],
            'finance' => [
                'view finances',
                'create transactions',
                'edit transactions',
                'view financial reports',
            ],
            'superadmin' => [
                // Super admin gets all permissions
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            
            if ($roleName === 'superadmin') {
                // Give all permissions to super admin
                $role->givePermissionTo(Permission::all());
            } else {
                $role->givePermissionTo($rolePermissions);
            }
        }
    }
}
