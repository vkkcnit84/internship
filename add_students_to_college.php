<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;
use App\College;
use App\Stream;
use App\Subject;
use App\Internship;
use App\StudentProfile;
use App\InternshipRegistration;
use Illuminate\Support\Facades\Hash;

echo "Creating Student Profiles and Internship Registrations...\n\n";

// Get colleges and internships
$scienceCollege = College::where('name', 'Delhi College of Science')->first();
$artsCollege = College::where('name', 'Delhi College of Arts')->first();
$commerceCollege = College::where('name', 'Delhi College of Commerce')->first();

$internships = Internship::where('is_active', true)->get();

if (!$scienceCollege || !$artsCollege || !$commerceCollege) {
    echo "❌ Error: Colleges not found. Please run add_college_admins.php first.\n";
    exit(1);
}

if ($internships->count() == 0) {
    echo "❌ Error: No active internships found. Please create internships first.\n";
    exit(1);
}

echo "✓ Found Colleges: {$scienceCollege->name}, {$artsCollege->name}, {$commerceCollege->name}\n";
echo "✓ Found Internships: {$internships->count()} active\n\n";

// Get or create streams and subjects for Science College
$bsStream = Stream::where('college_id', $scienceCollege->id)->first();
$subject = Subject::where('stream_id', optional($bsStream)->id)->first();

if (!$bsStream || !$subject) {
    echo "⚠️  Note: Create Streams and Subjects in admin panel first for better data.\n";
    echo "    For now, using NULL for stream_id and subject_id.\n\n";
}

// Student data for Science College
$scienceStudents = [
    ['name' => 'Aarav Patel', 'email' => 'aarav.patel@student.edu', 'mobile' => '9876543210', 'roll_no' => 'S001'],
    ['name' => 'Priya Singh', 'email' => 'priya.singh@student.edu', 'mobile' => '9876543211', 'roll_no' => 'S002'],
    ['name' => 'Rahul Kumar', 'email' => 'rahul.kumar@student.edu', 'mobile' => '9876543212', 'roll_no' => 'S003'],
    ['name' => 'Anjali Verma', 'email' => 'anjali.verma@student.edu', 'mobile' => '9876543213', 'roll_no' => 'S004'],
    ['name' => 'Akshay Gupta', 'email' => 'akshay.gupta@student.edu', 'mobile' => '9876543214', 'roll_no' => 'S005'],
];

// Student data for Arts College
$artsStudents = [
    ['name' => 'Divya Sharma', 'email' => 'divya.sharma@student.edu', 'mobile' => '9876543215', 'roll_no' => 'A001'],
    ['name' => 'Rohan Nair', 'email' => 'rohan.nair@student.edu', 'mobile' => '9876543216', 'roll_no' => 'A002'],
    ['name' => 'Sneha Chopra', 'email' => 'sneha.chopra@student.edu', 'mobile' => '9876543217', 'roll_no' => 'A003'],
];

// Student data for Commerce College
$commerceStudents = [
    ['name' => 'Vikram Singh', 'email' => 'vikram.singh@student.edu', 'mobile' => '9876543218', 'roll_no' => 'C001'],
    ['name' => 'Neha Reddy', 'email' => 'neha.reddy@student.edu', 'mobile' => '9876543219', 'roll_no' => 'C002'],
    ['name' => 'Arjun Desai', 'email' => 'arjun.desai@student.edu', 'mobile' => '9876543220', 'roll_no' => 'C003'],
];

$collegeStudents = [
    ['college' => $scienceCollege, 'students' => $scienceStudents, 'name' => 'Science'],
    ['college' => $artsCollege, 'students' => $artsStudents, 'name' => 'Arts'],
    ['college' => $commerceCollege, 'students' => $commerceStudents, 'name' => 'Commerce'],
];

$totalCreated = 0;

foreach ($collegeStudents as $collegeData) {
    $college = $collegeData['college'];
    $collegeName = $collegeData['name'];

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📚 Adding students to {$collegeName} College\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    foreach ($collegeData['students'] as $studentData) {
        // Create user
        $user = User::firstOrCreate(
            ['email' => $studentData['email']],
            [
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create student profile
        $profile = StudentProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'university_id' => $college->university_id,
                'college_id' => $college->id,
                'stream_id' => $bsStream ? $bsStream->id : null,
                'subject_id' => $subject ? $subject->id : null,
                'gender' => collect(['MALE', 'FEMALE'])->random(),
                'guardian_name' => "Guardian of {$studentData['name']}",
                'mobile' => $studentData['mobile'],
                'whatsapp' => $studentData['mobile'],
                'registration_number' => $studentData['roll_no'],
                'roll_number' => $studentData['roll_no'],
                'class_semester' => 'Semester 4',
                'year_of_study' => 2,
                'academic_session' => '2024-2025',
            ]
        );

        // Create internship registration
        $internship = $internships->random();
        $registration = InternshipRegistration::firstOrCreate(
            ['user_id' => $user->id, 'internship_id' => $internship->id],
            [
                'user_id' => $user->id,
                'internship_id' => $internship->id,
                'student_profile_id' => $profile->id,
                'price' => $internship->price,
                'tax_percent' => $internship->tax_percent,
                'tax_amount' => $internship->getTaxAmount(),
                'total_amount' => $internship->getTotalAmount(),
                'status' => collect(['pending_payment', 'paid'])->random(),
                'paid_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
            ]
        );

        echo "  ✓ {$studentData['name']} ({$studentData['roll_no']}) - Registered for: {$internship->name}\n";
        $totalCreated++;
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Successfully Created {$totalCreated} Student Registrations!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📊 Summary:\n";
echo "  • Science College: " . count($scienceStudents) . " students\n";
echo "  • Arts College: " . count($artsStudents) . " students\n";
echo "  • Commerce College: " . count($commerceStudents) . " students\n";
echo "  • Total: {$totalCreated} registrations\n\n";

echo "🔐 Student Login Credentials (all use password: 'password'):\n";
foreach ($collegeStudents as $collegeData) {
    echo "\n{$collegeData['name']} College Students:\n";
    foreach ($collegeData['students'] as $student) {
        echo "  • {$student['email']}\n";
    }
}

echo "\n🌐 Login URL: http://127.0.0.1:8000/login\n";
echo "👨‍💼 College Admin Dashboard: http://127.0.0.1:8000/admin\n";
echo "\n✨ Now college admins will see their students' registrations!\n";
