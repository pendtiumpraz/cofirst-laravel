<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Non-aktifkan pengecekan foreign key sementara
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // User::truncate();
        // Profile::truncate();
        // DB::table('model_has_roles')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil semua roles
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'teacher')->first();
        $studentRole = Role::where('name', 'student')->first();
        $parentRole = Role::where('name', 'parent')->first();
        $financeRole = Role::where('name', 'finance')->first();

        // 1. Create Super Admin
        $superadmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@codingfirst.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superadmin->assignRole($superadminRole);
        Profile::factory()->create(['user_id' => $superadmin->id]);

        // 2. Create Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@codingfirst.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);
        Profile::factory()->create(['user_id' => $admin->id]);

        // 3. Create Finance User
        $finance = User::factory()->create([
            'name' => 'Finance User',
            'email' => 'finance@codingfirst.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $finance->assignRole($financeRole);
        Profile::factory()->create(['user_id' => $finance->id]);

        // 4. Create Teachers (5 users)
        $teachers = User::factory(5)->create([
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $teachers->each(function ($teacher) use ($teacherRole) {
            $teacher->assignRole($teacherRole);
            Profile::factory()->create(['user_id' => $teacher->id]);
        });

        // 5. Create Students and Parents (10 users each)
        $students = User::factory(10)->create([
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $parents = User::factory(10)->create([
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $students->each(function ($student, $key) use ($studentRole, $parents, $parentRole) {
            // Assign student role
            $student->assignRole($studentRole);
            Profile::factory()->create(['user_id' => $student->id]);

            // Get a parent
            $parent = $parents->get($key);
            if ($parent) {
                // Assign parent role
                $parent->assignRole($parentRole);
                Profile::factory()->create(['user_id' => $parent->id]);

                // Attach student to parent
                // Pastikan tabel pivot 'parent_student' ada dan model Parent memiliki relasi students()
                // Asumsi: model Parent (atau User) punya relasi many-to-many 'students'
                // $parent->students()->attach($student->id);
            }
        });
    }
}