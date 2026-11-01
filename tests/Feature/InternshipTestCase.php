<?php

namespace Tests\Feature;

use App\College;
use App\Internship;
use App\Services\Razorpay\RazorpayClient;
use App\Stream;
use App\Subject;
use App\University;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Shared fixtures for the internship module. Not itself a test (name has no
 * "Test.php" suffix). Runs against in-memory SQLite (see phpunit.xml), never the dev DB.
 */
abstract class InternshipTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new \PermissionsTableSeeder)->run();
        (new \RolesTableSeeder)->run();
        (new \PermissionRoleTableSeeder)->run();
        (new \InternshipPermissionsSeeder)->run();
        (new \InternshipClassPermissionsSeeder)->run();

        $this->defineGates();

        config([
            'razorpay.key_id'         => 'rzp_test_abc',
            'razorpay.key_secret'     => 'test_secret',
            'razorpay.webhook_secret' => 'webhook_secret',
        ]);

        // Never call the real gateway from tests.
        $this->app->instance(RazorpayClient::class, new class extends RazorpayClient {
            public $orders = 0;

            public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
            {
                return ['id' => 'order_test_' . (++$this->orders), 'amount' => $amountPaise];
            }
        });
    }

    protected function admin(): User
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $user->roles()->attach(1);

        return $user;
    }

    protected function chain(string $suffix = ''): array
    {
        $university = University::create(['name' => 'Univ' . $suffix]);
        $college    = College::create(['name' => 'College' . $suffix, 'university_id' => $university->id]);
        $stream     = Stream::create(['name' => 'BA' . $suffix, 'college_id' => $college->id]);
        $subject    = Subject::create(['name' => 'Hindi' . $suffix, 'stream_id' => $stream->id]);

        return compact('university', 'college', 'stream', 'subject');
    }

    protected function internship(array $attributes = [], array $chain = null): Internship
    {
        // If no college_id is provided, use the chain if available, or create a default one
        if (!isset($attributes['college_id'])) {
            if ($chain === null) {
                $chain = $this->chain();
            }
            $attributes['college_id'] = $chain['college']->id;
        }
        return Internship::create($attributes + ['name' => 'UGC Internship', 'price' => 1000, 'tax_percent' => 0, 'is_active' => true]);
    }

    protected function registrationPayload(array $chain, Internship $internship, array $overrides = []): array
    {
        return $overrides + [
            'name'                => 'Asha Verma',
            'email'               => 'asha@example.com',
            'mobile'              => '9876543210',
            'whatsapp'            => '9876543210',
            'guardian_name'       => 'Ramesh Verma',
            'gender'              => 'female',
            'password'            => 'secret123',
            'payment_method'      => 'offline',
            'internship_id'       => $internship->id,
            'university_id'       => $chain['university']->id,
            'college_id'          => $chain['college']->id,
            'stream_id'           => $chain['stream']->id,
            'subject_id'          => $chain['subject']->id,
            'class_semester'      => 'Semester 3',
            'year_of_study'       => '2nd Year',
            'registration_number' => 'REG123',
            'roll_number'         => '42',
            'academic_session'    => \App\StudentProfile::academicSessionOptions()[array_key_first(\App\StudentProfile::academicSessionOptions())],
        ];
    }

    /**
     * Mirrors App\Http\Middleware\AuthGates, which skips itself in the console
     * (and PHPUnit always runs in the console).
     */
    protected function defineGates(): void
    {
        $byPermission = [];

        foreach (\App\Role::with('permissions')->get() as $role) {
            foreach ($role->permissions as $permission) {
                $byPermission[$permission->title][] = $role->id;
            }
        }

        foreach ($byPermission as $title => $roleIds) {
            Gate::define($title, function (User $user) use ($roleIds) {
                return count(array_intersect($user->roles->pluck('id')->toArray(), $roleIds)) > 0;
            });
        }
    }
}
