<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternshipsTable extends Migration
{
    public function up()
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name')->index();

            $table->unsignedSmallInteger('duration_weeks')->nullable();

            // Base price in INR, excluding tax. tax_percent is 0 until GST is switched on.
            $table->decimal('price', 10, 2)->default(0);

            $table->decimal('tax_percent', 5, 2)->default(0);

            $table->longText('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->softDeletes();
        });
    }
}
