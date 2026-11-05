<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

/**
 * Adds `internship_registration_amount_view` (shows the Total column on the registrations
 * list) and grants it to Admin only, so College Admins do not see amounts. Tick it on
 * other roles from the Roles page. Idempotent and additive:
 *   php artisan db:seed --class='\InternshipRegistrationAmountPermissionSeeder'
 */
class InternshipRegistrationAmountPermissionSeeder extends Seeder
{
    public function run()
    {
        $permission = Permission::firstOrCreate(['title' => 'internship_registration_amount_view']);

        if ($role = Role::find(1)) {
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }
}
