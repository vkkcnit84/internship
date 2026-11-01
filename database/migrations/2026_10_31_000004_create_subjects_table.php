<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('stream_id');
            $table->foreign('stream_id', 'subject_stream_fk')->references('id')->on('streams');

            $table->string('name');

            $table->timestamps();

            $table->softDeletes();

            $table->index(['stream_id', 'name']);
        });
    }
}
