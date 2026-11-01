<?php

namespace Tests\Feature;

use App\College;
use App\Internship;
use App\University;
use App\User;

class InternshipCrudTest extends InternshipTestCase
{
    public function test_admin_can_create_edit_and_delete_a_university()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/universities', ['name' => 'Delhi University'])
            ->assertRedirect('/admin/universities');
        $this->assertDatabaseHas('universities', ['name' => 'Delhi University']);

        $university = University::first();

        $this->actingAs($admin)->put("/admin/universities/{$university->id}", ['name' => 'DU'])
            ->assertRedirect('/admin/universities');
        $this->assertDatabaseHas('universities', ['id' => $university->id, 'name' => 'DU']);

        $this->actingAs($admin)->delete("/admin/universities/{$university->id}")->assertRedirect();
        $this->assertSoftDeleted('universities', ['id' => $university->id]);
    }

    public function test_index_create_edit_and_show_pages_render_for_every_entity()
    {
        $admin = $this->admin();
        $chain = $this->chain();
        $internship = $this->internship();

        $records = [
            'universities' => $chain['university'],
            'colleges'     => $chain['college'],
            'streams'      => $chain['stream'],
            'subjects'     => $chain['subject'],
            'internships'  => $internship,
        ];

        foreach ($records as $uri => $record) {
            $this->actingAs($admin)->get("/admin/{$uri}")->assertOk()->assertSee($record->name);
            $this->actingAs($admin)->get("/admin/{$uri}/create")->assertOk();
            $this->actingAs($admin)->get("/admin/{$uri}/{$record->id}/edit")->assertOk();
            $this->actingAs($admin)->get("/admin/{$uri}/{$record->id}")->assertOk()->assertSee($record->name);
        }

        $this->actingAs($admin)->get('/admin/internship-registrations')->assertOk();
    }

    public function test_names_must_be_unique_within_their_parent_only()
    {
        $admin = $this->admin();
        $a = University::create(['name' => 'A']);
        $b = University::create(['name' => 'B']);
        College::create(['name' => 'Central', 'university_id' => $a->id]);

        // Same college name under another university is fine ...
        $this->actingAs($admin)->post('/admin/colleges', ['name' => 'Central', 'university_id' => $b->id])
            ->assertRedirect('/admin/colleges');

        // ... but not twice under the same one.
        $this->actingAs($admin)->post('/admin/colleges', ['name' => 'Central', 'university_id' => $a->id])
            ->assertSessionHasErrors('name');

        $this->assertSame(2, College::count());
    }

    public function test_a_soft_deleted_name_can_be_reused()
    {
        $admin = $this->admin();
        University::create(['name' => 'Old'])->delete();

        $this->actingAs($admin)->post('/admin/universities', ['name' => 'Old'])->assertRedirect('/admin/universities');
    }

    public function test_child_must_have_an_existing_parent()
    {
        $this->actingAs($this->admin())->post('/admin/colleges', ['name' => 'X', 'university_id' => 999])
            ->assertSessionHasErrors('university_id');
    }

    public function test_a_parent_in_use_cannot_be_deleted()
    {
        $admin = $this->admin();
        $chain = $this->chain();

        $this->actingAs($admin)->delete("/admin/universities/{$chain['university']->id}")
            ->assertSessionHasErrors('delete');
        $this->assertNull($chain['university']->fresh()->deleted_at);

        // Mass delete is all-or-nothing.
        $spare = University::create(['name' => 'Spare']);
        $this->actingAs($admin)->deleteJson('/admin/universities/destroy', ['ids' => [$spare->id, $chain['university']->id]])
            ->assertStatus(422);
        $this->assertNull($spare->fresh()->deleted_at);

        $this->actingAs($admin)->deleteJson('/admin/universities/destroy', ['ids' => [$spare->id]])->assertNoContent();
        $this->assertSoftDeleted('universities', ['id' => $spare->id]);
    }

    public function test_internship_price_fields_are_validated_and_saved()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/internships', ['name' => 'Bad', 'price' => -5, 'tax_percent' => 0])
            ->assertSessionHasErrors('price');
        $this->actingAs($admin)->post('/admin/internships', ['name' => 'Bad', 'price' => 10, 'tax_percent' => 150])
            ->assertSessionHasErrors('tax_percent');

        $this->actingAs($admin)->post('/admin/internships', [
            'name' => 'Summer', 'price' => '1499.50', 'tax_percent' => 18, 'duration_weeks' => 4, 'is_active' => 1,
        ])->assertRedirect('/admin/internships');

        $internship = Internship::where('name', 'Summer')->first();
        $this->assertEquals(1499.50, $internship->price);
        $this->assertEquals(1769.41, $internship->getTotalAmount());
        $this->assertTrue($internship->is_active);
    }

    public function test_users_without_permission_are_forbidden()
    {
        $student = User::create(['name' => 'S', 'email' => 's@example.com', 'password' => 'password']);
        $student->roles()->attach(3);

        $this->actingAs($student)->get('/admin/universities')->assertForbidden();
        $this->actingAs($student)->post('/admin/universities', ['name' => 'Nope'])->assertForbidden();
        $this->actingAs($student)->get('/admin/internship-registrations')->assertForbidden();

        auth()->logout();
        $this->get('/admin/universities')->assertRedirect('/login');
    }
}
