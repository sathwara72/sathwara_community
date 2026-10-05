<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every successful payment the Mandal receives (online or at the office), one row each.
     * A failed or cancelled payment never creates a row.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->nullable()->unique(); // TXN-000001, set right after insert
            $table->string('payment_id')->nullable()->unique();     // Razorpay payment id; null for office payments
            $table->string('method', 20)->default('razorpay');      // razorpay | office
            $table->string('purpose', 50);
            $table->decimal('amount', 10, 2);
            $table->nullableMorphs('payable');
            $table->string('payer_name')->nullable();
            $table->string('payer_contact')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['purpose', 'created_at']);
        });

        $this->backfillPastPayments();
    }

    /**
     * Give every payment received before this table existed its own transaction, oldest first.
     */
    private function backfillPastPayments(): void
    {
        $rows = [];
        $add = function (?string $paymentId, string $purpose, $amount, string $type, $id, ?string $name, ?string $contact, $paidAt) use (&$rows) {
            if (empty($paymentId) || (float) $amount <= 0 || isset($rows[$paymentId])) {
                return;
            }
            $rows[$paymentId] = [
                'payment_id' => $paymentId, 'method' => 'razorpay', 'purpose' => $purpose, 'amount' => (float) $amount,
                'payable_type' => $type, 'payable_id' => $id, 'payer_name' => $name, 'payer_contact' => $contact,
                'created_at' => $paidAt ?? now(), 'updated_at' => now(),
            ];
        };

        foreach (DB::table('business_payment_links')->join('businesses', 'businesses.id', '=', 'business_payment_links.business_id')
            ->where('business_payment_links.status', 'paid')
            ->get(['business_payment_links.*', 'businesses.owner_name', 'businesses.business_name', 'businesses.phone']) as $l) {
            $add($l->razorpay_payment_id, 'business_renewal', $l->amount, 'App\\Models\\Business', $l->business_id, "{$l->owner_name} ({$l->business_name})", $l->phone, $l->paid_at);
        }
        foreach (DB::table('businesses')->where('payment_status', 'paid')->get() as $b) {
            $add($b->payment_id, 'business_registration', $b->payment_amount, 'App\\Models\\Business', $b->id, "{$b->owner_name} ({$b->business_name})", $b->phone, $b->created_at);
        }
        foreach (DB::table('users')->where('payment_status', 'paid')->get() as $u) {
            $add($u->payment_id, 'membership', $u->payment_amount, 'App\\Models\\User', $u->id, $u->name, null, $u->created_at);
        }
        foreach (DB::table('event_registrations')->where('payment_status', 'paid')->get() as $r) {
            $fd = json_decode($r->form_data ?? '[]', true) ?: [];
            $purpose = $r->yuva_melo_number ? 'yuva_melo_fee' : (!empty($fd['is_guest']) ? 'guest_event_pass' : 'event_pass');
            $add($r->payment_id, $purpose, $r->payment_amount, 'App\\Models\\EventRegistration', $r->id, $fd['full_name'] ?? null, $fd['mobile'] ?? $fd['contact_number'] ?? $fd['mobile_no'] ?? null, $r->created_at);
        }
        foreach (DB::table('event_sponsors')->where('payment_status', 'received')->get() as $sp) {
            $add($sp->payment_id, 'sponsorship', $sp->amount, 'App\\Models\\EventSponsor', $sp->id, $sp->name, $sp->mobile, $sp->created_at);
        }

        usort($rows, fn ($a, $b) => strcmp((string) $a['created_at'], (string) $b['created_at']));
        foreach ($rows as $row) {
            $id = DB::table('transactions')->insertGetId($row);
            DB::table('transactions')->where('id', $id)->update(['transaction_no' => 'TXN-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
