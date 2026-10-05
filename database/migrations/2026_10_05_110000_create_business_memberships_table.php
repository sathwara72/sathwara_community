<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per membership period of a business directory listing (approval, renewal or admin change).
     */
    public function up(): void
    {
        Schema::create('business_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->string('source', 30); // approval | payment | admin
            $table->string('payment_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'started_at']);
        });

        // Backfill: past paid renewals, then the current period of every approved business
        $now = now();
        foreach (DB::table('business_payment_links')->where('status', 'paid')->whereNotNull('paid_at')->get() as $link) {
            DB::table('business_memberships')->insert([
                'business_id' => $link->business_id,
                'started_at' => $link->paid_at,
                'expires_at' => \Illuminate\Support\Carbon::parse($link->paid_at)->addYear(),
                'source' => 'payment',
                'payment_id' => $link->razorpay_payment_id,
                'amount' => $link->amount,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (DB::table('businesses')->whereNotNull('approved_at')->get() as $business) {
            $approvedAt = \Illuminate\Support\Carbon::parse($business->approved_at);
            $alreadyRecorded = DB::table('business_memberships')
                ->where('business_id', $business->id)
                ->whereBetween('started_at', [$approvedAt->copy()->subMinutes(5), $approvedAt->copy()->addMinutes(5)])
                ->exists();

            if (!$alreadyRecorded) {
                DB::table('business_memberships')->insert([
                    'business_id' => $business->id,
                    'started_at' => $approvedAt,
                    'expires_at' => $approvedAt->copy()->addYear(),
                    'source' => 'approval',
                    'payment_id' => $business->payment_status === 'paid' ? $business->payment_id : null,
                    'amount' => $business->payment_status === 'paid' ? $business->payment_amount : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_memberships');
    }
};
