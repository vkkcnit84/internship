<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamsTable extends Migration
{
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('college_id');
            $table->foreign('college_id', 'stream_college_fk')->references('id')->on('colleges');

            $table->string('name');

            $table->timestamps();

            $table->softDeletes();

            $table->index(['college_id', 'name']);
        });
    }
}
