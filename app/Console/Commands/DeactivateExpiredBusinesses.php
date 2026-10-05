<?php

namespace App\Console\Commands;

use App\Mail\BusinessRenewalDueMail;
use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DeactivateExpiredBusinesses extends Command
{
    protected $signature = 'business:deactivate-expired';

    protected $description = 'End expired 1-year business memberships: renew automatically when renewal is free, otherwise close and email the owner to renew';

    public function handle(): int
    {
        $renewed = 0;
        $closed = 0;

        $expired = Business::where('status', 'approved')
            ->whereNotNull('approved_at')
            ->where('approved_at', '<=', now()->subYear())
            ->get();

        foreach ($expired as $business) {
            $result = $business->applyMembershipExpiry();

            if ($result === 'renewed') {
                $renewed++;
            } elseif ($result === 'closed') {
                $closed++;
                $this->remindToRenew($business);
            }
        }

        $this->info("Free renewals: {$renewed}. Closed (renewal fee due): {$closed}.");

        return self::SUCCESS;
    }

    /**
     * One reminder per expired membership period.
     */
    private function remindToRenew(Business $business): void
    {
        $expiredOn = $business->approved_at->copy()->addYear();
        if (empty($business->email) || ($business->renewal_reminder_sent_at && $business->renewal_reminder_sent_at->gte($expiredOn))) {
            return;
        }

        try {
            Mail::to($business->email)->send(new BusinessRenewalDueMail($business, Business::renewalFee()));
            $business->forceFill(['renewal_reminder_sent_at' => now()])->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('Business renewal reminder failed: ' . $e->getMessage(), ['business_id' => $business->id]);
        }
    }
}
