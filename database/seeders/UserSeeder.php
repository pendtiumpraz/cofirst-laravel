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
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@codingfirst.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$superadmin->hasRole($superadminRole)) {
            $superadmin->assignRole($superadminRole);
        }
        if (!Profile::where('user_id', $superadmin->id)->exists()) {
            Profile::factory()->create(['user_id' => $superadmin->id]);
        }

        // 2. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@codingfirst.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
        if (!Profile::where('user_id', $admin->id)->exists()) {
            Profile::factory()->create(['user_id' => $admin->id]);
        }

        // 3. Create Finance User
        $finance = User::firstOrCreate(
            ['email' => 'finance@codingfirst.com'],
            [
                'name' => 'Finance User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$finance->hasRole($financeRole)) {
            $finance->assignRole($financeRole);
        }
        if (!Profile::where('user_id', $finance->id)->exists()) {
            Profile::factory()->create(['user_id' => $finance->id]);
        }

        // 4. Create Teachers
        // Create specific test teacher first
        $testTeacher = User::firstOrCreate(
            ['email' => 'teacher@codingfirst.com'],
            [
                'name' => 'Test Teacher',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$testTeacher->hasRole($teacherRole)) {
            $testTeacher->assignRole($teacherRole);
        }
        if (!Profile::where('user_id', $testTeacher->id)->exists()) {
            Profile::factory()->create(['user_id' => $testTeacher->id]);
        }

        // Create specific teachers for course specialization
        $specificTeachers = [
            ['name' => 'Dika', 'email' => 'dika@codingfirst.com'],
            ['name' => 'Lazu', 'email' => 'lazu@codingfirst.com'],
            ['name' => 'Ghaza', 'email' => 'ghaza@codingfirst.com'],
            ['name' => 'Galih', 'email' => 'galih@codingfirst.com'],
            ['name' => 'Fajrul', 'email' => 'fajrul@codingfirst.com'],
            ['name' => 'Diyan', 'email' => 'diyan@codingfirst.com'],
            ['name' => 'Rizca', 'email' => 'rizca@codingfirst.com'],
            ['name' => 'Joan', 'email' => 'joan@codingfirst.com'],
            ['name' => 'Hafidz', 'email' => 'hafidz@codingfirst.com'],
            ['name' => 'Savero', 'email' => 'savero@codingfirst.com'],
            ['name' => 'Unggul', 'email' => 'unggul@codingfirst.com'],
            ['name' => 'Haritz', 'email' => 'haritz@codingfirst.com'],
            ['name' => 'Muslimin', 'email' => 'muslimin@codingfirst.com'],
        ];

        foreach ($specificTeachers as $teacherData) {
            $teacher = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if (!$teacher->hasRole($teacherRole)) {
                $teacher->assignRole($teacherRole);
            }
            if (!Profile::where('user_id', $teacher->id)->exists()) {
                Profile::factory()->create(['user_id' => $teacher->id]);
            }
        }

        // Create additional random teachers if needed
        $existingTeachersCount = User::role('teacher')->count();
        $teachersNeeded = max(0, 15 - $existingTeachersCount);
        
        if ($teachersNeeded > 0) {
            $teachers = User::factory($teachersNeeded)->create([
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $teachers->each(function ($teacher) use ($teacherRole) {
                $teacher->assignRole($teacherRole);
                Profile::factory()->create(['user_id' => $teacher->id]);
            });
        }

        // 5. Create Students and Parents (10 users each)
        // Create specific test student first
        $testStudent = User::firstOrCreate(
            ['email' => 'student@codingfirst.com'],
            [
                'name' => 'Test Student',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$testStudent->hasRole($studentRole)) {
            $testStudent->assignRole($studentRole);
        }
        if (!Profile::where('user_id', $testStudent->id)->exists()) {
            Profile::factory()->create(['user_id' => $testStudent->id]);
        }

        // Create specific test parent first
        $testParent = User::firstOrCreate(
            ['email' => 'parent@codingfirst.com'],
            [
                'name' => 'Test Parent',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        if (!$testParent->hasRole($parentRole)) {
            $testParent->assignRole($parentRole);
        }
        if (!Profile::where('user_id', $testParent->id)->exists()) {
            Profile::factory()->create(['user_id' => $testParent->id]);
        }

        // Create additional random students and parents
        $existingStudentsCount = User::role('student')->count();
        $studentsNeeded = max(0, 10 - $existingStudentsCount);
        
        $existingParentsCount = User::role('parent')->count();
        $parentsNeeded = max(0, 10 - $existingParentsCount);
        
        if ($studentsNeeded > 0) {
            $students = User::factory($studentsNeeded)->create([
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            
            $students->each(function ($student) use ($studentRole) {
                $student->assignRole($studentRole);
                Profile::factory()->create(['user_id' => $student->id]);
            });
        }
        
        if ($parentsNeeded > 0) {
            $parents = User::factory($parentsNeeded)->create([
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            
            $parents->each(function ($parent) use ($parentRole) {
                $parent->assignRole($parentRole);
                Profile::factory()->create(['user_id' => $parent->id]);
            });
        }
    }
}