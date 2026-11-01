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

echo "Setting up College Data...\n\n";

// Get colleges
$scienceCollege = College::where('name', 'Delhi College of Science')->first();
$artsCollege = College::where('name', 'Delhi College of Arts')->first();
$commerceCollege = College::where('name', 'Delhi College of Commerce')->first();
$jnuScience = College::where('name', 'JNU School of Sciences')->first();

if (!$scienceCollege || !$artsCollege || !$commerceCollege) {
    echo "❌ Error: Colleges not found. Please run add_college_admins.php first.\n";
    exit(1);
}

$internships = Internship::where('is_active', true)->get();
if ($internships->count() == 0) {
    echo "❌ Error: No active internships found.\n";
    exit(1);
}

echo "Step 1: Creating Streams and Subjects...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Create streams and subjects for each college
$collegeData = [
    [
        'college' => $scienceCollege,
        'name' => 'Science',
        'streams' => [
            ['name' => 'Bachelor of Science (Physics)', 'subjects' => ['Physics', 'Mathematics', 'Chemistry']],
            ['name' => 'Bachelor of Science (Biology)', 'subjects' => ['Biology', 'Chemistry', 'Botany']],
        ]
    ],
    [
        'college' => $artsCollege,
        'name' => 'Arts',
        'streams' => [
            ['name' => 'Bachelor of Arts', 'subjects' => ['Hindi', 'English', 'History', 'Political Science']],
        ]
    ],
    [
        'college' => $commerceCollege,
        'name' => 'Commerce',
        'streams' => [
            ['name' => 'Bachelor of Commerce', 'subjects' => ['Accounting', 'Economics', 'Business Studies']],
        ]
    ],
];

$streamSubjectMap = [];

foreach ($collegeData as $data) {
    $college = $data['college'];
    echo "📚 {$data['name']} College\n";

    foreach ($data['streams'] as $streamData) {
        $stream = Stream::firstOrCreate(
            ['name' => $streamData['name'], 'college_id' => $college->id],
            ['name' => $streamData['name'], 'college_id' => $college->id, 'is_active' => true]
        );
        echo "  ✓ Stream: {$stream->name}\n";

        $subjects = [];
        foreach ($streamData['subjects'] as $subjectName) {
            $subject = Subject::firstOrCreate(
                ['name' => $subjectName, 'stream_id' => $stream->id],
                ['name' => $subjectName, 'stream_id' => $stream->id, 'is_active' => true]
            );
            $subjects[] = $subject;
            echo "    - Subject: {$subject->name}\n";
        }

        $streamSubjectMap[$college->id] = ['stream' => $stream, 'subjects' => $subjects];
    }
    echo "\n";
}

echo "\nStep 2: Creating Student Profiles and Registrations...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Student data
$scienceStudents = [
    ['name' => 'Aarav Patel', 'email' => 'aarav.patel@student.edu', 'mobile' => '9876543210', 'roll_no' => 'S001'],
    ['name' => 'Priya Singh', 'email' => 'priya.singh@student.edu', 'mobile' => '9876543211', 'roll_no' => 'S002'],
    ['name' => 'Rahul Kumar', 'email' => 'rahul.kumar@student.edu', 'mobile' => '9876543212', 'roll_no' => 'S003'],
    ['name' => 'Anjali Verma', 'email' => 'anjali.verma@student.edu', 'mobile' => '9876543213', 'roll_no' => 'S004'],
    ['name' => 'Akshay Gupta', 'email' => 'akshay.gupta@student.edu', 'mobile' => '9876543214', 'roll_no' => 'S005'],
];

$artsStudents = [
    ['name' => 'Divya Sharma', 'email' => 'divya.sharma@student.edu', 'mobile' => '9876543215', 'roll_no' => 'A001'],
    ['name' => 'Rohan Nair', 'email' => 'rohan.nair@student.edu', 'mobile' => '9876543216', 'roll_no' => 'A002'],
    ['name' => 'Sneha Chopra', 'email' => 'sneha.chopra@student.edu', 'mobile' => '9876543217', 'roll_no' => 'A003'],
];

$commerceStudents = [
    ['name' => 'Vikram Singh', 'email' => 'vikram.singh@student.edu', 'mobile' => '9876543218', 'roll_no' => 'C001'],
    ['name' => 'Neha Reddy', 'email' => 'neha.reddy@student.edu', 'mobile' => '9876543219', 'roll_no' => 'C002'],
    ['name' => 'Arjun Desai', 'email' => 'arjun.desai@student.edu', 'mobile' => '9876543220', 'roll_no' => 'C003'],
];

$collegeStudentData = [
    ['college' => $scienceCollege, 'students' => $scienceStudents, 'name' => 'Science'],
    ['college' => $artsCollege, 'students' => $artsStudents, 'name' => 'Arts'],
    ['college' => $commerceCollege, 'students' => $commerceStudents, 'name' => 'Commerce'],
];

$totalCreated = 0;

foreach ($collegeStudentData as $collegeData) {
    $college = $collegeData['college'];
    $collegeName = $collegeData['name'];
    $streamData = $streamSubjectMap[$college->id];
    $stream = $streamData['stream'];
    $subjects = $streamData['subjects'];

    echo "📚 Adding students to {$collegeName} College\n";

    foreach ($collegeData['students'] as $studentData) {
        $user = User::firstOrCreate(
            ['email' => $studentData['email']],
            [
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $subject = $subjects[array_rand($subjects)]; // Random subject from the stream

        $profile = StudentProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'university_id' => $college->university_id,
                'college_id' => $college->id,
                'stream_id' => $stream->id,
                'subject_id' => $subject->id,
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

        $internship = $internships->random();
        $isPaid = rand(0, 1) == 1;

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
                'status' => $isPaid ? 'paid' : 'pending_payment',
                'paid_at' => $isPaid ? now()->subDays(rand(1, 30)) : null,
            ]
        );

        $status = $isPaid ? '✅ Paid' : '⏳ Pending';
        echo "  ✓ {$studentData['name']} ({$studentData['roll_no']}) - {$internship->name} {$status}\n";
        $totalCreated++;
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Setup Complete!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📊 Summary:\n";
echo "  • Created Streams and Subjects for all colleges\n";
echo "  • Created {$totalCreated} Student Registrations\n";
echo "  • Science College: " . count($scienceStudents) . " students\n";
echo "  • Arts College: " . count($artsStudents) . " students\n";
echo "  • Commerce College: " . count($commerceStudents) . " students\n\n";

echo "🔐 College Admin Login:\n";
echo "  • rajesh.science@college.edu / password\n";
echo "  • priya.arts@college.edu / password\n";
echo "  • akshay.commerce@college.edu / password\n\n";

echo "👨‍🎓 Student Login (password is 'password' for all):\n";
foreach ($collegeStudentData as $data) {
    echo "\n{$data['name']} College:\n";
    foreach ($data['students'] as $student) {
        echo "  • {$student['email']}\n";
    }
}

echo "\n\n🌐 URLs:\n";
echo "  • Login: http://127.0.0.1:8000/login\n";
echo "  • College Admin Dashboard: http://127.0.0.1:8000/admin\n";
echo "\n✨ College admins can now see their students' registrations!\n";
