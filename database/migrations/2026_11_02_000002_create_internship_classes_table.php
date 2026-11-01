<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternshipClassesTable extends Migration
{
    public function up()
    {
        Schema::create('internship_classes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('internship_id');
            $table->foreign('internship_id', 'class_internship_fk')->references('id')->on('internships');

            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by', 'class_created_by_fk')->references('id')->on('users')->onDelete('set null');

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('mode', 20)->default('online');
            $table->string('meeting_link', 500)->nullable();
            $table->string('venue')->nullable();

            // none = one-time, daily, weekly (on days_of_week, ISO 1=Mon .. 7=Sun)
            $table->string('recurrence', 20)->default('none');
            $table->string('days_of_week')->nullable();

            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['internship_id', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('internship_classes');
    }
}
