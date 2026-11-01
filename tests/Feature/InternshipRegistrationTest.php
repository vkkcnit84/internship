<?php

namespace Tests\Feature;

use App\InternshipRegistration;
use App\User;

class InternshipRegistrationTest extends InternshipTestCase
{
    public function test_form_renders_with_programmes_and_universities_only()
    {
        $chain = $this->chain('Zed');
        $this->internship([], $chain);
        $this->internship(['name' => 'Hidden', 'is_active' => false], $chain);

        $this->get('/internship/register')->assertOk()
            ->assertSee('UGC Internship')
            ->assertDontSee('Hidden')
            ->assertSee($chain['university']->name)
            // children are loaded on demand, not rendered up-front
            ->assertDontSee($chain['college']->name);
    }

    public function test_lookup_endpoints_return_only_the_children_of_the_parent()
    {
        $one = $this->chain('1');
        $two = $this->chain('2');

        $this->getJson('/internship/lookup/colleges?university_id=' . $one['university']->id)
            ->assertOk()->assertExactJson([['id' => $one['college']->id, 'name' => 'College1']]);
        $this->getJson('/internship/lookup/streams?college_id=' . $two['college']->id)
            ->assertOk()->assertExactJson([['id' => $two['stream']->id, 'name' => 'BA2']]);
        $this->getJson('/internship/lookup/subjects?stream_id=' . $one['stream']->id)
            ->assertOk()->assertExactJson([['id' => $one['subject']->id, 'name' => 'Hindi1']]);

        $this->getJson('/internship/lookup/colleges')->assertStatus(422);
    }

    public function test_successful_registration_persists_everything_and_logs_the_student_in()
    {
        $chain      = $this->chain();
        $internship = $this->internship(['price' => 1000, 'tax_percent' => 18], $chain);

        $response = $this->post('/internship/register', $this->registrationPayload($chain, $internship));

        $user         = User::where('email', 'asha@example.com')->firstOrFail();
        $registration = InternshipRegistration::firstOrFail();

        $response->assertRedirect(route('internship.registration.show', $registration));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->roles->pluck('title')->contains('Student'));
        $this->assertNotSame('secret123', $user->password);

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id, 'mobile' => '9876543210', 'guardian_name' => 'Ramesh Verma',
            'university_id' => $chain['university']->id, 'college_id' => $chain['college']->id,
            'stream_id' => $chain['stream']->id, 'subject_id' => $chain['subject']->id,
            'registration_number' => 'REG123', 'roll_number' => '42',
        ]);

        $this->assertSame('pending_payment', $registration->status);
        $this->assertEquals(1000, $registration->price);
        $this->assertEquals(180, $registration->tax_amount);
        $this->assertEquals(1180, $registration->total_amount);
    }

    public function test_price_is_a_snapshot_and_later_price_changes_do_not_affect_it()
    {
        $chain      = $this->chain();
        $internship = $this->internship(['price' => 500], $chain);

        $this->post('/internship/register', $this->registrationPayload($chain, $internship));
        $internship->update(['price' => 9999]);

        $this->assertEquals(500, InternshipRegistration::first()->total_amount);
    }

    public function test_free_programme_is_activated_immediately()
    {
        $chain      = $this->chain();
        $internship = $this->internship(['price' => 0], $chain);

        $this->post('/internship/register', $this->registrationPayload($chain, $internship));

        $registration = InternshipRegistration::first();
        $this->assertTrue($registration->isPaid());
        $this->assertNotNull($registration->paid_at);
    }

    public function test_required_fields_are_validated()
    {
        $this->post('/internship/register', [])->assertSessionHasErrors([
            'name', 'email', 'mobile', 'whatsapp', 'guardian_name', 'gender', 'password', 'payment_method', 'internship_id',
            'university_id', 'college_id', 'stream_id', 'subject_id', 'class_semester', 'year_of_study',
            'registration_number', 'roll_number', 'academic_session',
        ]);

        $this->assertSame(0, User::count());
    }

    public function test_field_formats_are_validated()
    {
        $chain      = $this->chain();
        $internship = $this->internship([], $chain);

        $this->post('/internship/register', $this->registrationPayload($chain, $internship, [
            'email' => 'not-an-email', 'mobile' => '12345', 'whatsapp' => '5876543210',
            'password' => 'short', 'gender' => 'robot', 'class_semester' => 'Semester 99', 'academic_session' => '1999-00',
        ]))->assertSessionHasErrors(['email', 'mobile', 'whatsapp', 'password', 'gender', 'class_semester', 'academic_session']);
    }

    public function test_the_academic_chain_must_be_consistent()
    {
        $one        = $this->chain('1');
        $two        = $this->chain('2');
        $internship = $this->internship([], $one);

        // college belongs to another university
        $this->post('/internship/register', $this->registrationPayload($one, $internship, ['college_id' => $two['college']->id]))
            ->assertSessionHasErrors('college_id');

        // stream belongs to another college
        $this->post('/internship/register', $this->registrationPayload($one, $internship, ['stream_id' => $two['stream']->id]))
            ->assertSessionHasErrors('stream_id');

        // subject belongs to another stream
        $this->post('/internship/register', $this->registrationPayload($one, $internship, ['subject_id' => $two['subject']->id]))
            ->assertSessionHasErrors('subject_id');

        $this->assertSame(0, User::count());
        $this->assertSame(0, InternshipRegistration::count());
    }

    public function test_inactive_or_unknown_programmes_are_rejected()
    {
        $chain    = $this->chain();
        $inactive = $this->internship(['is_active' => false], $chain);

        $this->post('/internship/register', $this->registrationPayload($chain, $inactive))->assertSessionHasErrors('internship_id');
        $this->post('/internship/register', $this->registrationPayload($chain, $inactive, ['internship_id' => 999]))->assertSessionHasErrors('internship_id');
    }

    public function test_duplicate_email_is_rejected_and_old_input_survives_a_failed_submit()
    {
        $chain      = $this->chain('Zed');
        $internship = $this->internship([], $chain);
        User::create(['name' => 'Existing', 'email' => 'asha@example.com', 'password' => 'password']);

        $this->from('/internship/register')
            ->post('/internship/register', $this->registrationPayload($chain, $internship))
            ->assertRedirect('/internship/register')
            ->assertSessionHasErrors('email');

        // the re-rendered form keeps the cascaded selections
        $this->withSession(['_old_input' => ['university_id' => $chain['university']->id, 'college_id' => $chain['college']->id, 'stream_id' => $chain['stream']->id]])
            ->get('/internship/register')
            ->assertSee('CollegeZed')->assertSee('BAZed')->assertSee('HindiZed');
    }

    public function test_existing_course_enrollment_flow_is_untouched()
    {
        $this->get('/')->assertOk();
        $this->get('/courses')->assertOk();
        $this->get('/login')->assertOk();
    }
}
