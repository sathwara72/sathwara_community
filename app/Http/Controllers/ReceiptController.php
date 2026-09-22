<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventSponsor;
use App\Models\SponsorshipType;
use App\Services\ReceiptNumberService;
use App\Services\ReceiptPdfService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Receipt numbers contain "/" (e.g. "2026-27/00006"), which Symfony's
     * download() response rejects in a filename. Swap it for "-" so every
     * download call below gets a filesystem/HTTP-safe name.
     */
    private static function filenameSafe(string $receiptNo): string
    {
        return str_replace('/', '-', $receiptNo);
    }

    /**
     * Download Membership Receipt PDF
     */
    public function downloadMembership($id)
    {
        $user = User::with('memberProfile')->findOrFail($id);
        $profile = $user->memberProfile;
        $paidAmount = (float) $user->payment_amount;
        $fee = $paidAmount > 0 ? $paidAmount : (float) \App\Models\Setting::get('member_signup_fee', '1000');
        $paymentStatus = $user->payment_status ?? 'paid';
        $paymentId = $user->payment_id;
        $receiptNo = ReceiptNumberService::assign($user, 'receipt_no');

        $pdf = ReceiptPdfService::make('emails.receipt_pdf.membership', [
            'user' => $user,
            'profile' => $profile,
            'receiptNo' => $receiptNo,
            'amount' => $fee,
            'paymentStatus' => $paymentStatus,
            'paymentId' => $paymentId,
        ]);

        if (request()->has('view')) {
            return $pdf->stream('Membership_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
        }

        return $pdf->download('Membership_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
    }

    /**
     * Download Business Directory Registration Receipt PDF
     */
    public function downloadBusiness($id)
    {
        $business = Business::with(['user', 'category', 'area'])->findOrFail($id);
        $user = $business->user;
        $paidAmount = (float) $business->payment_amount;
        $fee = $paidAmount > 0 ? $paidAmount : (float) \App\Models\Setting::get('business_registration_fee', '500');
        $paymentStatus = $business->payment_status ?? 'paid';
        $paymentId = $business->payment_id;
        $receiptNo = ReceiptNumberService::assign($business, 'receipt_no');

        $pdf = ReceiptPdfService::make('emails.receipt_pdf.business', [
            'business' => $business,
            'user' => $user,
            'receiptNo' => $receiptNo,
            'amount' => $fee,
            'paymentStatus' => $paymentStatus,
            'paymentId' => $paymentId,
        ]);

        if (request()->has('view')) {
            return $pdf->stream('Business_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
        }

        return $pdf->download('Business_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
    }

    /**
     * Download Event Pass & Payment Receipt PDF
     */
    public function downloadEventPass($id)
    {
        $registration = EventRegistration::with('event')->findOrFail($id);
        $event = $registration->event ?? Event::findOrFail($registration->event_id);
        $user = $registration->user_id ? User::find($registration->user_id) : null;

        // Ensure that if event has a pass fee, registration must be paid
        if ((float) ($event->pass_fee ?? 0) > 0 && ($registration->payment_status ?? 'unpaid') !== 'paid') {
            abort(403, 'Payment is required to download this event pass.');
        }

        $tokens = \App\Services\PassTokenService::getOrGenerateTokens($registration);
        $passTokens = [];
        foreach ($tokens as $tk) {
            $passTokens[] = [
                'passNo' => sprintf('%03d', $tk->pass_index),
                'passCode' => $tk->pass_code,
                'tokenHash' => $tk->token_hash,
                'qrUrl' => \App\Services\PassTokenService::getQrCodeImageUrl($tk->token_hash),
            ];
        }

        $passes = array_column($passTokens, 'passNo');
        $personCount = count($passes);
        $amount = (float) ($registration->payment_amount ?? 0);
        $paymentStatus = $registration->payment_status ?? 'paid';
        $paymentId = $registration->payment_id;
        $receiptNo = ReceiptNumberService::assign($registration, 'receipt_no');

        $pdf = ReceiptPdfService::make('emails.receipt_pdf.event_pass', [
            'event' => $event,
            'registration' => $registration,
            'user' => $user,
            'passes' => $passes,
            'passTokens' => $passTokens,
            'personCount' => $personCount,
            'receiptNo' => $receiptNo,
            'amount' => $amount,
            'paymentStatus' => $paymentStatus,
            'paymentId' => $paymentId,
        ]);

        if (request()->has('view')) {
            return $pdf->stream('Event_Pass_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
        }

        return $pdf->download('Event_Pass_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
    }

    /**
     * Download Sponsorship Contribution Receipt PDF
     */
    public function downloadSponsorship($id)
    {
        $sponsor = EventSponsor::with(['event', 'sponsorshipType'])->findOrFail($id);
        $event = $sponsor->event;
        $sponsorshipType = $sponsor->sponsorshipType;
        $amount = (float) ($sponsor->amount ?? 0);
        $paymentStatus = $sponsor->payment_status ?? 'received';
        $paymentId = $sponsor->payment_id;
        $receiptNo = ReceiptNumberService::assign($sponsor, 'receipt_no');

        $pdf = ReceiptPdfService::make('emails.receipt_pdf.sponsorship', [
            'event' => $event,
            'sponsor' => $sponsor,
            'sponsorshipType' => $sponsorshipType,
            'receiptNo' => $receiptNo,
            'amount' => $amount,
            'paymentStatus' => $paymentStatus,
            'paymentId' => $paymentId,
        ]);

        if (request()->has('view')) {
            return $pdf->stream('Sponsorship_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
        }

        return $pdf->download('Sponsorship_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
    }

    /**
     * Preview Sponsorship Receipt PDF inline in browser
     */
    public function previewSponsorship()
    {
        $event = Event::first();
        if (!$event) {
            $event = new Event([
                'id' => 1,
                'title' => 'સ્નેહ મિલન અને ઈનામ વિતરણ ૨૦૨૬',
                'event_date' => date('Y-m-d', strtotime('+7 days')),
                'start_time' => '18:00:00',
                'venue' => 'સતવારા સમાજ ભવન, ઓઢવ, અમદાવાદ',
            ]);
        }
        $sponsor = EventSponsor::with('sponsorshipType')->first();
        if (!$sponsor) {
            $sponsor = new EventSponsor([
                'id' => 1,
                'name' => 'શ્રી રામ એન્ટરપ્રાઇઝ',
                'contact_person' => 'જિજ્ઞેશભાઈ સતવારા',
                'mobile' => '+91-9825012345',
                'email' => 'sponsor@example.com',
                'city' => 'અમદાવાદ',
                'amount' => 25000.00,
                'payment_status' => 'received',
            ]);
        }
        $receiptNo = ReceiptNumberService::currentFinancialYear() . '/00004';
        $pdf = ReceiptPdfService::make('emails.receipt_pdf.sponsorship', [
            'event' => $event,
            'sponsor' => $sponsor,
            'sponsorshipType' => $sponsor->sponsorshipType,
            'receiptNo' => $receiptNo,
            'amount' => (float) ($sponsor->amount ?? 25000),
            'paymentStatus' => 'received',
            'paymentId' => 'pay_SPN_10004',
        ]);
        return $pdf->stream('Sponsorship_Receipt_' . self::filenameSafe($receiptNo) . '.pdf');
    }
}
