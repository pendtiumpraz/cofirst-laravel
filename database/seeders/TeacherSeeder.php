<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacherRole = Role::where('name', 'teacher')->first();
        
        if (!$teacherRole) {
            $this->command->error('Teacher role not found. Please run RoleSeeder first.');
            return;
        }

        $teachers = [
            'dika',
            'lazu', 
            'ghaza',
            'galih',
            'fajrul',
            'diyan',
            'rizca',
            'joan',
            'hafidz',
            'savero',
            'unggul',
            'haritz',
            'muslimin'
        ];

        foreach ($teachers as $teacherName) {
            $email = $teacherName . '@coding1st.com';
            
            // Create or update teacher user
            $teacher = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => ucfirst($teacherName),
                    'password' => Hash::make('Codingfirst2025@'),
                    'is_active' => true,
                ]
            );
            
            // Assign teacher role if not already assigned
            if (!$teacher->hasRole('teacher')) {
                $teacher->assignRole($teacherRole);
            }
            
            // Create or update profile
            Profile::updateOrCreate(
                ['user_id' => $teacher->id],
                [
                    'full_name' => ucfirst($teacherName),
                    'phone_number' => '0826' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'address' => 'Jakarta, Indonesia',
                    'birth_date' => now()->subYears(rand(25, 40))->format('Y-m-d'),
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'bio' => 'Experienced coding instructor at Coding First'
                ]
            );
            
            $this->command->info("Created/Updated teacher: {$teacher->name} ({$teacher->email})");
        }
        
        $this->command->info('All teachers seeded successfully!');
    }
}