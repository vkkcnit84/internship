<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCollegeIdToInternships extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('internships', 'college_id')) {
            Schema::table('internships', function (Blueprint $table) {
                $table->unsignedBigInteger('college_id')->nullable()->after('is_active');
                $table->foreign('college_id')->references('id')->on('colleges')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('internships', 'college_id')) {
            Schema::table('internships', function (Blueprint $table) {
                $table->dropForeign(['college_id']);
                $table->dropColumn('college_id');
            });
        }
    }
}
