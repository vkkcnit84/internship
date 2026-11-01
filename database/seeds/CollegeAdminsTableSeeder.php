<?php

use Illuminate\Database\Seeder;
use App\University;
use App\College;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;

class CollegeAdminsTableSeeder extends Seeder
{
    public function run()
    {
        // Get or create universities
        $du = University::firstOrCreate(
            ['code' => 'DU'],
            ['name' => 'Delhi University']
        );

        $jnu = University::firstOrCreate(
            ['code' => 'JNU'],
            ['name' => 'Jawaharlal Nehru University']
        );

        // Get or create colleges under Delhi University
        $scienceCollege = College::firstOrCreate(
            ['name' => 'Delhi College of Science', 'university_id' => $du->id],
            ['name' => 'Delhi College of Science', 'university_id' => $du->id]
        );

        $artsCollege = College::firstOrCreate(
            ['name' => 'Delhi College of Arts', 'university_id' => $du->id],
            ['name' => 'Delhi College of Arts', 'university_id' => $du->id]
        );

        $commerceCollege = College::firstOrCreate(
            ['name' => 'Delhi College of Commerce', 'university_id' => $du->id],
            ['name' => 'Delhi College of Commerce', 'university_id' => $du->id]
        );

        // Get or create college under JNU
        $jnuScience = College::firstOrCreate(
            ['name' => 'JNU School of Sciences', 'university_id' => $jnu->id],
            ['name' => 'JNU School of Sciences', 'university_id' => $jnu->id]
        );

        // Create college admin users
        $collegeAdmins = [
            [
                'name' => 'Dr. Rajesh Kumar',
                'email' => 'rajesh.science@college.edu',
                'college_id' => $scienceCollege->id,
                'college_name' => 'Science College',
            ],
            [
                'name' => 'Prof. Priya Sharma',
                'email' => 'priya.arts@college.edu',
                'college_id' => $artsCollege->id,
                'college_name' => 'Arts College',
            ],
            [
                'name' => 'Dr. Akshay Verma',
                'email' => 'akshay.commerce@college.edu',
                'college_id' => $commerceCollege->id,
                'college_name' => 'Commerce College',
            ],
            [
                'name' => 'Prof. Anjali Gupta',
                'email' => 'anjali.jnu@college.edu',
                'college_id' => $jnuScience->id,
                'college_name' => 'JNU Science',
            ],
        ];

        foreach ($collegeAdmins as $admin) {
            $user = User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => Hash::make('password'),
                    'college_id' => $admin['college_id'],
                    'email_verified_at' => now(),
                ]
            );

            // Assign a role (use role_id 3 or create a college admin role)
            // For now, using Student role (3) since college admins aren't admin or institution
            // You can change this to a dedicated COLLEGE_ADMIN role if created
            $user->roles()->syncWithoutDetaching([3]); // Student role

            echo "Created college admin: {$admin['name']} ({$admin['college_name']}) - {$admin['email']}\n";
        }

        echo "\nCollege admin users created successfully!\n";
        echo "Use these credentials to login:\n";
        foreach ($collegeAdmins as $admin) {
            echo "  Email: {$admin['email']}, Password: password\n";
        }
    }
}
