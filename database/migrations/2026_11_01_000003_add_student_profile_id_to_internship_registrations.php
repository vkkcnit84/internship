<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentProfileIdToInternshipRegistrations extends Migration
{
    public function up()
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->unsignedInteger('student_profile_id')->nullable()->after('user_id');
            $table->foreign('student_profile_id', 'ireg_student_profile_fk')->references('id')->on('student_profiles')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropForeign('ireg_student_profile_fk');
            $table->dropColumn('student_profile_id');
        });
    }
}
