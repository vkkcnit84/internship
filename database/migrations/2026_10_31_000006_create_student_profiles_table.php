<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->unique();
            $table->foreign('user_id', 'profile_user_fk')->references('id')->on('users');

            $table->string('mobile', 15);
            $table->string('whatsapp', 15);
            $table->string('guardian_name');
            $table->string('gender', 10);

            $table->unsignedInteger('university_id');
            $table->foreign('university_id', 'profile_university_fk')->references('id')->on('universities');

            $table->unsignedInteger('college_id');
            $table->foreign('college_id', 'profile_college_fk')->references('id')->on('colleges');

            $table->unsignedInteger('stream_id');
            $table->foreign('stream_id', 'profile_stream_fk')->references('id')->on('streams');

            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id', 'profile_subject_fk')->references('id')->on('subjects');

            $table->string('class_semester', 50);
            $table->string('year_of_study', 50);
            $table->string('registration_number', 100);
            $table->string('roll_number', 100);
            $table->string('academic_session', 20);

            $table->timestamps();

            $table->softDeletes();
        });
    }
}
