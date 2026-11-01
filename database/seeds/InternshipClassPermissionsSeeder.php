<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

/**
 * Adds the class-scheduling permissions and grants them to Admin and College Admin.
 * Idempotent and additive (never removes existing grants):
 *   php artisan db:seed --class='\InternshipClassPermissionsSeeder'
 */
class InternshipClassPermissionsSeeder extends Seeder
{
    public function run()
    {
        $ids = collect(['access', 'create', 'edit', 'show', 'delete'])->map(function ($action) {
            return Permission::firstOrCreate(['title' => "internship_class_{$action}"])->id;
        })->all();

        foreach ([1, 4] as $roleId) {
            if ($role = Role::find($roleId)) {
                $role->permissions()->syncWithoutDetaching($ids);
            }
        }
    }
}
