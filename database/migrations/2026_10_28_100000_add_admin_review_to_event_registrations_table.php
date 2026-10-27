<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin review of a submission (e.g. Inam marks that don't match the marksheet): the note shown to
     * the member when a correction is asked for or the submission is rejected.
     */
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->text('admin_note')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['admin_note', 'reviewed_at']);
        });
    }
};
