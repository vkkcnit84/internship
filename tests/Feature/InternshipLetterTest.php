<?php

namespace Tests\Feature;

use App\InternshipRegistration;

class InternshipLetterTest extends InternshipTestCase
{
    private function paidRegistration(): InternshipRegistration
    {
        $chain = $this->chain();
        $this->post('/internship/register', $this->registrationPayload($chain, $this->internship([], $chain)));
        auth()->logout();

        $registration = InternshipRegistration::firstOrFail();
        $registration->update(['status' => InternshipRegistration::STATUS_PAID, 'paid_at' => now()]);

        return $registration;
    }

    public function test_admin_can_open_a_students_consent_and_acceptance_letters()
    {
        $registration = $this->paidRegistration();
        $admin        = $this->admin();

        $this->actingAs($admin)->get("/admin/internship-registrations/{$registration->id}/consent-letter")
            ->assertOk()->assertSee('CONSENT LETTER')->assertSee('Asha Verma')->assertSee('REG123');

        $this->actingAs($admin)->get("/admin/internship-registrations/{$registration->id}/acceptance-letter")
            ->assertOk()->assertSee('INTERNSHIP ACCEPTANCE LETTER')->assertSee('Asha Verma');

        $this->actingAs($admin)->get('/admin/internship-registrations')
            ->assertOk()->assertSee('Consent Letter')->assertSee('Acceptance Letter');
    }

    public function test_student_cannot_open_the_admin_letter_routes()
    {
        $registration = $this->paidRegistration();
        $student      = $registration->user;

        $this->actingAs($student)->get("/admin/internship-registrations/{$registration->id}/consent-letter")->assertForbidden();
    }

    public function test_student_portal_pages_render()
    {
        $registration = $this->paidRegistration();
        $student      = $registration->user;

        foreach (['registration-slip' => 'Student Registration Slip', 'acceptance-letter' => 'INTERNSHIP ACCEPTANCE LETTER', 'welcome-letter' => 'CONSENT LETTER'] as $path => $text) {
            $this->actingAs($student)->get("/admin/$path")->assertOk()->assertSee($text);
        }
    }
}
