<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniversitiesTable extends Migration
{
    public function up()
    {
        // Names are validated for uniqueness in the application (ignoring soft-deleted rows),
        // so the DB index is intentionally not unique.
        Schema::create('universities', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name')->index();

            $table->timestamps();

            $table->softDeletes();
        });
    }
}
