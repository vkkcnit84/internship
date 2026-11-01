<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodApprovalToInternshipRegistrations extends Migration
{
    public function up()
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->enum('payment_method', ['online', 'offline'])->default('online')->after('status');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('payment_method');
        });
    }

    public function down()
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('approval_status');
        });
    }
}
