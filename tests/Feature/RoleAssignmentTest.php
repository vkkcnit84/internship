<?php

namespace Tests\Feature;

use App\Permission;
use App\Role;
use App\User;

class RoleAssignmentTest extends InternshipTestCase
{
    /** A College Admin who is allowed to manage users, but only assign the given roles. */
    private function manager(array $assignable): User
    {
        $role = Role::find(4);
        $role->permissions()->syncWithoutDetaching(
            Permission::whereIn('title', ['user_access', 'user_create', 'user_edit', 'user_show', 'user_delete'])->pluck('id')->all()
        );
        $role->assignableRoles()->sync($assignable);
        $this->defineGates();

        $user = User::create(['name' => 'Manager', 'email' => 'manager@example.com', 'password' => 'password']);
        $user->roles()->attach(4);

        return $user;
    }

    private function newUserPayload(array $roles, string $email = 'new@example.com'): array
    {
        return ['name' => 'New User', 'email' => $email, 'password' => 'secret123', 'roles' => $roles];
    }

    public function test_admin_always_sees_and_can_assign_every_role()
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin)->get('/admin/users/create')->assertOk();

        foreach (Role::pluck('title') as $title) {
            $response->assertSee('>' . $title . '</option>', false);
        }

        $this->actingAs($admin)->post('/admin/users', $this->newUserPayload([1, 4]))->assertRedirect('/admin/users');
        $this->assertSame([1, 4], User::where('email', 'new@example.com')->first()->roles->pluck('id')->sort()->values()->all());
    }

    public function test_a_role_only_sees_the_roles_it_may_assign()
    {
        $manager = $this->manager([3]);

        $this->actingAs($manager)->get('/admin/users/create')->assertOk()
            ->assertSee('>Student</option>', false)
            ->assertDontSee('>Admin</option>', false)
            ->assertDontSee('>College Admin</option>', false);
    }

    public function test_server_rejects_roles_that_are_not_assignable()
    {
        $manager = $this->manager([3]);

        $this->actingAs($manager)->post('/admin/users', $this->newUserPayload([1]))->assertSessionHasErrors('roles.0');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);

        $this->actingAs($manager)->post('/admin/users', $this->newUserPayload([3, 1]))->assertSessionHasErrors('roles.1');

        $this->actingAs($manager)->post('/admin/users', $this->newUserPayload([3]))->assertRedirect('/admin/users');
        $this->assertSame([3], User::where('email', 'new@example.com')->first()->roles->pluck('id')->all());
    }

    public function test_a_manager_cannot_list_edit_or_delete_users_with_roles_they_cannot_assign()
    {
        $manager = $this->manager([3]);
        $admin   = $this->admin();
        $student = User::create(['name' => 'Stu Dent', 'email' => 'stu@example.com', 'password' => 'password']);
        $student->roles()->attach(3);

        $this->actingAs($manager)->get('/admin/users')->assertOk()->assertSee('stu@example.com')->assertDontSee('admin@example.com');

        $this->actingAs($manager)->get("/admin/users/{$admin->id}/edit")->assertForbidden();
        $this->actingAs($manager)->put("/admin/users/{$admin->id}", ['name' => 'Hacked', 'email' => 'admin@example.com', 'roles' => [3]])->assertForbidden();
        $this->actingAs($manager)->delete("/admin/users/{$admin->id}")->assertForbidden();
        $this->actingAs($manager)->delete('/admin/users/destroy', ['ids' => [$student->id, $admin->id]])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Admin']);
        $this->assertDatabaseHas('users', ['id' => $student->id]);

        $this->actingAs($manager)->get("/admin/users/{$student->id}/edit")->assertOk();
        $this->actingAs($manager)->delete("/admin/users/{$student->id}")->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $student->id]);
    }

    public function test_a_role_with_nothing_ticked_can_assign_nothing()
    {
        $manager = $this->manager([]);

        $this->actingAs($manager)->post('/admin/users', $this->newUserPayload([3]))->assertSessionHasErrors('roles.0');
    }

    public function test_admin_can_set_the_assignable_roles_on_the_role_form()
    {
        $admin = $this->admin();
        $permissionId = Permission::first()->id;

        $this->actingAs($admin)->post('/admin/roles', [
            'title' => 'Coordinator', 'permissions' => [$permissionId], 'assignable_roles' => [3, 4],
        ])->assertRedirect('/admin/roles');

        $role = Role::where('title', 'Coordinator')->first();
        $this->assertSame([3, 4], $role->assignableRoles->pluck('id')->sort()->values()->all());

        $this->actingAs($admin)->get("/admin/roles/{$role->id}/edit")->assertOk()->assertSee('Roles this role can assign');
        $this->actingAs($admin)->get("/admin/roles/{$role->id}")->assertOk()->assertSee('Can assign roles');

        $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
            'title' => 'Coordinator', 'permissions' => [$permissionId], 'assignable_roles' => [3],
        ])->assertRedirect('/admin/roles');
        $this->assertSame([3], $role->fresh()->assignableRoles->pluck('id')->all());

        $this->actingAs($admin)->post('/admin/roles', [
            'title' => 'Bad', 'permissions' => [$permissionId], 'assignable_roles' => [999],
        ])->assertSessionHasErrors('assignable_roles.0');
    }
}
