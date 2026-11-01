<?php

namespace Tests\Feature;

use App\InternshipClass;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InternshipClassTest extends InternshipTestCase
{
    private function payload($internship, array $overrides = []): array
    {
        // 2026-12-07 is a Monday.
        return $overrides + [
            'internship_id' => $internship->id,
            'title'         => 'Morning batch',
            'mode'          => 'online',
            'meeting_link'  => 'https://meet.example.com/abc',
            'recurrence'    => 'weekly',
            'days_of_week'  => [1, 3, 5],
            'start_date'    => '2026-12-07',
            'end_date'      => '2026-12-21',
            'start_time'    => '10:00',
            'end_time'      => '11:30',
            'is_active'     => 1,
        ];
    }

    private function collegeAdmin($college): User
    {
        $user = User::create([
            'name' => 'College Admin', 'email' => 'ca' . $college->id . '@example.com',
            'password' => 'password', 'college_id' => $college->id,
        ]);
        $user->roles()->attach(4);

        return $user;
    }

    private function makeClass($internship, array $overrides = []): InternshipClass
    {
        return InternshipClass::create($overrides + [
            'internship_id' => $internship->id, 'title' => 'Class', 'mode' => 'online',
            'recurrence' => 'weekly', 'days_of_week' => '1,3,5',
            'start_date' => '2026-12-07', 'end_date' => '2026-12-21',
            'start_time' => '10:00:00', 'end_time' => '11:00:00', 'is_active' => true,
        ]);
    }

    public function test_admin_creates_a_weekly_recurring_class_on_behalf_of_a_college()
    {
        $internship = $this->internship();

        $this->actingAs($this->admin())
            ->post('/admin/internship-classes', $this->payload($internship))
            ->assertRedirect('/admin/internship-classes');

        $this->assertDatabaseHas('internship_classes', [
            'internship_id' => $internship->id,
            'title'         => 'Morning batch',
            'recurrence'    => 'weekly',
            'days_of_week'  => '1,3,5',
        ]);
        $this->assertSame('2026-12-21', InternshipClass::first()->end_date->toDateString());
    }

    public function test_one_time_class_ends_on_its_start_date()
    {
        $internship = $this->internship();

        $this->actingAs($this->admin())->post('/admin/internship-classes', $this->payload($internship, [
            'recurrence' => 'none', 'days_of_week' => null, 'end_date' => null,
        ]))->assertRedirect('/admin/internship-classes');

        $class = InternshipClass::first();
        $this->assertEquals('2026-12-07', $class->end_date->toDateString());
        $this->assertNull($class->days_of_week);
    }

    public function test_recurring_dates_and_totals_are_computed()
    {
        $class = $this->makeClass($this->internship());

        $dates = array_map(function ($d) { return $d->format('D d'); }, $class->occurrences(Carbon::parse('2026-12-07'), 50));

        $this->assertSame(['Mon 07', 'Wed 09', 'Fri 11', 'Mon 14', 'Wed 16', 'Fri 18', 'Mon 21'], $dates);
        $this->assertSame(7, $class->totalSessions());
        $this->assertSame('Mon, Wed, Fri', $class->schedule_summary);
        $this->assertSame('10:00 AM - 11:00 AM', $class->time_range);
        $this->assertSame('Wed 09', $class->occurrences(Carbon::parse('2026-12-08'), 1)[0]->format('D d'));
    }

    public function test_validation_rejects_bad_schedules()
    {
        $internship = $this->internship();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/internship-classes', $this->payload($internship, ['days_of_week' => []]))
            ->assertSessionHasErrors('days_of_week');

        $this->actingAs($admin)->post('/admin/internship-classes', $this->payload($internship, ['end_time' => '09:00']))
            ->assertSessionHasErrors('end_time');

        $this->actingAs($admin)->post('/admin/internship-classes', $this->payload($internship, ['end_date' => '2026-12-01']))
            ->assertSessionHasErrors('end_date');

        // Only Mon 7 and Tue 8 fall in range, but the class is on Saturdays.
        $this->actingAs($admin)->post('/admin/internship-classes', $this->payload($internship, [
            'days_of_week' => [6], 'end_date' => '2026-12-08',
        ]))->assertSessionHasErrors('days_of_week');

        $this->assertSame(0, InternshipClass::count());
    }

    public function test_college_admin_only_sees_and_manages_their_own_colleges_classes()
    {
        $chainA = $this->chain('A');
        $chainB = $this->chain('B');
        $internshipA = $this->internship(['name' => 'Intern A'], $chainA);
        $internshipB = $this->internship(['name' => 'Intern B'], $chainB);
        $classA = $this->makeClass($internshipA, ['title' => 'Class of A']);
        $classB = $this->makeClass($internshipB, ['title' => 'Class of B']);

        $adminA = $this->collegeAdmin($chainA['college']);

        $this->actingAs($adminA)->get('/admin/internship-classes')
            ->assertOk()->assertSee('Class of A')->assertDontSee('Class of B');

        $this->actingAs($adminA)->get("/admin/internship-classes/{$classB->id}")->assertNotFound();
        $this->actingAs($adminA)->delete("/admin/internship-classes/{$classB->id}")->assertNotFound();

        $this->actingAs($adminA)->post('/admin/internship-classes', $this->payload($internshipB))
            ->assertSessionHasErrors('internship_id');

        $this->actingAs($adminA)->post('/admin/internship-classes', $this->payload($internshipA, ['title' => 'New A class']))
            ->assertRedirect('/admin/internship-classes');
        $this->assertDatabaseHas('internship_classes', ['title' => 'New A class', 'created_by' => $adminA->id]);
    }

    public function test_super_admin_sees_every_college_and_can_filter()
    {
        $chainA = $this->chain('A');
        $chainB = $this->chain('B');
        $this->makeClass($this->internship(['name' => 'Intern A'], $chainA), ['title' => 'Class of A']);
        $this->makeClass($this->internship(['name' => 'Intern B'], $chainB), ['title' => 'Class of B']);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/internship-classes')->assertSee('Class of A')->assertSee('Class of B');
        $this->actingAs($admin)->get('/admin/internship-classes?college_id=' . $chainB['college']->id)
            ->assertSee('Class of B')->assertDontSee('Class of A');
    }

    public function test_pages_render()
    {
        $admin = $this->admin();
        $class = $this->makeClass($this->internship());

        $this->actingAs($admin)->get('/admin/internship-classes')->assertOk();
        $this->actingAs($admin)->get('/admin/internship-classes/create')->assertOk();
        $this->actingAs($admin)->get("/admin/internship-classes/{$class->id}")->assertOk()->assertSee('Total sessions');
        $this->actingAs($admin)->get("/admin/internship-classes/{$class->id}/edit")->assertOk();
    }

    public function test_college_logo_can_be_uploaded_on_create_and_replaced_on_edit()
    {
        Storage::fake('public');
        $admin = $this->admin();
        $university = \App\University::create(['name' => 'Univ']);

        $this->actingAs($admin)->post('/admin/colleges', [
            'name' => 'Logo College', 'university_id' => $university->id,
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])->assertRedirect('/admin/colleges');

        $college = \App\College::where('name', 'Logo College')->first();
        $this->assertNotNull($college->logo);
        Storage::disk('public')->assertExists($college->logo);
        $old = $college->logo;

        $this->actingAs($admin)->put("/admin/colleges/{$college->id}", [
            'name' => 'Logo College', 'university_id' => $university->id,
            'logo' => UploadedFile::fake()->image('new.jpg', 200, 200),
        ])->assertRedirect('/admin/colleges');

        $college->refresh();
        $this->assertNotSame($old, $college->logo);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($college->logo);

        // Updating without a file keeps the current logo.
        $kept = $college->logo;
        $this->actingAs($admin)->put("/admin/colleges/{$college->id}", ['name' => 'Renamed', 'university_id' => $university->id]);
        $this->assertSame($kept, $college->fresh()->logo);
    }

    public function test_college_logo_rejects_non_images()
    {
        Storage::fake('public');
        $university = \App\University::create(['name' => 'Univ']);

        $this->actingAs($this->admin())->post('/admin/colleges', [
            'name' => 'Bad Logo', 'university_id' => $university->id,
            'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('logo');

        $this->assertDatabaseMissing('colleges', ['name' => 'Bad Logo']);
    }

    public function test_college_logo_shows_in_the_header_for_college_admin_and_student()
    {
        Storage::fake('public');
        $chain = $this->chain();
        $chain['college']->update(['logo' => 'colleges/mylogo.png']);

        $collegeAdmin = $this->collegeAdmin($chain['college']);
        $this->actingAs($collegeAdmin)->get('/admin')->assertOk()->assertSee('colleges/mylogo.png');

        $student = User::create(['name' => 'Stu', 'email' => 'stu@example.com', 'password' => 'password']);
        $student->roles()->attach(3);
        \App\StudentProfile::create([
            'user_id' => $student->id, 'mobile' => '9876543210', 'whatsapp' => '9876543210', 'guardian_name' => 'G', 'gender' => 'male',
            'university_id' => $chain['university']->id, 'college_id' => $chain['college']->id,
            'stream_id' => $chain['stream']->id, 'subject_id' => $chain['subject']->id,
            'class_semester' => 'Semester 1', 'year_of_study' => '1st Year',
            'registration_number' => 'R1', 'roll_number' => '1', 'academic_session' => '2026-27',
        ]);
        $this->actingAs($student)->get('/admin/my-profile')->assertOk()->assertSee('colleges/mylogo.png');

        // Super admin keeps the site title.
        $this->actingAs($this->admin())->get('/admin')->assertDontSee('colleges/mylogo.png');
    }

    public function test_edit_page_shows_the_current_logo_even_if_app_url_differs()
    {
        config(['app.url' => 'http://some-other-host.test']);
        $chain = $this->chain();
        $chain['college']->update(['logo' => 'colleges/old-logo.png']);
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get("/admin/colleges/{$chain['college']->id}/edit")->assertOk();
        $response->assertSee('/storage/colleges/old-logo.png', false);
        $response->assertDontSee('some-other-host.test');

        $this->actingAs($admin)->get('/admin/colleges')->assertSee('/storage/colleges/old-logo.png', false);
    }

    private function enrol(array $chain, $internship, string $name, string $status, string $whatsapp = '9876543210', string $mobile = '9876543210'): User
    {
        static $n = 0;
        $n++;
        $user = User::create(['name' => $name, 'email' => "wa{$n}@example.com", 'password' => 'password']);
        $user->roles()->attach(3);
        $profile = \App\StudentProfile::create([
            'user_id' => $user->id, 'mobile' => $mobile, 'whatsapp' => $whatsapp, 'guardian_name' => 'G', 'gender' => 'male',
            'university_id' => $chain['university']->id, 'college_id' => $chain['college']->id,
            'stream_id' => $chain['stream']->id, 'subject_id' => $chain['subject']->id,
            'class_semester' => 'Semester 1', 'year_of_study' => '1st Year',
            'registration_number' => 'R' . $n, 'roll_number' => (string) $n, 'academic_session' => '2026-27',
        ]);
        \App\InternshipRegistration::create([
            'user_id' => $user->id, 'internship_id' => $internship->id, 'student_profile_id' => $profile->id,
            'price' => 1000, 'tax_percent' => 0, 'tax_amount' => 0, 'total_amount' => 1000,
            'status' => $status, 'payment_method' => 'offline', 'approval_status' => 'approved',
        ]);

        return $user;
    }

    public function test_whatsapp_links_go_to_paid_students_only_with_the_class_details()
    {
        $chain = $this->chain();
        $internship = $this->internship(['name' => 'Web Dev'], $chain);
        $class = $this->makeClass($internship, [
            'title' => 'Morning batch', 'meeting_link' => 'https://meet.example.com/abc', 'description' => 'Bring a laptop',
        ]);
        $this->enrol($chain, $internship, 'Asha Verma', 'paid');
        $this->enrol($chain, $internship, 'Pending Person', 'pending_payment');

        $recipients = $class->whatsappRecipients();
        $this->assertCount(1, $recipients);
        $this->assertSame('Asha Verma', $recipients[0]['name']);
        $this->assertStringStartsWith('https://wa.me/919876543210?text=', $recipients[0]['url']);

        $text = urldecode(substr($recipients[0]['url'], strlen('https://wa.me/919876543210?text=')));
        $this->assertStringContainsString('Hi Asha,', $text);
        $this->assertStringContainsString('Web Dev', $text);
        $this->assertStringContainsString('Class: Morning batch', $text);
        $this->assertStringContainsString('Mon, Wed, Fri (07 Dec 2026 to 21 Dec 2026)', $text);
        $this->assertStringContainsString('Time: 10:00 AM - 11:00 AM', $text);
        $this->assertStringContainsString('Join link: https://meet.example.com/abc', $text);
        $this->assertStringContainsString('Bring a laptop', $text);

        $this->actingAs($this->admin())->get("/admin/internship-classes/{$class->id}")
            ->assertOk()->assertSee('Asha Verma')->assertDontSee('Pending Person')
            ->assertSee('https://wa.me/919876543210?text=', false);
    }

    public function test_whatsapp_number_is_normalised_with_mobile_fallback()
    {
        $chain = $this->chain();
        $internship = $this->internship([], $chain);
        $class = $this->makeClass($internship);
        $this->enrol($chain, $internship, 'Plus Ninety One', 'paid', '+91 98765-43211');
        $this->enrol($chain, $internship, 'Fallback Mobile', 'paid', '12345', '09876543212');
        $this->enrol($chain, $internship, 'Bad Numbers', 'paid', '123', '456');

        $byName = collect($class->whatsappRecipients())->keyBy('name');
        $this->assertSame('919876543211', $byName['Plus Ninety One']['phone']);
        $this->assertSame('919876543212', $byName['Fallback Mobile']['phone']);
        $this->assertNull($byName['Bad Numbers']['phone']);
        $this->assertNull($byName['Bad Numbers']['url']);
    }

    public function test_offline_class_message_uses_the_venue_and_one_time_date()
    {
        $class = $this->makeClass($this->internship(), [
            'mode' => 'offline', 'venue' => 'Room 12, Main Block', 'meeting_link' => null,
            'recurrence' => 'none', 'days_of_week' => null, 'end_date' => '2026-12-07',
        ]);

        $text = $class->whatsappMessage();
        $this->assertStringContainsString('Venue: Room 12, Main Block', $text);
        $this->assertStringContainsString('When: Mon, 07 Dec 2026', $text);
        $this->assertStringNotContainsString('Join link', $text);
    }

    public function test_college_admin_only_gets_students_from_their_own_college()
    {
        $chainA = $this->chain('A');
        $internshipA = $this->internship(['name' => 'Shared'], $chainA);
        $classA = $this->makeClass($internshipA);
        $chainB = $this->chain('B');
        $this->enrol($chainA, $internshipA, 'Own Student', 'paid');
        $this->enrol($chainB, $internshipA, 'Other College Student', 'paid');

        $this->actingAs($this->collegeAdmin($chainA['college']))->get("/admin/internship-classes/{$classA->id}")
            ->assertOk()->assertSee('Own Student')->assertDontSee('Other College Student');
    }
}
