<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every time someone opens the Razorpay popup, one row: what they were paying for, who they are,
     * and how it ended (cancelled / failed / paid / verified / completed). Lets the office trace a
     * payment that was taken but never got verified or saved.
     */
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('attempt_key', 40)->unique();           // generated in the browser
            $table->string('purpose', 50)->nullable();             // Transaction::PURPOSES key
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payer_name')->nullable();
            $table->string('payer_contact', 30)->nullable();
            $table->string('payer_email')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('opened')->index(); // see PaymentAttempt::STATUSES
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_order_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->string('razorpay_status', 30)->nullable();      // last status fetched from Razorpay by an admin
            $table->timestamp('razorpay_checked_at')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
