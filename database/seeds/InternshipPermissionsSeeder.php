<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

/**
 * Adds the internship-module permissions and grants them to the Admin role.
 * Idempotent, so it can be run on an existing database:
 *   php artisan db:seed --class=InternshipPermissionsSeeder
 */
class InternshipPermissionsSeeder extends Seeder
{
    public function run()
    {
        $titles = ['internship_management_access', 'internship_registration_access'];

        foreach (['university', 'college', 'stream', 'subject', 'internship'] as $entity) {
            foreach (['create', 'edit', 'show', 'delete', 'access'] as $action) {
                $titles[] = "{$entity}_{$action}";
            }
        }

        // Specific permissions for offline payment management
        $paymentPermissions = [
            'internship_registration_approve',
            'internship_registration_reject',
            'internship_registration_mark_paid',
        ];

        $ids = collect($titles)->map(function ($title) {
            return Permission::firstOrCreate(['title' => $title])->id;
        });

        // Create payment-specific permissions
        $paymentIds = collect($paymentPermissions)->map(function ($title) {
            return Permission::firstOrCreate(['title' => $title])->id;
        });

        // Grant all permissions to Admin role (ID 1)
        if ($admin = Role::find(1)) {
            $admin->permissions()->syncWithoutDetaching($ids->merge($paymentIds)->all());
        }

        // Grant read-only + payment management permissions to College Admin role (ID 4)
        // College admins can: view all, access all, show all, approve/reject/mark-paid
        if ($collegeAdmin = Role::find(4)) {
            $collegeAdminPermissions = collect($titles)->filter(function ($title) {
                // Grant *_access and *_show for all entities
                return strpos($title, '_access') !== false || strpos($title, '_show') !== false;
            })->map(function ($title) {
                return Permission::where('title', $title)->first()->id ?? null;
            })->filter();

            // Add the specific payment management permissions
            $collegeAdminPermissions = $collegeAdminPermissions->merge($paymentIds);

            $collegeAdmin->permissions()->sync($collegeAdminPermissions->all());
        }
    }
}
