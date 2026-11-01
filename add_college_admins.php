<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\University;
use App\College;
use App\User;
use Illuminate\Support\Facades\Hash;

echo "Creating Universities and Colleges...\n";

// Get or create universities
$du = University::firstOrCreate(
    ['name' => 'Delhi University']
);
echo "✓ University: {$du->name}\n";

$jnu = University::firstOrCreate(
    ['name' => 'Jawaharlal Nehru University']
);
echo "✓ University: {$jnu->name}\n";

// Get or create colleges
$scienceCollege = College::firstOrCreate(
    ['name' => 'Delhi College of Science', 'university_id' => $du->id],
    ['name' => 'Delhi College of Science', 'university_id' => $du->id]
);
echo "✓ College: {$scienceCollege->name}\n";

$artsCollege = College::firstOrCreate(
    ['name' => 'Delhi College of Arts', 'university_id' => $du->id],
    ['name' => 'Delhi College of Arts', 'university_id' => $du->id]
);
echo "✓ College: {$artsCollege->name}\n";

$commerceCollege = College::firstOrCreate(
    ['name' => 'Delhi College of Commerce', 'university_id' => $du->id],
    ['name' => 'Delhi College of Commerce', 'university_id' => $du->id]
);
echo "✓ College: {$commerceCollege->name}\n";

$jnuScience = College::firstOrCreate(
    ['name' => 'JNU School of Sciences', 'university_id' => $jnu->id],
    ['name' => 'JNU School of Sciences', 'university_id' => $jnu->id]
);
echo "✓ College: {$jnuScience->name}\n";

echo "\nCreating College Admin Users...\n";

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

    echo "✓ Created: {$admin['name']} ({$admin['college_name']}) - {$admin['email']}\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "College Admin Users Created Successfully!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Login Credentials:\n";
foreach ($collegeAdmins as $admin) {
    echo "  📧 {$admin['email']}\n";
    echo "  🔑 password\n";
    echo "  🏫 {$admin['college_name']}\n\n";
}
echo "URL: http://127.0.0.1:8000/login\n";
echo "Dashboard: http://127.0.0.1:8000/admin\n";
