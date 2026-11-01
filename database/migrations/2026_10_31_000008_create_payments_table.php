<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('internship_registration_id');
            $table->foreign('internship_registration_id', 'payment_ireg_fk')->references('id')->on('internship_registrations');

            $table->string('gateway', 30)->default('razorpay');
            $table->string('gateway_order_id')->unique();
            $table->string('gateway_payment_id')->nullable();

            $table->unsignedInteger('amount_paise');
            $table->string('currency', 3)->default('INR');

            $table->string('status', 20)->default('created');

            $table->timestamp('paid_at')->nullable();

            $table->text('gateway_response')->nullable();

            $table->timestamps();
        });
    }
}
