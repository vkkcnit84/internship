<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollegesTable extends Migration
{
    public function up()
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('university_id');
            $table->foreign('university_id', 'college_university_fk')->references('id')->on('universities');

            $table->string('name');

            $table->timestamps();

            $table->softDeletes();

            $table->index(['university_id', 'name']);
        });
    }
}
