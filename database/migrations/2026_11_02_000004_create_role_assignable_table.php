<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoleAssignableTable extends Migration
{
    public function up()
    {
        Schema::create('role_assignable', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->foreign('role_id', 'role_assignable_role_fk')->references('id')->on('roles')->onDelete('cascade');

            $table->unsignedInteger('assignable_role_id');
            $table->foreign('assignable_role_id', 'role_assignable_target_fk')->references('id')->on('roles')->onDelete('cascade');

            $table->primary(['role_id', 'assignable_role_id']);
        });

        // Keep today's behaviour: any role that can already create/edit users may assign every role,
        // until the super admin narrows it down.
        $allRoleIds = DB::table('roles')->pluck('id');
        $managerRoleIds = DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->whereIn('permissions.title', ['user_create', 'user_edit'])
            ->pluck('permission_role.role_id')
            ->unique();

        foreach ($managerRoleIds as $roleId) {
            foreach ($allRoleIds as $targetId) {
                DB::table('role_assignable')->insert(['role_id' => $roleId, 'assignable_role_id' => $targetId]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_assignable');
    }
}
