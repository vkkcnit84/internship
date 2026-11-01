<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These columns were first added to the dev database by hand; this makes a fresh
 * `migrate` produce the same schema. Skips any column that already exists.
 */
class AddCollegeIdToUsersCoursesInstitutions extends Migration
{
    private $tables = ['users', 'courses', 'institutions'];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'college_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedInteger('college_id')->nullable();
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'college_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('college_id');
                });
            }
        }
    }
}
