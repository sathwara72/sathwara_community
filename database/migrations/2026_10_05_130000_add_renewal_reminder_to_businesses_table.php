<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->timestamp('renewal_reminder_sent_at')->nullable()->after('approved_at');
        });

        // The old daily job wrongly wrote "inactive" into the approval status; that meant an expired membership
        DB::table('businesses')->where('status', 'inactive')->update(['status' => 'approved', 'membership_status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('renewal_reminder_sent_at');
        });
    }
};
