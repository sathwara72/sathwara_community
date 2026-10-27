<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pass purchase last date gets a time (India time). Existing dates keep closing
     * at the end of that day, 23:59:59.
     * Sponsors get a payment mode (online / cash); those paid through Razorpay are online.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('registration_end_date')->nullable()->change();
        });

        foreach (DB::table('events')->whereNotNull('registration_end_date')->get(['id', 'registration_end_date']) as $e) {
            DB::table('events')->where('id', $e->id)->update([
                'registration_end_date' => substr($e->registration_end_date, 0, 10) . ' 23:59:59',
            ]);
        }

        Schema::table('event_sponsors', function (Blueprint $table) {
            $table->string('payment_mode', 20)->nullable()->after('payment_status'); // online, cash
        });

        DB::table('event_sponsors')->whereNotNull('payment_id')->update(['payment_mode' => 'online']);
    }

    public function down(): void
    {
        Schema::table('event_sponsors', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->date('registration_end_date')->nullable()->change();
        });
    }
};
