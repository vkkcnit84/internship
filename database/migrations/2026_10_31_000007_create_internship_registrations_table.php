<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternshipRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('internship_registrations', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id', 'ireg_user_fk')->references('id')->on('users');

            $table->unsignedInteger('internship_id');
            $table->foreign('internship_id', 'ireg_internship_fk')->references('id')->on('internships');

            // Price snapshot taken at registration so later price edits never change old records.
            $table->decimal('price', 10, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            $table->string('status', 30)->default('pending_payment');

            $table->timestamp('paid_at')->nullable();

            // Reserved for the certificate / "Verify Certificate" feature.
            $table->string('certificate_number')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'internship_id'], 'ireg_user_internship_unique');
        });
    }
}
