<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // 'members_only' = login required, 'anyone' = non-members can buy passes with a verified email + mobile
            $table->string('pass_purchase_access', 20)->default('members_only')->after('pass_fee');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            // Razorpay order that paid for this registration; unique so a paid order can never create two registrations
            $table->string('razorpay_order_id')->nullable()->unique()->after('payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropUnique(['razorpay_order_id']);
            $table->dropColumn('razorpay_order_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('pass_purchase_access');
        });
    }
};
